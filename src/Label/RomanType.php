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

final class RomanType implements LabelGenerator
{
    public const UPPER = 1;
    public const LOWER = 2;

    /**
     * @var IntegerType
     */
    private $labelGenerator;

    /**
     * @var int
     */
    private $lettercase;

    /**
     * New instance.
     */
    public function __construct(IntegerType $labelGenerator, int $lettercase = self::UPPER)
    {
        $this->labelGenerator = $labelGenerator;
        $this->lettercase = $this->filter($lettercase);
    }

    /**
     * Formatter lettercase value.
     */
    private function filter(int $lettercase): int
    {
        if (!in_array($lettercase, [self::UPPER, self::LOWER], true)) {
            return self::UPPER;
        }

        return $lettercase;
    }

    /**
     * Returns the starting Letter.
     */
    public function getStartingAt(): int
    {
        return $this->labelGenerator->getStartingAt();
    }

    /**
     * Tells whether the roman integer will be uppercased or not.
     */
    public function isUpper(): bool
    {
        return self::UPPER === $this->lettercase;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startWith(int $int): self
    {
        $labelGenerator = $this->labelGenerator->startWith($int);
        if ($labelGenerator === $this->labelGenerator) {
            return $this;
        }

        $clone = clone $this;
        $clone->labelGenerator = $labelGenerator;

        return $clone;
    }

    /**
     * Return an instance with the new lettercase setting.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the lettercase setting.
     */
    public function withLetterCase(int $lettercase): self
    {
        $lettercase = $this->filter($lettercase);
        if ($lettercase === $this->lettercase) {
            return $this;
        }

        $clone = clone $this;
        $clone->lettercase = $lettercase;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels(Sequence $sequence): array
    {
        $retval = array_map([$this, 'convert'], $this->labelGenerator->getLabels($sequence));
        if (self::LOWER === $this->lettercase) {
            return array_map('strtolower', $retval);
        }

        return $retval;
    }

    /**
     * Convert a integer number into its roman representation.
     */
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
