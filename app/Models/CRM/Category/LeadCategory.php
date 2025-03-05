<?php

declare(strict_types=1);

namespace App\Models\CRM\Category;

use App\Models\CRM\Campaign\CampaignLeadCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LeadCategory.
 *
 * This class is the model for basic category metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                                   $id
 * @property string                                $name
 * @property string                                $description
 * @property Carbon                                $created_at
 * @property Carbon                                $updated_at
 * @property Carbon                                $deleted_at
 * @property Collection<LeadCategoryAssignment>    $assignments
 * @property Collection<CampaignLeadCategory>      $campaignLinks
 */
class LeadCategory extends Model
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
     * Relation to assignments.
     *
     * @return HasMany
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(LeadCategoryAssignment::class, 'category_id', 'id');
    }

    /**
     * Relation to campaign links.
     *
     * @return HasMany
     */
    public function campaignLinks(): HasMany
    {
        return $this->hasMany(CampaignLeadCategory::class, 'category_id', 'id');
    }
}
