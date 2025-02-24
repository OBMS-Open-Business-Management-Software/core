<?php

declare(strict_types=1);

namespace App\Jobs\TenantJobs;

use App\Helpers\Products;
use App\Jobs\Structure\TenantJob;
use App\Jobs\Structure\UniquelyQueueable;
use App\Models\Shop\Configurator\ShopConfiguratorField;
use App\Models\Shop\OrderQueue\ShopOrderQueue;
use App\Models\Shop\OrderQueue\ShopOrderQueueField;
use App\Models\Shop\OrderQueue\ShopOrderQueueHistory;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class ShopOrderQueueSetup.
 *
 * This class is the tenant job for processing orders of a specific type.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ShopOrderQueueSetup extends TenantJob
{
    use UniquelyQueueable;

    public $tries = 1;

    public $timeout = 3600;

    public static $onQueue = 'shop_order_setups';

    private string $type;

    /**
     * ShopOrderQueueSetup constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->type = $data['type'];
    }

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        $handler = Products::list()->filter(function ($handler) {
            return $handler->technicalName() === $this->type;
        })->first();

        if (! empty($handler)) {
            ShopOrderQueue::where('product_type', '=', $handler->technicalName())
                ->where('verified', '=', false)
                ->where('invalid', '=', false)
                ->where('approved', '=', true)
                ->where('disapproved', '=', false)
                ->where('setup', '=', false)
                ->where('locked', '=', false)
                ->where('deleted', '=', false)
                ->where('fails', '<', 3)
                ->each(function (ShopOrderQueue $queueItem) {
                    $fields          = collect();
                    $validationRules = [];

                    $queueItem->fields->each(function (ShopOrderQueueField $queueField) use ($queueItem, &$fields, &$validationRules) {
                        $fields->put($queueField->key, $queueField->value);

                        $queueItem->form->fields->each(function (ShopConfiguratorField $field) use (&$validationRules) {
                            $rules = [];

                            if ($field->required) {
                                $rules[] = 'required';
                            } else {
                                $rules[] = 'nullable';
                            }

                            switch ($field->type) {
                                case 'input_number':
                                case 'input_range':
                                    $rules[] = 'numeric';

                                    if (! empty($field->min)) {
                                        $rules[] = 'min:' . $field->min;
                                    }

                                    if (! empty($field->max)) {
                                        $rules[] = 'max:' . $field->max;
                                    }

                                    if (! empty($field->step)) {
                                        $rules[] = 'multiple_of:' . $field->step;
                                    }

                                    break;
                                case 'input_radio':
                                case 'input_radio_image':
                                case 'select':
                                    $availableOptions = $field->options
                                        ->pluck('value')
                                        ->toArray();

                                    if (! empty($field->value)) {
                                        $availableOptions[] = $field->value;
                                    }

                                    $rules[] = Rule::in($availableOptions);

                                    break;
                                case 'input_text':
                                case 'textarea':
                                default:
                                    $rules[] = 'string';

                                    break;
                            }

                            $validationRules[$field->key] = $rules;
                        });
                    });

                    if (! Validator::make($fields->toArray(), $validationRules)->fails()) {
                        $fails = $queueItem->fails + 1;

                        $queueItem->update([
                            'verified' => false,
                            'invalid'  => true,
                            'fails'    => $fails,
                        ]);

                        ShopOrderQueueHistory::create([
                            'order_id' => $queueItem->id,
                            'type'     => 'warning',
                            'message'  => 'Field validation failed.',
                        ]);

                        if ($fails >= 3) {
                            ShopOrderQueueHistory::create([
                                'order_id' => $queueItem->id,
                                'type'     => 'danger',
                                'message'  => 'Setup failed and won\'t be executed again.',
                            ]);
                        }
                    } else {
                        $queueItem->update([
                            'verified' => true,
                            'invalid'  => false,
                        ]);

                        ShopOrderQueueHistory::create([
                            'order_id' => $queueItem->id,
                            'type'     => 'success',
                            'message'  => 'Field validation succeeded.',
                        ]);
                    }
                });

            ShopOrderQueue::where('product_type', '=', $handler->technicalName())
                ->where('verified', '=', true)
                ->where('invalid', '=', false)
                ->where('approved', '=', true)
                ->where('disapproved', '=', false)
                ->where('setup', '=', false)
                ->where('fails', '<', 3)
                ->each(function (ShopOrderQueue $queueItem) {
                    try {
                        $contractExisted = false;

                        if (! empty($contract = $queueItem->createContract($contractExisted))) {
                            $queueItem->update([
                                'contract_id' => $contract->id,
                            ]);

                            if (! $contractExisted) {
                                ShopOrderQueueHistory::create([
                                    'order_id' => $queueItem->id,
                                    'type'     => 'success',
                                    'message'  => 'Contract creation succeeded.',
                                ]);
                            } else {
                                ShopOrderQueueHistory::create([
                                    'order_id' => $queueItem->id,
                                    'type'     => 'warning',
                                    'message'  => 'Contract already exists. Skipping...',
                                ]);
                            }

                            if ($queueItem->createProduct()) {
                                ShopOrderQueueHistory::create([
                                    'order_id' => $queueItem->id,
                                    'type'     => 'success',
                                    'message'  => 'Product creation succeeded.',
                                ]);

                                if ($contract->start()) {
                                    $queueItem->update([
                                        'setup' => true,
                                    ]);

                                    ShopOrderQueueHistory::create([
                                        'order_id' => $queueItem->id,
                                        'type'     => 'warning',
                                        'message'  => 'Contract starting process succeeded.',
                                    ]);
                                } else {
                                    $fails = $queueItem->fails + 1;

                                    ShopOrderQueueHistory::create([
                                        'order_id' => $queueItem->id,
                                        'type'     => 'warning',
                                        'message'  => 'Contract starting process failed.',
                                    ]);

                                    $queueItem->update([
                                        'fails' => $fails,
                                    ]);

                                    if ($fails >= 3) {
                                        ShopOrderQueueHistory::create([
                                            'order_id' => $queueItem->id,
                                            'type'     => 'danger',
                                            'message'  => 'Setup failed and won\'t be executed again.',
                                        ]);

                                        $queueItem->sendEmailSetupFailedNotification();
                                    }
                                }
                            } else {
                                $fails = $queueItem->fails + 1;

                                $queueItem->update([
                                    'fails' => $fails,
                                ]);

                                ShopOrderQueueHistory::create([
                                    'order_id' => $queueItem->id,
                                    'type'     => 'warning',
                                    'message'  => 'Product creation failed.',
                                ]);

                                if ($fails >= 3) {
                                    ShopOrderQueueHistory::create([
                                        'order_id' => $queueItem->id,
                                        'type'     => 'danger',
                                        'message'  => 'Setup failed and won\'t be executed again.',
                                    ]);

                                    $queueItem->sendEmailSetupFailedNotification();
                                }
                            }
                        } else {
                            $fails = $queueItem->fails + 1;

                            ShopOrderQueueHistory::create([
                                'order_id' => $queueItem->id,
                                'type'     => 'warning',
                                'message'  => 'Contract creation failed.',
                            ]);

                            $queueItem->update([
                                'fails' => $fails,
                            ]);

                            if ($fails >= 3) {
                                ShopOrderQueueHistory::create([
                                    'order_id' => $queueItem->id,
                                    'type'     => 'danger',
                                    'message'  => 'Setup failed and won\'t be executed again.',
                                ]);

                                $queueItem->sendEmailSetupFailedNotification();
                            }
                        }
                    } catch (Exception $exception) {
                        $fails = $queueItem->fails + 1;

                        $queueItem->update([
                            'fails' => $fails,
                        ]);

                        if ($fails >= 3) {
                            ShopOrderQueueHistory::create([
                                'order_id' => $queueItem->id,
                                'type'     => 'danger',
                                'message'  => 'Setup failed and won\'t be executed again.',
                            ]);

                            $queueItem->sendEmailSetupFailedNotification();
                        }
                    }
                });
        }
    }

    /**
     * Define tags which the job can be identified by.
     *
     * @return array
     */
    public function tags(): array
    {
        return $this->injectTenantTags([
            'job',
            'job:tenant',
            'job:tenant:ShopOrderQueueSetup',
            'job:tenant:ShopOrderQueueSetup:' . $this->type,
        ]);
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function getUniqueIdentifier(): string
    {
        return 'shop-orders-setup-' . $this->type . '-' . $this->tenant_id;
    }
}
