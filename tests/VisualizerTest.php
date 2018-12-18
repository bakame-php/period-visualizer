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

namespace BakameTest\Period\Visualizer;

use Bakame\Period\Visualizer\Configuration;
use Bakame\Period\Visualizer\Visualizer;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Bakame\Period\Visualizer\Visualizer
 */
final class VisualizerTest extends TestCase
{
    /**
     * @var Visualizer
     */
    private $view;

    public function setUp(): void
    {
        $this->view = new Visualizer();
    }

    public function testConfiguration(): void
    {
        $config = $this->view->getConfiguration();
        $newConfig = Configuration::createFromRandom();
        $this->view->setConfiguration($newConfig);
        self::assertNotSame($config, $this->view->getConfiguration());
    }
}
