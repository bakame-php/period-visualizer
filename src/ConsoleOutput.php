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

use function array_column;
use function array_map;
use function count;
use function implode;
use function max;
use function str_pad;

/**
 * A class to output to the console the matrix.
 */
final class ConsoleOutput implements Output
{
    private const TOKEN_TO_METHOD = [
        Matrix::TOKEN_SPACE => 'space',
        Matrix::TOKEN_BODY => 'body',
        Matrix::TOKEN_START_EXCLUDED => 'startExcluded',
        Matrix::TOKEN_START_INCLUDED => 'startIncluded',
        Matrix::TOKEN_END_INCLUDED => 'endIncluded',
        Matrix::TOKEN_END_EXCLUDED => 'endExcluded',
    ];

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var ConsoleConfig
     */
    private $config;

    /**
     * New instance.
     */
    public function __construct(ConsoleConfig $config = null, Writer $writer = null)
    {
        $this->config = $config ?? new ConsoleConfig();
        $this->writer = $writer ?? new Stdout(fopen('php://stdout', 'w+'));
    }

    /**
     * {@inheritDoc}
     */
    public function display(iterable $blocks): int
    {
        $matrix = Matrix::build($blocks, $this->config->width());
        if ([] === $matrix) {
            return 0;
        }

        $bytes = 0;
        foreach ($this->format($matrix) as [$line, $color]) {
            $bytes += $this->writer->writeln($this->writer->colorize($line, $color));
        }

        return $bytes;
    }

    /**
     * Builds an Iterator to visualize one or more
     * periods and/or collections in a more
     * human readable / parsable manner.
     *
     * The submitted array values represent a tuple where
     * the first value is the identifier and the second value
     * the intervals represented as Period or Sequence instances.
     *
     * This method returns one output string line at a time.
     */
    private function format(array $matrix): iterable
    {
        $nameLength = max(...array_map('strlen', array_column($matrix, 0)));
        $colorOffsets = $this->config->colors();
        $key = -1;
        foreach ($matrix as [$name, $row]) {
            $prefix = str_pad($name, $nameLength, ' ');
            $data = implode('', array_map([$this, 'convertMatrixValue'], $row));
            $line = $prefix.'    '.$data;
            $color = $colorOffsets[++$key % count($colorOffsets)];

            yield [$line, $color];
        }
    }

    /**
     * Turns the matrix values into characters representing the interval.
     */
    private function convertMatrixValue(int $token): string
    {
        return $this->config->{self::TOKEN_TO_METHOD[$token]}();
    }
}
