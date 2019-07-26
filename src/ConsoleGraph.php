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

use Bakame\Period\Visualizer\Contract\Graph;
use Bakame\Period\Visualizer\Contract\OutputWriter;
use Closure;
use League\Period\Period;
use League\Period\Sequence;
use function array_fill;
use function array_splice;
use function ceil;
use function count;
use function floor;
use function implode;
use function str_pad;
use function str_repeat;
use const STDOUT;

/**
 * A class to output to the console the matrix.
 */
final class ConsoleGraph implements Graph
{
    /**
     * @var OutputWriter
     */
    private $output;

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
     * @param ?OutputWriter  $output
     */
    public function __construct(?ConsoleConfig $config = null, ?OutputWriter $output = null)
    {
        $this->config = $config ?? new ConsoleConfig();
        $this->output = $output ?? new ConsoleOutput(STDOUT);
    }

    /**
     * {@inheritDoc}
     */
    public function display(Dataset $dataset): void
    {
        $this->setScale($dataset);
        $graph = $this->drawGraph($dataset);
        $this->output->writeln($graph);
    }

    /**
     * Sets the scale to render the line.
     */
    private function setScale(Dataset $dataset): void
    {
        $this->start = 0;
        $this->unit = 1;
        $boundaries = $dataset->boundaries();
        if (null !== $boundaries) {
            $this->start = $boundaries->getStartDate()->getTimestamp();
            $this->unit = $this->config->width() / $boundaries->getTimestampInterval();
        }
    }

    /**
     * Converts a Dataset entry into a series of lines to be outputted by the OutputWriter implementation.
     *
     * @return iterable<string>
     */
    private function drawGraph(Dataset $dataset): iterable
    {
        $colorCodeIndexes = $this->config->colors();
        $colorCodeCount = count($colorCodeIndexes);
        $padding = $this->config->labelAlign();
        $gap = str_repeat(' ', $this->config->gapSize());
        $lineCharacters = array_fill(0, $this->config->width(), $this->config->space());
        $labelMaxLength = $dataset->labelMaxLength();
        foreach ($dataset as $offset => [$label, $item]) {
            $colorIndex = $colorCodeIndexes[$offset % $colorCodeCount];
            $labelPortion = str_pad($label, $labelMaxLength, ' ', $padding);
            $dataPortion = $this->drawDataPortion($item, $lineCharacters);

            yield ' '.$this->output->colorize($labelPortion.$gap.$dataPortion, $colorIndex);
        }
    }

    /**
     * Convert a Dataset item into a graph data portion.
     *
     * @param Period|Sequence $item
     * @param string[]        $lineCharacters
     */
    private function drawDataPortion($item, array $lineCharacters): string
    {
        if ($item instanceof Period) {
            return implode('', $this->drawPeriod($lineCharacters, $item));
        }

        static $drawSequence;
        $drawSequence = $drawSequence ?? Closure::fromCallable([$this, 'drawPeriod']);

        return implode('', $item->reduce($drawSequence, $lineCharacters));
    }

    /**
     * Converts a Period instance into an sequence of characters.
     *
     * The conversion depends on the Period interval and boundaries.
     *
     * @param string[] $lineCharacters
     *
     * @return string[]
     */
    private function drawPeriod(array $lineCharacters, Period $period): array
    {
        $startIndex = (int) floor(($period->getStartDate()->getTimestamp() - $this->start) * $this->unit);
        $endIndex = (int) ceil(($period->getEndDate()->getTimestamp() - $this->start) * $this->unit);
        $periodLength = $endIndex - $startIndex;

        array_splice($lineCharacters, $startIndex, $periodLength, array_fill(0, $periodLength, $this->config->body()));
        $lineCharacters[$startIndex] = $period->isStartIncluded() ? $this->config->startIncluded() : $this->config->startExcluded();
        $lineCharacters[$endIndex - 1] = $period->isEndIncluded() ? $this->config->endIncluded() : $this->config->endExcluded();

        return $lineCharacters;
    }
}
