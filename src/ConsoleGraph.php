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
        if (!$dataset->isEmpty()) {
            $this->output->writeln($this->drawLines($dataset));
        }
    }

    /**
     * Converts a Dataset entry into a line to be outputted by the OutputWriter implementation.
     *
     * @return iterable<string>
     */
    private function drawLines(Dataset $dataset): iterable
    {
        $nameLength = $dataset->labelMaxLength();
        /** @var Period $boundaries */
        $boundaries = $dataset->boundaries();
        $this->start = $boundaries->getStartDate()->getTimestamp();
        $this->unit = $this->config->width() / $boundaries->getTimestampInterval();
        $colorCodeIndexes = $this->config->colors();
        $colorCodeCount = count($colorCodeIndexes);
        $padding = $this->config->labelAlign();
        $gap = str_repeat(' ', $this->config->gapSize());
        $lineCharacters = array_fill(0, $this->config->width(), $this->config->space());
        $drawSequence = Closure::fromCallable([$this, 'drawPeriod']);
        foreach ($dataset as $offset => [$name, $item]) {
            $color = $colorCodeIndexes[$offset % $colorCodeCount];
            $line = str_pad($name, $nameLength, ' ', $padding).$gap;
            if ($item instanceof Period) {
                $line .= implode('', $this->drawPeriod($lineCharacters, $item));

                yield ' '.$this->output->colorize($line, $color);
            } elseif ($item instanceof Sequence) {
                $line .= implode('', $item->reduce($drawSequence, $lineCharacters));

                yield ' '.$this->output->colorize($line, $color);
            }
        }
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
