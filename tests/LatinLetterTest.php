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

use Bakame\Period\Visualizer\LatinLetter;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\LatinLetter;
 */
final class LatinLetterTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(Sequence $sequence, string $letter, array $expected): void
    {
        $generator = new LatinLetter($letter);
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
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => 'i',
                'expected' => ['i'],
            ],
            'labels starts ends at ab' => [
                'sequence' => new Sequence(
                    new Period('2018-01-01', '2018-02-01'),
                    new Period('2018-02-01', '2018-03-01')
                ),
                'letter' => 'aa',
                'expected' => ['aa', 'ab'],
            ],
            'labels starts at 0 (1)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => '        ',
                'expected' => ['0'],
            ],
            'labels starts at 0 (2)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => '',
                'expected' => ['0'],
            ],
            'labels with an integer' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => '1',
                'expected' => ['A'],
            ],
        ];
    }

    public function testStartWith(): void
    {
        $generator = new LatinLetter('i');
        self::assertSame('i', $generator->startingAt());
        $new = $generator->startsWith('o');
        self::assertNotSame($new, $generator);
        self::assertSame('o', $new->startingAt());
        self::assertSame($generator, $generator->startsWith('i'));
    }
}
