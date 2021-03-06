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
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\LatinLetter;
 */
final class LatinLetterTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(int $nbLabels, string $letter, array $expected): void
    {
        $generator = new LatinLetter($letter);
        self::assertSame($expected, $generator->generate($nbLabels));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'nbLabels' => 0,
                'letter' => 'i',
                'expected' => [],
            ],
            'labels starts at i' => [
                'nbLabels' => 1,
                'letter' => 'i',
                'expected' => ['i'],
            ],
            'labels starts ends at ab' => [
                'nbLabels' => 2,
                'letter' => 'aa',
                'expected' => ['aa', 'ab'],
            ],
            'labels starts at 0 (1)' => [
                'nbLabels' => 1,
                'letter' => '        ',
                'expected' => ['0'],
            ],
            'labels starts at 0 (2)' => [
                'nbLabels' => 1,
                'letter' => '',
                'expected' => ['0'],
            ],
            'labels with an integer' => [
                'nbLabels' => 1,
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

    public function testFormat(): void
    {
        $generator = new LatinLetter('i');
        self::assertSame('foobar', $generator->format('foobar'));
    }
}
