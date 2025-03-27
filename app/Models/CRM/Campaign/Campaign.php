<?php

declare(strict_types=1);

namespace App\Models\CRM\Campaign;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Campaign.
 *
 * This class is the model for campaigns.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                              $id
 * @property string                           $name
 * @property string                           $description
 * @property Carbon                           $created_at
 * @property Carbon                           $updated_at
 * @property Carbon                           $deleted_at
 * @property Collection<CampaignLead>         $leads
 * @property Collection<CampaignLeadCategory> $categoryLinks
 */
class Campaign extends Model
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
     * Relation to leads.
     *
     * @return HasMany
     */
    public function leads(): HasMany
    {
        return $this->hasMany(CampaignLead::class, 'campaign_id', 'id');
    }

    /**
     * Relation to lead categories.
     *
     * @return HasMany
     */
    public function categoryLinks(): HasMany
    {
        return $this->hasMany(CampaignLeadCategory::class, 'campaign_id', 'id');
    }
}
