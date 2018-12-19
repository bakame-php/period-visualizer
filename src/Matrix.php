<?php

/**
 * League.Period Visualizer (https://github.com/bakame-php/period-visualizer).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bakame\Period\Visualizer;

use League\Period\Period;
use League\Period\Sequence;
use function array_combine;
use function array_fill;
use function array_keys;
use function array_values;
use function ceil;
use function count;
use function floor;

/**
 * This class is heavily influence by the work of
 * https://github.com/thecrypticace on the Visualizer class
 * for Spatie/Period package.
 */
final class Matrix
{
    /**
     * @var ConsoleConfig
     */
    private static $config;

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Build a 2D table such that:
     * - There's one row for every block.
     * - There's one column for every unit of width.
     * - Each cell is true when a period is active for that unit.
     * - Each cell is false when a period is not active for that unit.
     */
    public static function build(array $blocks, int $width): array
    {
        $boundaries = self::getBoundaries($blocks);
        if (null === $boundaries) {
            return [];
        }

        $matrix = array_fill(0, count($blocks), array_fill(0, $width, false));
        $matrix = array_combine(array_keys($blocks), array_values($matrix));

        foreach ($blocks as $name => $block) {
            if ($block instanceof Period) {
                $block = [$block];
            }

            foreach ($block as $period) {
                $matrix[$name] = self::populateRow($matrix[$name], $period, $boundaries, $width);
            }
        }

        return $matrix;
    }

    /**
     * Get the bounds encompassing all visualized periods.
     */
    private static function getBoundaries(array $blocks): ?Period
    {
        $periods = new Sequence();
        foreach ($blocks as $block) {
            if ($block instanceof Period) {
                $block = [$block];
            }

            if (0 !== count($block)) {
                $periods->push(...$block);
            }
        }

        return $periods->getBoundaries();
    }

    /**
     * Populate a row with true values where periods are active.
     */
    private static function populateRow(array $row, Period $period, Period $boundaries, int $width): array
    {
        [$startIndex, $endIndex] = self::coords($period, $boundaries, $width);
        for ($i = 0; $i < $width; $i++) {
            if ($startIndex <= $i && $i < $endIndex) {
                $row[$i] = true;
            }
        }

        return $row;
    }

    /**
     * Get the start / end coordinates for a given period.
     */
    private static function coords(Period $period, Period $boundaries, int $width): array
    {
        $boundsStart = $boundaries->getStartDate()->getTimestamp();
        $boundsLength = $boundaries->getTimestampInterval();

        // Get the bounds
        $start = ($period->getStartDate()->getTimestamp() - $boundsStart) * $width / $boundsLength;
        $end = ($period->getEndDate()->getTimestamp() - $boundsStart) * $width / $boundsLength;

        // Cap at integer intervals
        return [floor($start), ceil($end)];
    }
}
