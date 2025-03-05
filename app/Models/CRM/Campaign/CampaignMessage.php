<?php

declare(strict_types=1);

namespace App\Models\CRM\Campaign;

use App\Models\CRM\Campaign\Campaign;
use App\Models\CRM\Campaign\LeadCampaignMessage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CampaignMessage.
 *
 * This class is the model for campaign messages.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 * 
 * @property int                  $id
 * @property int                  $campaign_id
 * @property string               $channel
 * @property string               $message
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon               $deleted_at
 * @property Campaign|null        $campaign
 * @property Collection<LeadCampaignMessage> $leadCampaignMessages
 */
class CampaignMessage extends Model
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
     * Relation to lead campaign messages.
     *
     * @return HasMany
     */
    public function leadCampaignMessages(): HasMany
    {
        return $this->hasMany(LeadCampaignMessage::class, 'campaign_message_id', 'id');
    }

    /**
     * Relation to campaign.
     *
     * @return HasOne
     */
    public function campaign(): HasOne
    {
        return $this->hasOne(Campaign::class, 'id', 'campaign_id');
    }
}