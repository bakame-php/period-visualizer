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
     * @param ?ConsoleOutput  $output
     * @param ?LabelGenerator $label
     */
    public function __construct(?ConsoleOutput $output = null, ?LabelGenerator $label = null)
    {
        $this->setOutput($output ?? new ConsoleOutput());
        $this->setLabelGenerator($label ?? new LetterGenerator());
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
     * Returns the sequence view representation.
     */
    public function sequence(Sequence $sequence): string
    {
        $input = $this->addLabels($sequence);

        return $this->output->display($input);
    }

    /**
     * Returns the sequence intersections view representation.
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
            return self::DEFAULT_RESULT_LABEL;
        }

        return $label;
    }

    /**
     * Returns the sequence gaps view representation.
     */
    public function gaps(Sequence $sequence, string $resultLabel = 'GAPS'): string
    {
        $input = $this->addLabels($sequence);
        $input[] = [$this->filterResultLabel($resultLabel), $sequence->gaps()];

        return $this->output->display($input);
    }

    /**
     * Returns the sequence gaps view representation.
     */
    public function unions(Sequence $sequence, string $resultLabel = 'UNIONS'): string
    {
        $input = $this->addLabels($sequence);
        $input[] = [$this->filterResultLabel($resultLabel), $sequence->unions()];

        return $this->output->display($input);
    }

    /**
     * Returns the sequences diff view representation.
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

    /**
     * Format the sequence data to be shown.
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
}
