<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NumberRanges.
 *
 * This class is the helper for handling number ranges.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class NumberRanges
{
    /**
     * Get a number for a model.
     *
     * @param string $type
     * @param Model  $model
     *
     * @return string
     */
    public static function getNumber(string $type, Model $model): string
    {
        $config = config('number_ranges.' . $type);

        if (empty($config)) {
            return (string) $model->id;
        }

        $result = '';

        if (! empty($config['prefix'])) {
            $result = $config['prefix'];
        }

        if ($config['date']['prepend']) {
            $result .= $model->created_at->format($config['date']['format']);
        }

        $id = $model->id + $config['increment']['reserved'];

        if (! empty($config['increment']['group_by'])) {
            switch ($config['increment']['group_by']) {
                case 'year':
                    $start = 'startOfYear';
                    $end   = 'endOfYear';

                    break;
                case 'month':
                    $start = 'startOfMonth';
                    $end   = 'endOfMonth';

                    break;
                case 'week':
                    $start = 'startOfWeek';
                    $end   = 'endOfWeek';

                    break;
                case 'day':
                default:
                    $start = 'startOfDay';
                    $end   = 'endOfDay';

                    break;
            }

            $reserved = ($model::class)::where('created_at', '>=', $model->created_at->{$start}())
                ->where('created_at', '<=', $model->created_at->{$end}())
                ->where('id', '<', $model->id)
                ->withTrashed()
                ->count();

            $id = $reserved + 1;
        }

        $result .= $id;

        return $result;
    }
}
