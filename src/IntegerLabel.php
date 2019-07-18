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
use function count;
use function is_scalar;
use function method_exists;
use function range;

final class IntegerLabel implements LabelGenerator
{
    /**
     * @var int
     */
    private $int;

    /**
     * New instance.
     */
    public function __construct(int $int = 1)
    {
        if (0 >= $int) {
            $int = 1;
        }

        $this->int = $int;
    }

    /**
     * Returns the starting Letter.
     */
    public function startingAt(): int
    {
        return $this->int;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startsWith(int $int): self
    {
        if (0 >= $int) {
            $int = 1;
        }

        if ($int === $this->int) {
            return $this;
        }

        $clone = clone $this;
        $clone->int = $int;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Sequence $sequence): array
    {
        $letters = [];
        if ($sequence->isEmpty()) {
            return $letters;
        }

        return array_map([$this, 'format'], range($this->int, $this->int + count($sequence) - 1));
    }

    /**
     * {@inheritdoc}
     */
    public function format($str): string
    {
        if (is_scalar($str) || method_exists($str, '__toString') || null === $str) {
            return (string) $str;
        }

        return '';
    }
}
