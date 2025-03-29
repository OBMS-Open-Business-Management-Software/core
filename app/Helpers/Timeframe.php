<?php

declare(strict_types=1);

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class Timeframe.
 *
 * This class is the helper for timeframe calculation.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Timeframe
{
    public static function getPastTimeframes(int $back = 12, string $grouping = 'month'): Collection
    {
        switch ($grouping) {
            case 'hour':
                $groupingMethod = 'subHours';

                break;
            case 'day':
                $groupingMethod = 'subDays';

                break;
            case 'month':
            default:
                $groupingMethod = 'subMonths';

                break;
            case 'year':
                $groupingMethod = 'subYears';

                break;
        }

        $now = Carbon::now();

        $timeframes = collect();

        for ($i = $back - 2; $i >= 0; $i--) {
            $start = (clone $now)->{$groupingMethod}($i)->startOfMonth();
            $end   = (clone $now)->{$groupingMethod}($i)->endOfMonth();

            $timeframes->push((object) [
                'start' => $start,
                'end'   => $end,
                'label' => $start->format('F Y'),
            ]);
        }

        return $timeframes->push((object) [
            'start' => (clone $now)->startOfMonth(),
            'end'   => $now,
            'label' => (clone $now)->format('F Y'),
        ]);
    }
}
