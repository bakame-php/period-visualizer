Period Visualizer
=======

[![Author][ico-author]][link-author]
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-packagist]][link-packagist]
[![Latest Stable Version][ico-release]][link-release]
[![Software License][ico-license]][link-license]

This package contains a visualizer for [League Period](https://period.thephpleague.com).

It is heavily inspired from the work of [@thecrypticace](https://github.com/thecrypticace) on the following PR [Visualization Helper](https://github.com/spatie/period/pull/10).

~~~php
<?php

use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\ConsoleOutput;
use League\Period\Period;
use League\Period\Sequence;

require 'vendor/autoload.php';

$view = new Viewer(new ConsoleOutput());
echo $view->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2017-01-01', '2019-01-01')
));
~~~

results:

~~~bash
 A         =    
 B    [========]
~~~

System Requirements
-------

You need:

- **PHP >= 7.1.3** but the latest stable version of PHP is recommended

Installation
--------

```bash
$ composer require bakame/period-visualizer
```

Usage
--------

## Basic Usage

### Viewing a Sequence collection.

~~~php
<?php

use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\ConsoleOutput;
use League\Period\Period;
use League\Period\Sequence;

$view = new Viewer(new ConsoleOutput());
echo $view->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 A    [========]
 B    [===]     
~~~

### Viewing a Sequence gaps.

~~~php
$view = new Viewer(new ConsoleOutput());
echo $view->gaps(new Sequence(
    new Period('2018-01-01', '2018-03-01'),
    new Period('2018-05-01', '2018-08-01')
));
~~~

results:

~~~bash
 A       [=]       
 B            [===]
 GAPS      [==]  
~~~

### Viewing a Sequence intersections.

~~~php
$view = new Viewer(new ConsoleOutput());
echo $view->intersections(new Sequence(
    new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00'),
    new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00')
));
~~~

results:

~~~bash
 A                [=====]   
 B                   [=====]
 INTERSECTIONS       [==] 
~~~

### Viewing a Period difference.

~~~php
$view = new Viewer(new ConsoleOutput());
echo $view->diff
    new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00'),
    new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00')
);
~~~

results:

~~~bash
 A       [=====]   
 B          [=====]
 DIFF    [==]  [==]
~~~

## Advance Usage

### Customize the line labels

The `Bakame\Period\Visualizer\Viewer` class can be further formatter by providing a object to improve line index generation.
By default the class is instantiated with the letter index strategy which starts incrementing the labes from  the 'A' index. You can choose between the following strategies to modify the default behaviour

#### Letter strategy

~~~php
<?php

use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\ConsoleOutput;
use Bakame\Period\Visualizer\Label\LetterType;
use League\Period\Period;
use League\Period\Sequence;

$view = new Viewer(new ConsoleOutput(), new LetterType('aa'));
echo $view->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 aa    [========]
 ab    [===]     
~~~

#### Integer strategy

~~~php
<?php

use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\ConsoleOutput;
use Bakame\Period\Visualizer\Label\IntegerType;
use League\Period\Period;
use League\Period\Sequence;

$view = new Viewer(new ConsoleOutput(), new IntegerType(42));
echo $view->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 42    [========]
 43    [===]     
~~~

#### Roman Number strategy

~~~php
<?php

use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\ConsoleOutput;
use Bakame\Period\Visualizer\Label\IntegerType;
use Bakame\Period\Visualizer\Label\RomanType;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new RomanType(new IntegerType(5), RomanType::LOWER);

$view = new Viewer(new ConsoleOutput(), $labelGenerator);
echo $view->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 v     [========]
 vi    [===]     
~~~

#### Affix strategy

~~~php
<?php

use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\ConsoleOutput;
use Bakame\Period\Visualizer\Label\IntegerType;
use Bakame\Period\Visualizer\Label\RomanType;
use Bakame\Period\Visualizer\Label\AffixType;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new AffixType(new RomanType(new IntegerType(5), RomanType::LOWER));
$labelGenerator = $labelGenerator->withSuffix('.');

$view = new Viewer(new ConsoleOutput(), $labelGenerator);
echo $view->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 v.     [========]
 vi.    [===]     
~~~

#### Custom strategy

You can create your own strategy by implementing the `Bakame\Period\Visualizer\Label\LabelGenerator` interface like shown below:

~~~php
use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\ConsoleOutput;
use Bakame\Period\Visualizer\Label\LabelGenerator;
use League\Period\Period;
use League\Period\Sequence;

$reverseLabel = new class implements LabelGenerator {
    public function getLabels(Sequence $sequence): array
    {
        return array_reverse(array_keys($sequence->toArray()));
    }
};

$view = new Viewer(new ConsoleOutput(), $reverseLabel);
echo $view->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 1     [========]
 0     [===]     
~~~

### Customize the output

If what you want to display can not be rendered using the `Viewer` class you can fallback to using the `ConsoleOutput` class directly.

~~~php
<?php

use Bakame\Period\Visualizer\ConsoleOutput;
use League\Period\Period;

$view = new ConsoleOutput();
echo $view->display([
    'first' => new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00'),
    'last' => new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00')
]);
~~~

results:

~~~bash
 first    [=====]   
 last        [=====]
~~~

The `ConsoleOutput` class can be further customize by providing a `ConsoleConfig` object with further configuration to apply to the output.

~~~php
<?php

use Bakame\Period\Visualizer\ConsoleOutput;
use League\Period\Period;

$config = (new ConsoleConfig())
    ->withHead(')')
    ->withTail('[')
    ->withBody('-')
    ->withSpace('+')
    ->withColors('yellow', 'red')
;

$view = new ConsoleOutput($config);
echo $view->display([
    'first' => new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00'),
    'last' => new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00')
]);
~~~

results:

~~~bash
 first    [-----)+++
 last     +++[-----)
~~~

*On a Posix compliant console the first line will be yellow and the second red*

![Result on a POSIX compliant console](posix-result.png)

**ALL CONFIGURATION OBJECTS ARE IMMUTABLE SO MODIFYING THEIR PROPERTIES RETURNS A NEW INSTANCE**

Changelog
-------

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

Testing
-------

The library has a :

- a [PHPUnit](https://phpunit.de) test suite
- a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/).
- a code analysis compliance test suite using [PHPStan](https://github.com/phpstan/phpstan).

To run the tests, run the following command from the project folder.

``` bash
$ composer test
```

Security
-------

If you discover any security related issues, please email nyamsprod@gmail.com instead of using the issue tracker.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [jordan pittman](https://github.com/thecrypticace)
- [All Contributors](https://github.com/bakame-php/laravel-domain-parser/contributors)

License
-------

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-author]: https://img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/bakame-php/period-visualizer/master.svg?style=flat-square
[ico-packagist]: https://img.shields.io/packagist/dt/bakame/period-visualizer.svg?style=flat-square
[ico-release]: https://img.shields.io/github/release/bakame-php/period-visualizer.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-author]: https://twitter.com/nyamsprod
[link-travis]: https://travis-ci.org/bakame-php/period-visualizer
[link-packagist]: https://packagist.org/packages/bakame/period-visualizer
[link-release]: https://github.com/bakame-php/period-visualizer/releases
[link-license]: https://github.com/bakame-php/period-visualizer/blob/master/LICENSE