<?php

/**
 * League.Period Visualizer (https://github.com/bakame-php/period-visualizer).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bakame\Period\Visualizer\Label;

use League\Period\Sequence;

final class RomanLabel implements GeneratorInterface
{
    /**
     * @var IntegerLabel
     */
    private $integerLabel;

    /**
     * New instance.
     */
    public function __construct(IntegerLabel $integerLabel)
    {
        $this->integerLabel = $integerLabel;
    }

    /**
     * Returns the starting Letter.
     */
    public function getStartingAt(): int
    {
        return $this->integerLabel->getStartingAt();
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startWith(int $int): self
    {
        $integerLabel = $this->integerLabel->startWith($int);
        if ($integerLabel === $this->integerLabel) {
            return $this;
        }

        $clone = clone $this;
        $clone->integerLabel = $integerLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels(Sequence $sequence): array
    {
        return array_map([$this, 'convert'], $this->integerLabel->getLabels($sequence));
    }

    private function convert(int $number): string
    {
        $map = [
            'M' => 1000, 'CM' => 900, 'D' => 500,
            'CD' => 400, 'C' => 100, 'XC' => 90,
            'L' => 50, 'XL' => 40, 'X' => 10,
            'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1,
        ];

        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }

        return $returnValue;
    }
}
