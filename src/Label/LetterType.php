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
use function count;
use function trim;

final class LetterType implements LabelGenerator
{
    /**
     * @var string
     */
    private $str;

    /**
     * New instance.
     */
    public function __construct(string $str = 'A')
    {
        $this->str = $this->filter($str);
    }

    /**
     * Returns the starting Letter.
     */
    public function getStartingString(): string
    {
        return $this->str;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startWith(string $str): self
    {
        $str = $this->filter($str);
        if ($str === $this->str) {
            return $this;
        }

        $clone = clone $this;
        $clone->str = $str;

        return $clone;
    }

    /**
     * Format the starting string.
     */
    private function filter(string $letter): string
    {
        $letter = trim($letter);
        if ('' === $letter) {
            return '0';
        }

        if (1 !== preg_match('/^[A-Za-z]+$/', $letter)) {
            return 'A';
        }

        return $letter;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels(Sequence $sequence): array
    {
        $letters = [];
        if ($sequence->isEmpty()) {
            return $letters;
        }
        $nbItems = count($sequence);
        $count = 0;
        $letter = $this->str;
        while ($count < $nbItems) {
            $letters[] = $letter++;
            ++$count;
        }

        return $letters;
    }
}
