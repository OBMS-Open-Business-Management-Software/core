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
                $groupingMethod      = 'subHours';
                $groupingStartMethod = 'startOfHour';
                $groupingEndMethod   = 'endOfHour';
                $groupLabelFormat    = 'H:i';

                break;
            case 'day':
                $groupingMethod      = 'subDays';
                $groupingStartMethod = 'startOfDay';
                $groupingEndMethod   = 'endOfDay';
                $groupLabelFormat    = 'd.m.Y';

                break;
            case 'month':
            default:
                $groupingMethod      = 'subMonths';
                $groupingStartMethod = 'startOfMonth';
                $groupingEndMethod   = 'endOfMonth';
                $groupLabelFormat    = 'F Y';

                break;
            case 'year':
                $groupingMethod      = 'subYears';
                $groupingStartMethod = 'startOfYear';
                $groupingEndMethod   = 'endOfYear';
                $groupLabelFormat    = 'Y';

                break;
        }

        $now = Carbon::now();

        if ($now->format('d') >= 28 && $grouping === 'month') {
            $now->setDay(28);
        }

        $timeframes = collect();

        collect(range(1, $back - 1))->each(function ($subtract) use ($groupingMethod, $groupingStartMethod, $groupingEndMethod, $groupLabelFormat, $timeframes, $now) {
            $start = (clone $now)->{$groupingMethod}($subtract)->{$groupingStartMethod}();
            $end   = (clone $now)->{$groupingMethod}($subtract)->{$groupingEndMethod}();

            $timeframes->push((object) [
                'start' => $start,
                'end'   => $end,
                'label' => $start->format($groupLabelFormat),
            ]);
        });

        return $timeframes
            ->reverse()
            ->values()
            ->push((object) [
                'start' => (clone $now)->{$groupingStartMethod}(),
                'end'   => (clone $now)->{$groupingEndMethod}(),
                'label' => (clone $now)->format('F Y'),
            ]);
    }
}
