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

use Bakame\Period\Visualizer\IntegerLabel;
use Bakame\Period\Visualizer\RomanLabel;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\RomanLabel;
 */
final class RomanGeneratorTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(Sequence $sequence, int $label, int $lettercase, array $expected): void
    {
        $generator = new RomanLabel(new IntegerLabel($label), $lettercase);
        self::assertSame($expected, $generator->generate($sequence));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'sequence' => new Sequence(),
                'label' => 1,
                'lettercase' => RomanLabel::UPPER,
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
                'lettercase' => RomanLabel::UPPER,
                'expected' => ['IV', 'V'],
            ],
            'labels starts at 0 (1)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => -1,
                'lettercase' => RomanLabel::LOWER,
                'expected' => ['i'],
            ],
            'labels starts at 0 (2)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'label' => 0,
                'lettercase' => RomanLabel::LOWER,
                'expected' => ['i'],
            ],
        ];
    }

    public function testStartWith(): void
    {
        $generator = new RomanLabel(new IntegerLabel(42));
        self::assertSame(42, $generator->startingAt());
        $new = $generator->startsWith(69);
        self::assertNotSame($new, $generator);
        self::assertSame(69, $new->startingAt());
        self::assertSame($generator, $generator->startsWith(42));
        self::assertSame(1, (new IntegerLabel(-3))->startingAt());
        self::assertSame(1, $generator->startsWith(-3)->startingAt());
    }

    public function testLetterCase(): void
    {
        $generator = new RomanLabel(new IntegerLabel(1));
        self::assertTrue($generator->isUpper());
        self::assertFalse($generator->isLower());
        $new = $generator->withLetterCase(RomanLabel::LOWER);
        self::assertFalse($new->isUpper());
        self::assertTrue($new->isLower());
        $alt = $new->withLetterCase(RomanLabel::LOWER);
        self::assertSame($alt, $new);
    }

    public function testFormat(): void
    {
        $generator = new RomanLabel(new IntegerLabel(10));
        self::assertSame('', $generator->format([]));
    }
}
