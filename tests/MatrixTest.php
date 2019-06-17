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

use Bakame\Period\Visualizer\Matrix;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;
use function count;

/**
 * @coversDefaultClass \Bakame\Period\Visualizer\Matrix
 */
final class MatrixTest extends TestCase
{
    /**
     * @covers ::build
     */
    public function testBuildEmptyBlocks(): void
    {
        self::assertEmpty(Matrix::build([], 30));
    }

    /**
     * @covers ::build
     * @covers ::getBoundaries
     * @covers ::populateRow
     */
    public function testBuildPeriod(): void
    {
        $input = [
            ['foo', Period::around('NOW', '1 DAY')],
        ];
        $matrix = Matrix::build($input, 30);
        self::assertCount(1, $matrix);
        self::assertSame($input[0][0], $matrix[0][0]);

        foreach ($matrix as [$name, $data]) {
            self::assertContains(Matrix::TOKEN_START_INCLUDED, $data);
            self::assertContains(Matrix::TOKEN_BODY, $data);
            self::assertContains(Matrix::TOKEN_END_EXCLUDED, $data);
        }
    }

    /**
     * @covers ::build
     * @covers ::getBoundaries
     * @covers ::populateRow
     */
    public function testBuildSequence(): void
    {
        $sequence = new Sequence(
            Period::around('NOW - 1 WEEK', '1 DAY', Period::INCLUDE_ALL),
            Period::around('NOW + 1 DAY', '1 DAY', Period::EXCLUDE_ALL)
        );

        $matrix = Matrix::build([['foo', $sequence]], 30);
        self::assertCount(1, $matrix);

        [$name, $data] = $matrix[0];

        self::assertSame('foo', $name);
        self::assertSame(Matrix::TOKEN_START_INCLUDED, $data[0]);
        self::assertContains(Matrix::TOKEN_BODY, $data);
        self::assertContains(Matrix::TOKEN_SPACE, $data);
        self::assertSame(Matrix::TOKEN_END_EXCLUDED, $data[count($data) - 1]);
    }
}
