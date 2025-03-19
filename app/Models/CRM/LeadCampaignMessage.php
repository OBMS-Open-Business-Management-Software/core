<?php

declare(strict_types=1);

namespace App\Models\CRM;

use App\Models\CRM\Campaign\CampaignMessage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LeadCampaignMessage.
 *
 * This class is the model for lead campaign messages.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                  $id
 * @property int                  $lead_id
 * @property int                  $campaign_message_id
 * @property Carbon               $sent_at
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon               $deleted_at
 * @property Lead|null            $lead
 * @property CampaignMessage|null $campaignMessage
 */
class LeadCampaignMessage extends Model
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
        'sent_at' => 'datetime',
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

    /**
     * Relation to campaign message.
     *
     * @return HasOne
     */
    public function campaignMessage(): HasOne
    {
        return $this->hasOne(CampaignMessage::class, 'id', 'campaign_message_id');
    }
}
