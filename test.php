<?php

use League\Period\Period;
use League\Period\Sequence;
use Bakame\Period\Visualizer\SequenceViewer;
use Bakame\Period\Visualizer\Label\IntegerLabel;
use Bakame\Period\Visualizer\Label\RomanLabel;

require 'vendor/autoload.php';

$view = new SequenceViewer();
$view->setLabelGenerator(new RomanLabel(new IntegerLabel(12)));
echo $view->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2017-01-01', '2019-01-01')
));