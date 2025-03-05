<?php

declare(strict_types=1);

namespace App\Models\CRM\Category;

use App\Models\CRM\Lead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LeadCategoryAssignment.
 *
 * This class is the model for linking leads with lead category metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                  $id
 * @property int                  $category_id
 * @property int                  $lead_id
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon               $deleted_at
 * @property LeadCategory|null    $category
 * @property Lead|null            $lead
 */
class LeadCategoryAssignment extends Model
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
     * Relation to lead category.
     *
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(LeadCategory::class, 'id', 'category_id');
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
