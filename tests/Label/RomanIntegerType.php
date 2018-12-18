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

namespace BakameTest\Period\Visualizer\Label;

use Bakame\Period\Visualizer\Label\IntegerType;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Bakame\Period\Visualizer\Label\RomanInteger;
 */
final class RomanIntegerType extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(Sequence $sequence, int $label, array $expected): void
    {
        $generator = new IntegerType($label);
        self::assertSame($expected, $generator->getLabels($sequence));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'sequence' => new Sequence(),
                'label' => 1,
                'expected' => [],
            ],
            'labels starts at 3' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => 3,
                'expected' => ['3'],
            ],
            'labels starts ends at 4' => [
                'sequence' => new Sequence(
                    new Period('2018-01-01', '2018-02-01'),
                    new Period('2018-02-01', '2018-03-01')
                ),
                'label' => 4,
                'expected' => ['4', '5'],
            ],
            'labels starts at 0 (1)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => -1,
                'expected' => ['1'],
            ],
            'labels starts at 0 (2)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => 0,
                'expected' => ['1'],
            ],
        ];
    }

    public function testStartWith(): void
    {
        $generator = new IntegerType(42);
        self::assertSame(42, $generator->getStartingAt());
        $new = $generator->startWith(69);
        self::assertNotSame($new, $generator);
        self::assertSame(69, $new->getStartingAt());
        self::assertSame($generator, $generator->startWith(42));
        self::assertSame(1, (new IntegerType(-3))->getStartingAt());
        self::assertSame(1, $generator->startWith(-3)->getStartingAt());
    }
}
