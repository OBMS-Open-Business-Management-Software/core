<?php

declare(strict_types=1);

namespace App\Models\Accounting\Invoice;

use App\Models\FileManager\File;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class InvoiceImporterHistory
 *
 * This class is the model for basic invoice importer metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                  $id
 * @property int                  $importer_id
 * @property int|null             $invoice_id
 * @property int|null             $file_id
 * @property string               $subject
 * @property string               $from
 * @property string               $from_name
 * @property string               $to
 * @property string               $message
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon               $deleted_at
 * @property InvoiceImporter|null $importer
 * @property Invoice|null         $invoice
 * @property File|null            $file
 */
class InvoiceImporterHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_importer_history';

    /**
     * Relation to invoice importer.
     *
     * @return HasOne
     */
    public function importer(): HasOne
    {
        return $this->hasOne(InvoiceImporter::class, 'id', 'importer_id');
    }

    /**
     * Relation to invoice.
     *
     * @return HasOne
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }

    /**
     * Relation to file.
     *
     * @return HasOne
     */
    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }
}
