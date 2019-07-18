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

use Bakame\Period\Visualizer\AffixLabel;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\LatinLetter;
use Bakame\Period\Visualizer\RomanNumber;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\AffixLabel;
 */
final class AffixLabelTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(
        Sequence $sequence,
        string $letter,
        string $prefix,
        string $suffix,
        array $expected
    ): void {
        $generator = new AffixLabel(new LatinLetter($letter), $prefix, $suffix);
        self::assertSame($expected, $generator->generate($sequence));

        $generator = (new AffixLabel(new LatinLetter($letter)))->withPrefix($prefix)->withSuffix($suffix);
        self::assertSame($expected, $generator->generate($sequence));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'sequence' => new Sequence(),
                'letter' => 'i',
                'prefix' => '',
                'suffix' => '',
                'expected' => [],
            ],
            'labels starts at i' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => 'i',
                'prefix' => '',
                'suffix' => '.',
                'expected' => ['i.'],
            ],
            'labels starts ends at ab' => [
                'sequence' => new Sequence(
                    new Period('2018-01-01', '2018-02-01'),
                    new Period('2018-02-01', '2018-03-01')
                ),
                'letter' => 'aa',
                'prefix' => '-',
                'suffix' => '',
                'expected' => ['-aa', '-ab'],
            ],
            'labels starts at 0 (1)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => '        ',
                'prefix' => '.',
                'suffix' => '.',
                'expected' => ['.0.'],
            ],
            'labels starts at 0 (2)' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => '',
                'prefix' => '.'.PHP_EOL,
                'suffix' => PHP_EOL.'.',
                'expected' => ['.0.'],
            ],
            'labels with an integer' => [
                'sequence' => new Sequence(new Period('2018-01-01', '2018-02-01')),
                'letter' => '1',
                'prefix' => '.'.PHP_EOL,
                'suffix' => PHP_EOL,
                'expected' => ['.A'],
            ],
        ];
    }

    public function testGetter(): void
    {
        $generator = new AffixLabel(new RomanNumber(new DecimalNumber(10)));
        self::assertSame('', $generator->suffix());
        self::assertSame('', $generator->prefix());
        $new = $generator->withPrefix('o')->withSuffix('');
        self::assertNotSame($new, $generator);
        self::assertSame('o', $new->prefix());
        self::assertSame('', $new->suffix());
    }

    public function testFormat(): void
    {
        $generator = new AffixLabel(new RomanNumber(new DecimalNumber(10)), ':', '.');
        self::assertSame(':.', $generator->format([]));
    }
}
