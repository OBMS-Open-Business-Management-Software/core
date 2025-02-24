<?php

declare(strict_types=1);

namespace App\Models\Shop\OrderQueue;

use App\Emails\Shop\OrderNewPendingApproval;
use App\Emails\Shop\OrderNewSuccessfulApproval;
use App\Emails\Shop\OrderSetupFailed;
use App\Emails\Shop\OrderSuccessfulApproval;
use App\Emails\Shop\OrderUnsuccessfulApproval;
use App\Emails\Shop\OrderUpdatePendingApproval;
use App\Emails\Shop\OrderUpdateSuccessfulApproval;
use App\Emails\Shop\ProductLockFailure;
use App\Emails\Shop\ProductLockSuccessful;
use App\Emails\Shop\ProductRemovalSuccessful;
use App\Emails\Shop\ProductUnlockFailure;
use App\Emails\Shop\ProductUnlockSuccessful;
use App\Helpers\Products;
use App\Models\Accounting\Contract\Contract;
use App\Models\Accounting\Contract\ContractPosition;
use App\Models\Accounting\Position;
use App\Models\Shop\Configurator\ShopConfiguratorForm;
use App\Models\UsageTracker\Tracker;
use App\Models\UsageTracker\TrackerInstance;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * Class ShopConfiguratorForm
 *
 * This class is the model for basic shop order metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                               $id
 * @property int|null                          $user_id
 * @property int|null                          $form_id
 * @property int|null                          $contract_id
 * @property int|null                          $tracker_id
 * @property string                            $product_type
 * @property float                             $amount
 * @property float                             $vat_percentage
 * @property bool                              $reverse_charge
 * @property bool                              $verified
 * @property bool                              $invalid
 * @property bool                              $approved
 * @property bool                              $disapproved
 * @property bool                              $setup
 * @property int                               $fails
 * @property bool                              $locked
 * @property bool                              $deleted
 * @property Carbon                            $created_at
 * @property Carbon                            $updated_at
 * @property Carbon                            $deleted_at
 * @property User|null                         $user
 * @property ShopConfiguratorForm|null         $form
 * @property Contract|null                     $contract
 * @property Tracker|null                      $tracker
 * @property Collection<ShopOrderQueueField>   $fields
 * @property Collection<ShopOrderQueueHistory> $history
 * @property string                            $number
 * @property mixed                             $handler
 * @property string                            $email
 */
