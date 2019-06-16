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

namespace BakameTest\Period\Visualizer\Label;

use Bakame\Period\Visualizer\Label\IntegerType;
use Bakame\Period\Visualizer\Label\RomanType;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Bakame\Period\Visualizer\Label\RomanType;
 */
final class RomanTypeTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(Sequence $sequence, int $label, int $lettercase, array $expected): void
    {
        $generator = new RomanType(new IntegerType($label), $lettercase);
        self::assertSame($expected, $generator->generateLabels($sequence));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'sequence' => new Sequence(),
                'label' => 1,
                'lettercase' => RomanType::UPPER,
                'expected' => [],
            ],
            'labels starts at 3' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => 3,
                'lettercase' => 42,
                'expected' => ['III'],
            ],
            'labels starts ends at 4' => [
                'sequence' => new Sequence(
                    new Period('2018-01-01', '2018-02-01'),
                    new Period('2018-02-01', '2018-03-01')
                ),
                'label' => 4,
                'lettercase' => RomanType::UPPER,
                'expected' => ['IV', 'V'],
            ],
            'labels starts at 0 (1)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => -1,
                'lettercase' => RomanType::LOWER,
                'expected' => ['i'],
            ],
            'labels starts at 0 (2)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => 0,
                'lettercase' => RomanType::LOWER,
                'expected' => ['i'],
            ],
        ];
    }

    public function testStartWith(): void
    {
        $generator = new RomanType(new IntegerType(42));
        self::assertSame(42, $generator->getStartingAt());
        $new = $generator->startWith(69);
        self::assertNotSame($new, $generator);
        self::assertSame(69, $new->getStartingAt());
        self::assertSame($generator, $generator->startWith(42));
        self::assertSame(1, (new IntegerType(-3))->getStartingAt());
        self::assertSame(1, $generator->startWith(-3)->getStartingAt());
    }

    public function testLetterCase(): void
    {
        $generator = new RomanType(new IntegerType(1));
        self::assertTrue($generator->isUpper());
        $new = $generator->withLetterCase(RomanType::LOWER);
        self::assertFalse($new->isUpper());
        $alt = $new->withLetterCase(RomanType::LOWER);
        self::assertSame($alt, $new);
    }
}
