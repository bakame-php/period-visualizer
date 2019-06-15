<?php

/**
 * League.Period Visualizer (https://github.com/bakame-php/period-visualizer)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bakame\Period\Visualizer;

use League\Period\Period;
use League\Period\Sequence;
use function array_fill;
use function array_reduce;
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
     * @var float
     */
    private static $start;

    /**
     * @var float
     */
    private static $unit;

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
     *
     * @return array<int, array<int|string, bool[]>>
     */
    public static function build(array $blocks, int $width): array
    {
        $matrix = [];
        $boundaries = self::getBoundaries($blocks);
        if (null === $boundaries) {
            return $matrix;
        }

        self::$start = $boundaries->getStartDate()->getTimestamp();
        self::$unit = $width / $boundaries->getTimestampInterval();
        $baseRow = array_fill(0, $width, false);
        foreach ($blocks as [$name, $block]) {
            if (!$block instanceof Sequence) {
                $matrix[] = [$name, self::populateRow($baseRow, $block)];
                continue;
            }

            $matrix[] = [$name, array_reduce($block->toArray(), [self::class, 'populateRow'], $baseRow)];
        }

        return $matrix;
    }

    /**
     * Gets the bounds encompassing all visualized periods.
     */
    private static function getBoundaries(array $blocks): ?Period
    {
        $sequence = new Sequence();
        foreach ($blocks as [$name, $block]) {
            if ($block instanceof Period) {
                $block = [$block];
            }

            if (0 !== count($block)) {
                $sequence->push(...$block);
            }
        }

        return $sequence->boundaries();
    }

    /**
     * Populates a row with true values where periods are active.
     *
     * @param bool[] $row
     *
     * @return bool[]
     */
    private static function populateRow(array $row, Period $period): array
    {
        $startIndex = floor(($period->getStartDate()->getTimestamp() - self::$start) * self::$unit);
        $endIndex = ceil(($period->getEndDate()->getTimestamp() - self::$start) * self::$unit);
        foreach ($row as $index => &$value) {
            if ($startIndex <= $index && $index < $endIndex) {
                $value = true;
            }
        }
        unset($value);

        return $row;
    }
}
