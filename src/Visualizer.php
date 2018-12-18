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

final class Visualizer implements VisualizerInterface
{
    /**
     * @var Configuration
     */
    private $config;

    /**
     * Create a new visualizer.
     *
     * @param ?Configuration $config
     */
    public function __construct(?Configuration $config = null)
    {
        $this->setConfiguration($config ?? new Configuration());
    }

    /**
     * Returns the Configuration object.
     *
     * This method is not part of the VisualizerInterface interface
     *
     */
    public function getConfiguration(): Configuration
    {
        return $this->config;
    }

    /**
     * Sets the Configuration object.
     *
     * This method is not part of the VisualizerInterface interface
     */
    public function setConfiguration(Configuration $config): void
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function display(array $blocks): string
    {
        ob_start();
        foreach ($this->render($blocks) as $row) {
            echo $row;
        }

        return (string) ob_get_clean();
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $blocks): iterable
    {
        return $this->config->applyColors($this->convert($blocks));
    }

    /**
     * {@inheritdoc}
     */
    private function convert(array $blocks): iterable
    {
        $matrix = $this->matrix($blocks);
        if ([] === $matrix) {
            return;
        }

        $nameLength = max(...array_map('strlen', array_keys($matrix)));
        foreach ($matrix as $name => $row) {
            yield sprintf('%s    %s', str_pad($name, $nameLength, ' '), $this->toBars($row));
        }
    }

    /**
     * Build a 2D table such that:
     * - There's one row for every block.
     * - There's one column for every unit of width.
     * - Each cell is true when a period is active for that unit.
     * - Each cell is false when a period is not active for that unit.
     *
     */
    private function matrix(array $blocks): array
    {
        $bounds = $this->getBoundaries($blocks);
        if (null === $bounds) {
            return [];
        }

        $width = $this->config->getWidth();
        $matrix = array_fill(0, count($blocks), array_fill(0, $width, false));
        $matrix = array_combine(array_keys($blocks), array_values($matrix));

        foreach ($blocks as $name => $block) {
            if ($block instanceof Period) {
                $block = [$block];
            }

            foreach ($block as $period) {
                $matrix[$name] = $this->populateRow($matrix[$name], $period, $bounds);
            }
        }

        return $matrix;
    }

    /**
     * Get the start / end coordinates for a given period.
     */
    private function coords(Period $period, Period $bounds, int $width): array
    {
        $boundsStart = $bounds->getStartDate()->getTimestamp();

        // Get the bounds
        $start = $period->getStartDate()->getTimestamp() - $boundsStart;
        $end = $period->getEndDate()->getTimestamp() - $boundsStart;

        // Rescale from timestamps to width units
        $boundsLength = $bounds->getTimestampInterval();
        $start *= $width / $boundsLength;
        $end *= $width / $boundsLength;

        // Cap at integer intervals
        $start = floor($start);
        $end = ceil($end);

        return [$start, $end];
    }

    /**
     * Populate a row with true values where periods are active.
     */
    private function populateRow(array $row, Period $period, Period $bounds): array
    {
        $width = $this->config->getWidth();

        [$startIndex, $endIndex] = $this->coords($period, $bounds, $width);

        for ($i = 0; $i < $width; $i++) {
            if ($startIndex <= $i && $i < $endIndex) {
                $row[$i] = true;
            }
        }

        return $row;
    }

    /**
     * Get the bounds encompassing all visualized periods.
     */
    private function getBoundaries(array $blocks): ?Period
    {
        $periods = new Sequence();
        foreach ($blocks as $block) {
            if ($block instanceof Period) {
                $periods->push($block);
            } elseif ($block instanceof Sequence && !$block->isEmpty()) {
                $periods->push(...$block);
            }
        }

        return $periods->getBoundaries();
    }

    /**
     * Turn a series of true/false values into bars representing the start/end of periods.
     */
    private function toBars(array $row): string
    {
        $tmp = '';

        for ($i = 0, $l = count($row); $i < $l; $i++) {
            $prev = $row[$i - 1] ?? null;
            $curr = $row[$i];
            $next = $row[$i + 1] ?? null;

            // Small state machine to build the string
            switch (true) {
                // The current period is only one unit long so display a "="
                case $curr && $curr !== $prev && $curr !== $next:
                    $tmp .= $this->config->getBody();
                    break;

                // We've hit the start of a period
                case $curr && $curr !== $prev && $curr === $next:
                    $tmp .= $this->config->getTail();
                    break;

                // We've hit the end of the period
                case $curr && $curr !== $next:
                    $tmp .= $this->config->getHead();
                    break;

                // We're adding segments to the current period
                case $curr && $curr === $prev:
                    $tmp .= $this->config->getBody();
                    break;

                // Otherwise it's just empty space
                default:
                    $tmp .= $this->config->getSpace();
                    break;
            }
        }

        return $tmp;
    }
}
