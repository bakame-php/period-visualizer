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

use Bakame\Period\Visualizer\Label\LabelGenerator;
use Bakame\Period\Visualizer\Label\LetterType;
use League\Period\Period;
use League\Period\Sequence;
use function array_values;

final class Viewer
{
    /**
     * @var OutputInterface
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
     */
    public function __construct(OutputInterface $output, ?LabelGenerator $label = null)
    {
        $this->output = $output;
        $this->setLabelGenerator($label ?? new LetterType());
    }

    /**
     * Returns the output.
     */
    public function getOutput(): OutputInterface
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
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Sets the Label Generator.
     */
    public function setLabelGenerator(LabelGenerator $label): void
    {
        $this->labelGenerator = $label;
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
    public function intersections(Sequence $sequence): string
    {
        $input = $this->addLabels($sequence);
        $input[] = ['INTERSECTIONS', $sequence->getIntersections()];

        return $this->output->display($input);
    }

    /**
     * Returns the sequence gaps view representation.
     */
    public function gaps(Sequence $sequence): string
    {
        $input = $this->addLabels($sequence);
        $input[] = ['GAPS', $sequence->getGaps()];

        return $this->output->display($input);
    }

    /**
     * Returns the sequences diff view representation.
     */
    public function diff(Period $interval1, Period $interval2): string
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
        $input[] = ['DIFF', $diff];

        return $this->output->display($input);
    }

    /**
     * Format the sequence data to be shown.
     */
    private function addLabels(Sequence $sequence): array
    {
        $labels = array_values($this->labelGenerator->getLabels($sequence));
        $data = array_values($sequence->toArray());
        $results = [];
        foreach ($data as $offset => $period) {
            $results[] = [$labels[$offset], $period];
        }

        return $results;
    }
}
