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
    public function display(Tuple $tuple): void
    {
        if ($tuple->isEmpty()) {
            return;
        }

        $matrix = $this->buildMatrix($tuple);
        foreach ($this->matrixToLine($matrix) as $line) {
            $this->writer->writeln($line);
        }
    }

    /**
     * Build a 2D table such that:.
     *
     * - There's one row for every block.
     * - There's one column for every unit of width.
     * - Cell state depends on Period presence and boundary type.
     *
     * @return array<int, array{0:string, 1:int[]}>
     */
    private function buildMatrix(Tuple $tuple): array
    {
        $matrix = [];
        /** @var Period $boundaries */
        $boundaries = $tuple->boundaries();
        $width = $this->config->width();
        $row = array_fill(0, $width, self::TOKEN_SPACE);
        $this->start = $boundaries->getStartDate()->getTimestamp();
        $this->unit = $width / $boundaries->getTimestampInterval();
        $callable = Closure::fromCallable([$this, 'addPeriodToRow']);
        foreach ($tuple as [$name, $block]) {
            if ($block instanceof Period) {
                $matrix[] = [$name, $callable($row, $block)];
            } elseif ($block instanceof Sequence) {
                $matrix[] = [$name, $block->reduce($callable, $row)];
            }
        }

        return $matrix;
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
     *
     * @return string[]
     */
    private function matrixToLine(array $matrix): iterable
    {
        $nameLength = max(...array_map('strlen', array_column($matrix, 0)));
        $colorCodeIndexes = $this->config->colors();
        $colorCodeCount = count($colorCodeIndexes);
        $key = -1;
        $padding = $this->config->padding();
        $gap = $this->config->gap();
        foreach ($matrix as [$name, $row]) {
            $lineName = str_pad($name, $nameLength, ' ', $padding);
            $lineContent = implode('', array_map([$this, 'tokenToCharacters'], $row));
            $color = $colorCodeIndexes[++$key % $colorCodeCount];

            yield ' '.$this->writer->colorize($lineName.$gap.$lineContent, $color);
        }
    }

    /**
     * Turns the matrix values into characters representing the interval.
     */
    private function tokenToCharacters(int $token): string
    {
        return $this->config->{self::TOKEN_TO_METHOD[$token]}();
    }
}
