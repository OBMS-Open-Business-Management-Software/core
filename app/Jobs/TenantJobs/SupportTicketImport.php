<?php

namespace App\Jobs\TenantJobs;

use App\Helpers\IMAP;
use App\Helpers\Tags;
use App\Jobs\Structure\TenantJob;
use App\Jobs\Structure\UniquelyQueueable;
use App\Models\FileManager\File;
use App\Models\Support\Category\SupportCategory;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketFile;
use App\Models\Support\SupportTicketHistory;
use App\Models\Support\SupportTicketMessage;
use Carbon\Carbon;
use finfo;
use stdClass;

/**
 * Class SupportTicketImport
 *
 * This class is the tenant job for importing ticket metadata via. IMAP inboxes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class SupportTicketImport extends TenantJob
{
    use UniquelyQueueable;

    public $tries = 1;
    public $timeout = 3600;
    public static $onQueue = 'support_ticket_import';

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
        SupportCategory::whereHas('imapInbox')->each(function (SupportCategory $category) {
            /* Get IMAP inbox content */
            IMAP::getInbox(
                $category->imapInbox->host,
                $category->imapInbox->username,
                $category->imapInbox->password,
                $category->imapInbox->port ?? 143,
                $category->imapInbox->protocol ?? 'tls',
                $category->imapInbox->validate_cert ?? false,
                $category->imapInbox->folder ?? 'INBOX',
                true
            )->each(function (stdClass $mail) use ($category) {
                /* Check reference to other tickets */
                /* @var SupportTicket $ticket */
                if (
                    ! empty($reference = Tags::getStringBetween($mail->subject, '[Ticket ', ']')) &&
                    ! empty($ticket = SupportTicket::find($reference)) &&
                    $ticket->email === $mail->from
                ) {
                    SupportTicketMessage::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => null,
                        'message' => $mail->message,
                        'note' => false,
                        'external' => true,
                        'imap_name' => $mail->from_name,
                        'imap_email' => $mail->from
                    ]);
                } else {
                    /* @var SupportTicket $ticket */
                    $ticket = SupportTicket::create([
                        'category_id' => $category->id,
                        'subject' => $mail->subject,
                        'priority' => $category->priority,
                    ]);

                    SupportTicketMessage::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => null,
                        'message' => $mail->message,
                        'note' => false,
                        'external' => true,
                    ]);

                    SupportTicketHistory::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => null,
                        'type' => 'status',
                        'action' => 'open',
                    ]);

                    $ticket->sendEmailCreationNotification();
                }

                if (! empty($mail->attachments)) {
                    collect($mail->attachments)
                        ->reject(function (array $file) {
                            return $file['is_attachment'] !== 1;
                        })
                        ->each(function (array $file) use ($ticket) {
                            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                            $fileMime = $fileInfo->buffer($file['attachment']);

                            $file = File::create([
                                'user_id' => null,
                                'folder_id' => null,
                                'name' => Carbon::now()->format('YmdHis') . '_' . $file['filename'],
                                'data' => $file['attachment'],
                                'mime' => $fileMime,
                                'size' => strlen($file['attachment']),
                            ]);

                            if ($file instanceof File) {
                                SupportTicketFile::create([
                                    'ticket_id' => $ticket->id,
                                    'user_id' => null,
                                    'file_id' => $file->id,
                                    'external' => true,
                                ]);

                                SupportTicketHistory::create([
                                    'ticket_id' => $ticket->id,
                                    'user_id' => null,
                                    'type' => 'file',
                                    'action' => 'add',
                                    'reference' => $file->id,
                                ]);
                            }
                        });
                }
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
            'job:tenant:SupportTicketImport',
        ]);
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function getUniqueIdentifier(): string
    {
        return 'support-ticket-import-' . $this->tenant_id;
    }
}
