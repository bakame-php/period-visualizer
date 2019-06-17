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

use Bakame\Period\Visualizer\Label\IntegerGenerator;
use Bakame\Period\Visualizer\Label\RomanGenerator;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Bakame\Period\Visualizer\Label\RomanType;
 */
final class RomanGeneratorTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(Sequence $sequence, int $label, int $lettercase, array $expected): void
    {
        $generator = new RomanGenerator(new IntegerGenerator($label), $lettercase);
        self::assertSame($expected, $generator->generate($sequence));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'sequence' => new Sequence(),
                'label' => 1,
                'lettercase' => RomanGenerator::UPPER,
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
                'lettercase' => RomanGenerator::UPPER,
                'expected' => ['IV', 'V'],
            ],
            'labels starts at 0 (1)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => -1,
                'lettercase' => RomanGenerator::LOWER,
                'expected' => ['i'],
            ],
            'labels starts at 0 (2)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => 0,
                'lettercase' => RomanGenerator::LOWER,
                'expected' => ['i'],
            ],
        ];
    }

    public function testStartWith(): void
    {
        $generator = new RomanGenerator(new IntegerGenerator(42));
        self::assertSame(42, $generator->getStartingAt());
        $new = $generator->startWith(69);
        self::assertNotSame($new, $generator);
        self::assertSame(69, $new->getStartingAt());
        self::assertSame($generator, $generator->startWith(42));
        self::assertSame(1, (new IntegerGenerator(-3))->getStartingAt());
        self::assertSame(1, $generator->startWith(-3)->getStartingAt());
    }

    public function testLetterCase(): void
    {
        $generator = new RomanGenerator(new IntegerGenerator(1));
        self::assertTrue($generator->isUpper());
        $new = $generator->withLetterCase(RomanGenerator::LOWER);
        self::assertFalse($new->isUpper());
        $alt = $new->withLetterCase(RomanGenerator::LOWER);
        self::assertSame($alt, $new);
    }
}
