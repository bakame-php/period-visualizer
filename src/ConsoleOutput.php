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

use Bakame\Period\Visualizer\Contract\Output;
use Bakame\Period\Visualizer\Contract\Writer;
use Closure;
use League\Period\Period;
use League\Period\Sequence;
use function array_column;
use function array_fill;
use function array_map;
use function array_splice;
use function ceil;
use function count;
use function floor;
use function implode;
use function max;
use function str_pad;
use const STDOUT;

/**
 * A class to output to the console the matrix.
 */
final class ConsoleOutput implements Output
{
    private const TOKEN_SPACE = 0;

    private const TOKEN_BODY = 1;

    private const TOKEN_START_INCLUDED = 2;

    private const TOKEN_START_EXCLUDED = 3;

    private const TOKEN_END_INCLUDED = 4;

    private const TOKEN_END_EXCLUDED = 5;

    private const TOKEN_TO_METHOD = [
        self::TOKEN_SPACE => 'space',
        self::TOKEN_BODY => 'body',
        self::TOKEN_START_EXCLUDED => 'startExcluded',
        self::TOKEN_START_INCLUDED => 'startIncluded',
        self::TOKEN_END_INCLUDED => 'endIncluded',
        self::TOKEN_END_EXCLUDED => 'endExcluded',
    ];

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var ConsoleConfig
     */
    private $config;

    /**
     * @var float
     */
    private $start;

    /**
     * @var float
     */
    private $unit;

    /**
     * New instance.
     *
     * @param ?ConsoleConfig $config
     * @param ?Writer        $writer
     */
    public function __construct(?ConsoleConfig $config = null, ?Writer $writer = null)
    {
        $this->config = $config ?? new ConsoleConfig();
        $this->writer = $writer ?? new ConsoleStdout(STDOUT);
    }

    /**
     * {@inheritDoc}
     */
    public function display(iterable $blocks): int
    {
        $matrix = $this->buildMatrix($blocks);
        if ([] === $matrix) {
            return 0;
        }

        $bytes = 0;
        foreach ($this->format($matrix) as [$line, $color]) {
            $bytes += $this->writer->writeln(' '.$this->writer->colorize($line, $color));
        }

        return $bytes;
    }

    /**
     * Build a 2D table such that:.
     *
     * - There's one row for every block.
     * - There's one column for every unit of width.
     * - Cell state depends on Period presence and boundary type.
     */
    private function buildMatrix(iterable $blocks): array
    {
        $matrix = [];
        $boundaries = $this->getBoundaries($blocks);
        if (null === $boundaries) {
            return $matrix;
        }

        $this->start = $boundaries->getStartDate()->getTimestamp();
        $this->unit = $this->config->width() / $boundaries->getTimestampInterval();
        $row = array_fill(0, $this->config->width(), self::TOKEN_SPACE);
        $callable = Closure::fromCallable([$this, 'addPeriodToRow']);
        foreach ($blocks as [$name, $block]) {
            if ($block instanceof Period) {
                $matrix[] = [$name, $callable($row, $block)];
            } elseif ($block instanceof Sequence) {
                $matrix[] = [$name, $block->reduce($callable, $row)];
            }
        }

        return $matrix;
    }

    /**
     * Gets the boundary encompassing all visualized intervals.
     */
    private function getBoundaries(iterable $blocks): ?Period
    {
        $sequence = new Sequence();
        foreach ($blocks as [$name, $block]) {
            if ($block instanceof Period) {
                $sequence->push($block);
            } elseif ($block instanceof Sequence) {
                $sequence->push(...$block);
            }
        }

        return $sequence->boundaries();
    }

    /**
     * Converts and add a Period to the matrix row.
     *
     * The conversion depends on the period presence and boundaries.
     *
     * @param int[] $row
     *
     * @return int[]
     */
    private function addPeriodToRow(array $row, Period $period): array
    {
        $startIndex = (int) floor(($period->getStartDate()->getTimestamp() - $this->start) * $this->unit);
        $endIndex = (int) ceil(($period->getEndDate()->getTimestamp() - $this->start) * $this->unit);
        $periodLength = $endIndex - $startIndex;

        array_splice($row, $startIndex, $periodLength, array_fill(0, $periodLength, self::TOKEN_BODY));
        $row[$startIndex] = $period->isStartIncluded() ? self::TOKEN_START_INCLUDED : self::TOKEN_START_EXCLUDED;
        $row[$endIndex - 1] = $period->isEndIncluded() ? self::TOKEN_END_INCLUDED : self::TOKEN_END_EXCLUDED;

        return $row;
    }

    /**
     * Builds an Iterator to visualize one or more
     * periods and/or collections in a more
     * human readable / parsable manner.
     *
     * The submitted array values represent a tuple where
     * the first value is the identifier and the second value
     * the intervals represented as Period or Sequence instances.
     *
     * This method returns one output string line at a time.
     */
    private function format(array $matrix): iterable
    {
        $nameLength = max(...array_map('strlen', array_column($matrix, 0)));
        $colorCodeIndexes = $this->config->colors();
        $colorCodeCount = count($colorCodeIndexes);
        $key = -1;
        foreach ($matrix as [$name, $row]) {
            $prefix = str_pad($name, $nameLength, ' ');
            $data = implode('', array_map([$this, 'convertMatrixValue'], $row));
            $line = $prefix.' '.$data;
            $color = $colorCodeIndexes[++$key % $colorCodeCount];

            yield [$line, $color];
        }
    }

    /**
     * Turns the matrix values into characters representing the interval.
     */
    private function convertMatrixValue(int $token): string
    {
        return $this->config->{self::TOKEN_TO_METHOD[$token]}();
    }
}
