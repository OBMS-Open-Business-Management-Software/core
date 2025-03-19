<?php

declare(strict_types=1);

namespace App\Models\CRM;

use App\Models\CRM\Campaign\CampaignLead;
use App\Models\CRM\Category\LeadCategoryAssignment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Lead.
 *
 * This class is the model for leads.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                                $id
 * @property string                             $name
 * @property string                             $email
 * @property string                             $phone
 * @property string                             $address
 * @property string                             $city
 * @property string                             $state
 * @property string                             $zip
 * @property string                             $country
 * @property string                             $notes
 * @property Carbon                             $created_at
 * @property Carbon                             $updated_at
 * @property Carbon                             $deleted_at
 * @property Collection<LeadCategoryAssignment> $categoryAssignments
 * @property Collection<CampaignLead>           $campaignLinks
 * @property Collection<LeadContact>            $contacts
 * @property LeadContact|null                   $primaryContact
 */
class Lead extends Model
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
     * Relation to category assignments.
     *
     * @return HasMany
     */
    public function categoryAssignments(): HasMany
    {
        return $this->hasMany(LeadCategoryAssignment::class, 'lead_id', 'id');
    }

    /**
     * Relation to campaign links.
     *
     * @return HasMany
     */
    public function campaignLinks(): HasMany
    {
        return $this->hasMany(CampaignLead::class, 'lead_id', 'id');
    }

    /**
     * Relation to lead contacts.
     *
     * @return HasMany
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(LeadContact::class, 'lead_id', 'id');
    }

    /**
     * Get value for computed property "primaryContact".
     *
     * @return LeadContact|null
     */
    public function getPrimaryContactAttribute(): ?LeadContact
    {
        return $this->contacts()->where('is_primary', true)->first();
    }
}
