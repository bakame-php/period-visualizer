<?php

/**
 * League.Period Visualizer (https://github.com/bakame-php/period-visualizer).
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

final class AffixType implements LabelGenerator
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
    public function __construct(LabelGenerator $labelGenerator)
    {
        $this->labelGenerator = $labelGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels(Sequence $sequence): array
    {
        $mapper = function (string $value) {
            return $this->prefix.$value.$this->suffix;
        };

        return array_map($mapper, $this->labelGenerator->getLabels($sequence));
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
        $suffix = (string) preg_replace("/[\r\n]/", '', $suffix);
        $suffix = trim($suffix);
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
        $prefix = (string) preg_replace("/[\r\n]/", '', $prefix);
        $prefix = trim($prefix);
        if ($prefix === $this->prefix) {
            return $this;
        }

        $clone = clone $this;
        $clone->prefix = $prefix;

        return $clone;
    }
}
