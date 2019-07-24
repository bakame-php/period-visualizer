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
use function is_scalar;
use function method_exists;
use function preg_match;
use function trim;

final class LatinLetter implements LabelGenerator
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
        $this->str = $this->filterLetter($str);
    }

    public function filterLetter(string $str): string
    {
        $str = trim($str);
        if ('' === $str) {
            return '0';
        }

        if (1 !== preg_match('/^[A-Za-z]+$/', $str)) {
            return 'A';
        }

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function format($str): string
    {
        if (!is_scalar($str) && !method_exists($str, '__toString') && null !== $str) {
            return '';
        }

        return (string) $str;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(int $nbLabels): array
    {
        if (0 >= $nbLabels) {
            $nbLabels = 0;
        }

        $letters = [];
        $count = 0;
        $letter = $this->str;
        while ($count < $nbLabels) {
            $letters[] = $letter++;
            ++$count;
        }

        return $letters;
    }

    /**
     * Returns the starting Letter.
     */
    public function startingAt(): string
    {
        return $this->str;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startsWith(string $str): self
    {
        $str = $this->filterLetter($str);
        if ($str === $this->str) {
            return $this;
        }

        $clone = clone $this;
        $clone->str = $str;

        return $clone;
    }
}
