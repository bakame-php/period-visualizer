Period Visualizer
=======

TODO.

~~~php
<?php

use Bakame\Period\Visualizer\SequenceViewer;
use League\Period\Period;
use League\Period\Sequence;

require 'vendor/autoload.php';

$view = new SequenceViewer();
echo $view->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2017-01-01', '2019-01-01')
));
~~~

returns

~~~bash
 A         =    
 B    [========]
~~~