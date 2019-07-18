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

use Bakame\Period\Visualizer\Contract\LabelGenerator;
use League\Period\Sequence;
use function array_map;
use function filter_var;
use function in_array;
use function strtolower;
use const FILTER_VALIDATE_INT;

final class RomanNumber implements LabelGenerator
{
    public const UPPER = 1;
    public const LOWER = 2;

    private const CHARACTER_MAP = [
        'M'  => 1000, 'CM' => 900,  'D' => 500,
        'CD' => 400,   'C' => 100, 'XC' => 90,
        'L'  => 50,   'XL' => 40,   'X' => 10,
        'IX' => 9,     'V' => 5,   'IV' => 4,
        'I'  => 1,
    ];

    /**
     * @var DecimalNumber
     */
    private $labelGenerator;

    /**
     * @var int
     */
    private $lettercase;

    /**
     * New instance.
     */
    public function __construct(DecimalNumber $labelGenerator, int $lettercase = self::UPPER)
    {
        $this->labelGenerator = $labelGenerator;
        $this->lettercase = $this->filterLetterCase($lettercase);
    }

    /**
     * filter letter case state.
     */
    private function filterLetterCase(int $lettercase): int
    {
        if (!in_array($lettercase, [self::UPPER, self::LOWER], true)) {
            return self::UPPER;
        }

        return $lettercase;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Sequence $sequence): array
    {
        return array_map([$this, 'format'], $this->labelGenerator->generate($sequence));
    }

    /**
     * {@inheritdoc}
     */
    public function format($str): string
    {
        $res = filter_var($str, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $res) {
            return '';
        }

        return $this->convert($res);
    }

    /**
     * Convert a integer number into its roman representation.
     *
     * @see https://stackoverflow.com/a/15023547
     */
    private function convert(int $number): string
    {
        $retVal = '';
        while ($number > 0) {
            foreach (self::CHARACTER_MAP as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $retVal .= $roman;
                    break;
                }
            }
        }

        if (self::LOWER === $this->lettercase) {
            return strtolower($retVal);
        }

        return $retVal;
    }

    /**
     * Returns the starting Letter.
     */
    public function startingAt(): int
    {
        return $this->labelGenerator->startingAt();
    }

    /**
     * Tells whether the roman letter is upper cased.
     */
    public function isUpper(): bool
    {
        return self::UPPER === $this->lettercase;
    }

    /**
     * Tells whether the roman letter is lower cased.
     */
    public function isLower(): bool
    {
        return self::LOWER === $this->lettercase;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startsWith(int $int): self
    {
        $labelGenerator = $this->labelGenerator->startsWith($int);
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
        $lettercase = $this->filterLetterCase($lettercase);
        if ($lettercase === $this->lettercase) {
            return $this;
        }

        $clone = clone $this;
        $clone->lettercase = $lettercase;

        return $clone;
    }
}
