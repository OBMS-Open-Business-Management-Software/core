<?php

declare(strict_types=1);

namespace App\Models\CRM\Campaign;

use App\Models\CRM\Campaign\Campaign;
use App\Models\CRM\Category\LeadCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CampaignLeadCategory.
 *
 * This class is the model for linking campaigns with lead categories.	
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                  $id
 * @property int                  $campaign_id
 * @property int                  $category_id
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon               $deleted_at
 * @property Campaign|null        $campaign
 * @property LeadCategory|null    $category
 */
class CampaignLeadCategory extends Model
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
     * Relation to lead category.
     *
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(LeadCategory::class, 'id', 'category_id');
    }
}