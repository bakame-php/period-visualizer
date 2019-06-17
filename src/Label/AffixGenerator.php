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

namespace Bakame\Period\Visualizer\Label;

use League\Period\Sequence;
use function array_map;
use function preg_replace;
use function trim;

final class AffixGenerator implements LabelGenerator
{
    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var string
     */
    private $suffix = '';

    /**
     * New instance.
     */
    public function __construct(LabelGenerator $labelGenerator, string $prefix = '', string $suffix = '')
    {
        $this->labelGenerator = $labelGenerator;
        $this->prefix = $this->filterString($prefix);
        $this->suffix = $this->filterString($suffix);
    }

    private function filterString(string $str): string
    {
        $str = (string) preg_replace("/[\r\n]/", '', $str);

        return trim($str);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Sequence $sequence): array
    {
        $mapper = function (string $value) {
            return $this->prefix.$value.$this->suffix;
        };

        return array_map($mapper, $this->labelGenerator->generate($sequence));
    }

    /**
     * Returns the suffix.
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * Returns the prefix.
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Return an instance with the suffix.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the suffix.
     */
    public function withSuffix(string $suffix): self
    {
        $suffix = $this->filterString($suffix);
        if ($suffix === $this->suffix) {
            return $this;
        }

        $clone = clone $this;
        $clone->suffix = $suffix;

        return $clone;
    }

    /**
     * Return an instance with the prefix.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the prefix.
     */
    public function withPrefix(string $prefix): self
    {
        $prefix = $this->filterString($prefix);
        if ($prefix === $this->prefix) {
            return $this;
        }

        $clone = clone $this;
        $clone->prefix = $prefix;

        return $clone;
    }
}
