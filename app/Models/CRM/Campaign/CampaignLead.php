<?php

declare(strict_types=1);

namespace App\Models\CRM\Campaign;

use App\Models\CRM\Lead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CampaignLead.
 *
 * This class is the model for linking campaigns with leads.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int           $id
 * @property int           $campaign_id
 * @property int           $lead_id
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property Carbon        $deleted_at
 * @property Campaign|null $campaign
 * @property Lead|null     $lead
 */
class CampaignLead extends Model
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
     * Relation to campaign.
     *
     * @return HasOne
     */
    public function campaign(): HasOne
    {
        return $this->hasOne(Campaign::class, 'id', 'campaign_id');
    }

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
