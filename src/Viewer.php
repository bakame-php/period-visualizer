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

use Bakame\Period\Visualizer\Label\LabelGenerator;
use Bakame\Period\Visualizer\Label\LetterGenerator;
use League\Period\Period;
use League\Period\Sequence;
use function trim;

final class Viewer
{
    private const DEFAULT_RESULT_LABEL = 'RESULT';

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * Create a new output.
     *
     * @param ?LabelGenerator $label
     * @param ?ConsoleOutput  $output
     */
    public function __construct(?LabelGenerator $label = null, ?ConsoleOutput $output = null)
    {
        $this->setLabelGenerator($label ?? new LetterGenerator());
        $this->setOutput($output ?? new ConsoleOutput());
    }

    /**
     * Returns the output.
     */
    public function getOutput(): ConsoleOutput
    {
        return $this->output;
    }

    /**
     * Returns the Label Generator.
     */
    public function getLabelGenerator(): LabelGenerator
    {
        return $this->labelGenerator;
    }

    /**
     * Sets the output.
     */
    public function setOutput(ConsoleOutput $output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Sets the Label Generator.
     */
    public function setLabelGenerator(LabelGenerator $label): self
    {
        $this->labelGenerator = $label;

        return $this;
    }

    /**
     * Visualizes a sequence.
     */
    public function sequence(Sequence $sequence): string
    {
        $input = $this->addLabels($sequence);

        return $this->output->display($input);
    }

    /**
     * Attach the labels to the sequence.
     */
    private function addLabels(Sequence $sequence): array
    {
        $labels = $this->labelGenerator->generate($sequence);
        $results = [];
        foreach ($sequence as $offset => $period) {
            $results[] = [$labels[$offset], $period];
        }

        return $results;
    }

    /**
     * Visualizes a sequence intersections.
     */
    public function intersections(Sequence $sequence, string $resultLabel = 'INTERSECTIONS'): string
    {
        $input = $this->addLabels($sequence);
        $input[] = [$this->filterResultLabel($resultLabel), $sequence->intersections()];

        return $this->output->display($input);
    }

    /**
     * Format the result label.
     */
    private function filterResultLabel(string $label): string
    {
        $label = trim($label);
        if ('' === $label) {
            return $this->labelGenerator->format(self::DEFAULT_RESULT_LABEL);
        }

        return $this->labelGenerator->format($label);
    }

    /**
     * Visualizes a sequence gaps.
     */
    public function gaps(Sequence $sequence, string $resultLabel = 'GAPS'): string
    {
        $input = $this->addLabels($sequence);
        $input[] = [$this->filterResultLabel($resultLabel), $sequence->gaps()];

        return $this->output->display($input);
    }

    /**
     * Visualizes a sequence unions.
     */
    public function unions(Sequence $sequence, string $resultLabel = 'UNIONS'): string
    {
        $input = $this->addLabels($sequence);
        $input[] = [$this->filterResultLabel($resultLabel), $sequence->unions()];

        return $this->output->display($input);
    }

    /**
     * Visualizes a sequence diff.
     */
    public function diff(Period $interval1, Period $interval2, string $resultLabel = 'DIFF'): string
    {
        $res = $interval1->diff($interval2);
        $sequence = new Sequence($interval1, $interval2);
        $diff = new Sequence();
        foreach ($res as $part) {
            if (null !== $part) {
                $diff->push($part);
            }
        }

        $input = $this->addLabels($sequence);
        $input[] = [$this->filterResultLabel($resultLabel), $diff];

        return $this->output->display($input);
    }
}
