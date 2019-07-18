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

namespace BakameTest\Period\Visualizer;

use Bakame\Period\Visualizer\LetterLabel;
use Bakame\Period\Visualizer\ReverseLabel;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\ReverseLabel;
 */
final class ReverseGeneratorTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(
        Sequence $sequence,
        string $letter,
        array $expected
    ): void {
        $generator = new ReverseLabel(new LetterLabel($letter));
        self::assertSame($expected, $generator->generate($sequence));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'sequence' => new Sequence(),
                'letter' => 'i',
                'expected' => [],
            ],
            'labels starts at i' => [
                'sequence' => new Sequence(
                    new Period('2018-01-01', '2018-02-01'),
                    new Period('2018-01-01', '2018-02-01')
                ),
                'letter' => 'i',
                'expected' => ['j', 'i'],
            ],
            'labels starts ends at ab' => [
                'sequence' => new Sequence(
                    new Period('2018-01-01', '2018-02-01'),
                    new Period('2018-02-01', '2018-03-01')
                ),
                'letter' => 'aa',
                'expected' => ['ab', 'aa'],
            ],
        ];
    }

    public function testFormat(): void
    {
        $generator = new ReverseLabel(new LetterLabel('AA'));
        self::assertSame('', $generator->format([]));
    }
}