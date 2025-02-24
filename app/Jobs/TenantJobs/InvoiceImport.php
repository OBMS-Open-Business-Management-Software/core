<?php

declare(strict_types=1);

namespace App\Jobs\TenantJobs;

use App\Helpers\IMAP;
use App\Jobs\Structure\TenantJob;
use App\Jobs\Structure\UniquelyQueueable;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceImporter;
use App\Models\Accounting\Invoice\InvoiceImporterHistory;
use App\Models\FileManager\File;
use App\Models\User;
use Carbon\Carbon;
use finfo;
use Illuminate\Database\Eloquent\Builder;
use stdClass;

/**
 * Class InvoiceImport
 *
 * This class is the tenant job for importing invoice metadata via. IMAP inboxes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class InvoiceImport extends TenantJob
{
    use UniquelyQueueable;

    public $tries = 1;

    public $timeout = 3600;

    public static $onQueue = 'invoice_import';

    /**
     * SupportTicketImport constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        InvoiceImporter::whereHas('imapInbox')->each(function (InvoiceImporter $importer) {
            /* Get IMAP inbox content */
            IMAP::getInbox(
                $importer->imapInbox->host,
                $importer->imapInbox->username,
                $importer->imapInbox->password,
                $importer->imapInbox->port ?? 143,
                $importer->imapInbox->protocol ?? 'tls',
                $importer->imapInbox->validate_cert ?? false,
                $importer->imapInbox->folder ?? 'INBOX',
                true
            )->transform(function (stdClass $mail) {
                if (
                    ! empty(
                        $supplier = User::where('role', '=', 'supplier')
                            ->where(function (Builder $builder) use ($mail) {
                                return $builder->where('email', '=', $mail->from)
                                    ->orWhereHas('profile', function (Builder $builder) use ($mail) {
                                        return $builder->whereHas('emailAddresses', function (Builder $builder) use ($mail) {
                                            return $builder->where('email', '=', $mail->from);
                                        });
                                    });
                            })
                            ->first()
                    ) &&
                    ! empty($mail->attachments)
                ) {
                    return (object) [
                        'supplier' => $supplier,
                        'mail'     => $mail,
                    ];
                }

                return null;
            })->filter(function (stdClass $result) {
                return ! empty($result);
            })->each(function (stdClass $result) use ($importer) {
                collect($result->mail->attachments)
                    ->reject(function (array $file) {
                        return $file['is_attachment'] !== 1;
                    })
                    ->each(function (array $file) use ($result, $importer) {
                        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                        $fileMime = $fileInfo->buffer($file['attachment']);

                        $file = File::create([
                            'user_id'   => null,
                            'folder_id' => null,
                            'name'      => Carbon::now()->format('YmdHis') . '_' . $file['filename'],
                            'data'      => $file['attachment'],
                            'mime'      => $fileMime,
                            'size'      => strlen($file['attachment']),
                        ]);

                        if ($file instanceof File) {
                            $invoice = Invoice::create([
                                'user_id'        => $result->supplier->id,
                                'reverse_charge' => $result->supplier->reverseCharge,
                            ]);
                        }

                        InvoiceImporterHistory::create([
                            'importer_id' => $importer->id,
                            'invoice_id'  => isset($invoice) && $invoice instanceof Invoice ? $invoice->id : null,
                            'file_id'     => isset($file) && $file instanceof File ? $file->id : null,
                            'subject'     => $result->mail->subject,
                            'from'        => $result->mail->subject,
                            'from_name'   => $result->mail->subject,
                            'to'          => $result->mail->to,
                            'message'     => $result->mail->message,
                        ]);
                    });
            });
        });
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
            'job:tenant:InvoiceImport',
        ]);
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function getUniqueIdentifier(): string
    {
        return 'support-invoice-' . $this->tenant_id;
    }
}