class ShopOrderQueue extends Model
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_order_queue';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'reverse_charge' => 'bool',
        'verified'       => 'bool',
        'approved'       => 'bool',
        'disapproved'    => 'bool',
        'setup'          => 'bool',
        'locked'         => 'bool',
        'deleted'        => 'bool',
    ];

    /**
     * Relation to user.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relation to form.
     *
     * @return HasOne
     */
    public function form(): HasOne
    {
        return $this->hasOne(ShopConfiguratorForm::class, 'id', 'form_id');
    }

    /**
     * Relation to contract.
     *
     * @return HasOne
     */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'id', 'contract_id');
    }

    /**
     * Relation to tracker.
     *
     * @return HasOne
     */
    public function tracker(): HasOne
    {
        return $this->hasOne(Tracker::class, 'id', 'tracker_id');
    }

    /**
     * Relation to fields.
     *
     * @return HasMany
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ShopOrderQueueField::class, 'order_id', 'id');
    }

    /**
     * Relation to history.
     *
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(ShopOrderQueueHistory::class, 'order_id', 'id');
    }

    /**
     * Get order number.
     *
     * @return string
     */
    public function getNumberAttribute(): string
    {
        return 'O' . $this->created_at->format('Ymd') . $this->id;
    }

    /**
     * Get product handler.
     *
     * @return mixed
     */
    public function getHandlerAttribute()
    {
        if (empty($this->product_type)) {
            return null;
        }

        return Products::list()->filter(function ($handler) {
            return $handler->technicalName() === $this->product_type;
        })->first();
    }

    /**
     * Get email address to send notifications to.
     *
     * @return string
     */
    public function getEmailAttribute(): string
    {
        return $this->user->contactEmailAddress->email ?? $this->user->email;
    }

    /**
     * Create contract for order.
     *
     * @param bool $existed
     *
     * @return Contract|null
     */
    public function createContract(bool &$existed = false): ?Contract
    {
        /* @var Contract $contract */
        if (! empty($contract = $this->contract)) {
            $existed = true;

            return $contract;
        }

        if (
            ! empty(
                $contract = Contract::create([
                    'user_id'                 => $this->user_id,
                    'type_id'                 => $this->form->contract_type_id,
                    'reserved_prepaid_amount' => in_array($this->form->contractType->type, [
                        'prepaid_auto',
                        'prepaid_manual',
                    ]) ? $this->amount * (100 + $this->vat_percentage) / 100 : 0,
                ])
            )
        ) {
            /* @var Position $position */
            // TODO: Try to split positions to show product settings (if possible)
            if (
                ! empty(
                    $position = Position::create([
                        'order_id'       => $this->id,
                        'product_id'     => $this->form_id,
                        'discount_id'    => null,
                        'name'           => __('interface.misc.shop_order') . ' #' . $this->id,
                        'description'    => __($this->form->name),
                        'amount'         => $this->amount,
                        'vat_percentage' => $this->vat_percentage,
                        'quantity'       => 1,
                    ])
                )
            ) {
                $positionLink = ContractPosition::create([
                    'contract_id' => $contract->id,
                    'position_id' => $position->id,
                ]);

                if (! empty($this->tracker_id)) {
                    TrackerInstance::create([
                        'contract_id'          => $contract->id,
                        'contract_position_id' => $positionLink->id,
                        'tracker_id'           => $this->tracker_id ?? null,
                    ]);
                }

                return $contract;
            } else {
                $contract->delete();
            }
        }

        return null;
    }

    /**
     * Create product for order.
     *
     * @return Contract|null
     */
    public function createProduct(): ?bool
    {
        if (! empty($this->handler)) {
            return $this->handler->create($this->fields->toBase());
        }

        return null;
    }

    /**
     * Send the email creation with pending approval notification.
     */
    public function sendEmailCreationPendingApprovalNotification(): void
    {
        try {
            $this->notify(new OrderNewPendingApproval());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email creation with successful approval notification.
     */
    public function sendEmailCreationSuccessfulApprovalNotification(): void
    {
        try {
            $this->notify(new OrderNewSuccessfulApproval());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email successful approval notification.
     */
    public function sendEmailSuccessfulApprovalNotification(): void
    {
        try {
            $this->notify(new OrderSuccessfulApproval());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email unsuccessful approval notification.
     */
    public function sendEmailUnsuccessfulApprovalNotification(): void
    {
        try {
            $this->notify(new OrderUnsuccessfulApproval());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email setup failed notification.
     */
    public function sendEmailSetupFailedNotification(): void
    {
        try {
            $this->notify(new OrderSetupFailed());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email update with pending approval notification.
     */
    public function sendEmailUpdatePendingApprovalNotification(): void
    {
        try {
            $this->notify(new OrderUpdatePendingApproval());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email update with successful approval notification.
     */
    public function sendEmailUpdateSuccessfulApprovalNotification(): void
    {
        try {
            $this->notify(new OrderUpdateSuccessfulApproval());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email removal successful notification.
     */
    public function sendEmailRemovalNotification(): void
    {
        try {
            $this->notify(new ProductRemovalSuccessful());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email lock successful notification.
     */
    public function sendEmailLockNotification(): void
    {
        try {
            $this->notify(new ProductLockSuccessful());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email lock failure notification.
     */
    public function sendEmailLockFailedNotification(): void
    {
        try {
            $this->notify(new ProductLockFailure());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email unlock successful notification.
     */
    public function sendEmailUnlockNotification(): void
    {
        try {
            $this->notify(new ProductUnlockSuccessful());
        } catch (Exception | Error $exception) {
        }
    }

    /**
     * Send the email unlock failure notification.
     */
    public function sendEmailUnlockFailedNotification(): void
    {
        try {
            $this->notify(new ProductUnlockFailure());
        } catch (Exception | Error $exception) {
        }
    }
}
