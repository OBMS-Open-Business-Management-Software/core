<?php

declare(strict_types=1);

namespace App\Models\CRM;

use App\Models\CRM\Lead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LeadContact.
 *
 * This class is the model for lead contacts.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 * 
 * @property int                  $id
 * @property int                  $lead_id
 * @property string               $name
 * @property string               $email
 * @property string               $phone
 * @property string               $role
 * @property string               $notes
 * @property bool                 $is_primary
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon               $deleted_at
 * @property Lead|null            $lead
 */
class LeadContact extends Model
{
    use SoftDeletes;

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
        'is_primary' => 'boolean',
    ];
    
    /**
     * Relation to lead.
     *
     * @return HasOne
     */
    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class, 'id', 'lead_id');
    }
}