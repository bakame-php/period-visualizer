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
use function array_splice;
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
    public const TOKEN_BODY = 1;

    public const TOKEN_SPACE = 0;

    public const TOKEN_START_INCLUDED = 2;

    public const TOKEN_START_EXCLUDED = 3;

    public const TOKEN_END_INCLUDED = 4;

    public const TOKEN_END_EXCLUDED = 5;

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
     * - Cell state depends on Period presence and boundary type.
     *
     */
    public static function build(iterable $blocks, int $width): array
    {
        $matrix = [];
        $boundaries = self::getBoundaries($blocks);
        if (null === $boundaries) {
            return $matrix;
        }

        self::$start = $boundaries->getStartDate()->getTimestamp();
        self::$unit = $width / $boundaries->getTimestampInterval();
        $row = array_fill(0, $width, self::TOKEN_SPACE);
        foreach ($blocks as [$name, $block]) {
            if ($block instanceof Period) {
                $matrix[] = [$name, self::addPeriodToRow($row, $block)];
                continue;
            }

            $matrix[] = [$name, array_reduce($block->toArray(), [self::class, 'addPeriodToRow'], $row)];
        }

        return $matrix;
    }

    /**
     * Gets the bounds encompassing all visualized periods.
     */
    private static function getBoundaries(iterable $blocks): ?Period
    {
        $sequence = new Sequence();
        foreach ($blocks as [$name, $block]) {
            if ($block instanceof Period) {
                $sequence->push($block);
                continue;
            }

            if (0 !== count($block)) {
                $sequence->push(...$block);
            }
        }

        return $sequence->boundaries();
    }

    /**
     * Converts and add a Period to the matrix row.
     *
     * The conversion is done depending on the period presence and boundaries.
     *
     * @param int[] $row
     *
     * @return int[]
     */
    private static function addPeriodToRow(array $row, Period $period): array
    {
        $startIndex = (int) floor(($period->getStartDate()->getTimestamp() - self::$start) * self::$unit);
        $endIndex = (int) ceil(($period->getEndDate()->getTimestamp() - self::$start) * self::$unit);
        $periodLength = $endIndex - $startIndex;

        array_splice($row, $startIndex, $periodLength, array_fill(0, $periodLength, self::TOKEN_BODY));
        $row[$startIndex] = $period->isStartIncluded() ? self::TOKEN_START_INCLUDED : self::TOKEN_START_EXCLUDED;
        $row[$endIndex - 1] = $period->isEndIncluded() ? self::TOKEN_END_INCLUDED : self::TOKEN_END_EXCLUDED;

        return $row;
    }
}
