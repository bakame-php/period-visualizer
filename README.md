Period Visualizer
------

[![Author][ico-author]][link-author]
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-packagist]][link-packagist]
[![Latest Stable Version][ico-release]][link-release]
[![Software License][ico-license]][link-license]

This package contains a visualizer for [League Period](https://period.thephpleague.com).

It is inspired from the work of [@thecrypticace](https://github.com/thecrypticace) on the following PR [Visualization Helper](https://github.com/spatie/period/pull/10).

~~~php
<?php

use Bakame\Period\Visualizer\Viewer;
use League\Period\Period;
use League\Period\Sequence;

$viewer = new Viewer();
echo $viewer->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-15', '2018-02-01')
));
~~~

results:

~~~bash
 A    [------------------------------------------------------------------------------)
 B                                        [------------------------------------------)
~~~

System Requirements
-------

You need:

- **PHP >= 7.2** but the latest stable version of PHP is recommended
- **League/Period 4.4+** but the latest stable version is recommended

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
use League\Period\Period;
use League\Period\Sequence;

$viewer = new Viewer();
$viewer->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 A [------------------------------------------------------------------------------)
 B                                     [------------------------------------------)
~~~

### Viewing a Sequence gaps.

~~~php
$viewer = new Viewer();
$viewer->gaps(new Sequence(
    new Period('2018-01-01', '2018-03-01'),
    new Period('2018-05-01', '2018-08-01')
));
~~~

results:

~~~bash
 A    [---------------------)                                                         
 B                                                 [--------------------------------=)
 GAPS                       [----------------------)    
~~~

### Viewing a Sequence intersections.

~~~php
$viewer = new Viewer();
$viewer->intersections(new Sequence(
    new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00'),
    new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00')
));
~~~

results:

~~~bash
 A             [----------------------------------------------------)                          
 B                                       [----------------------------------------------------)
 INTERSECTIONS                           [--------------------------) 
~~~

### Viewing a Period difference.

~~~php
$viewer = new Viewer();
$viewer->diff(
    new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00'),
    new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00')
);
~~~

results:

~~~bash
 A    [----------------------------------------------------)                          
 B                              [----------------------------------------------------)
 DIFF [-------------------------)                          [-------------------------)
~~~

## Advance Usage

### Customize the line labels

The `Bakame\Period\Visualizer\Viewer` class can be further formatter by providing a object to improve line index generation.
By default the class is instantiated with the letter index strategy which starts incrementing the labels from  the 'A' index. You can choose between the following strategies to modify the default behaviour

#### Letter strategy

~~~php
use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\LatinLetter;
use League\Period\Period;
use League\Period\Sequence;

$viewer = new Viewer(new LatinLetter('aa'));
$viewer->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 aa [------------------------------------------------------------------------------)
 ab [-----------------------------------)     
~~~

#### Decimal Number Strategy

~~~php
use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\DecimalNumber;
use League\Period\Period;
use League\Period\Sequence;

$viewer = new Viewer(new DecimalNumber(42));
$viewer->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 42 [------------------------------------------------------------------------------)
 43 [-----------------------------------)
~~~

#### Roman Numeral Strategy

~~~php
use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\RomanNumber;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new RomanNumber(new DecimalNumber(5), RomanNumber::LOWER);

$viewer = new Viewer($labelGenerator);
$viewer->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 v  [------------------------------------------------------------------------------)
 vi [-----------------------------------)
~~~

#### Affix Label Strategy

~~~php
use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\RomanNumber;
use Bakame\Period\Visualizer\AffixLabel;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new AffixLabel(
    new RomanNumber(new DecimalNumber(5), RomanNumber::LOWER),
    '*', //prefix
    '.)'    //suffix
);
$viewer = new Viewer($labelGenerator);
$viewer->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 * v .)  [----------------------------------------------------------)
 * vi .) [--------------------------) 
~~~

#### Reverse Label Strategy

~~~php
use Bakame\Period\Visualizer\Viewer;
use Bakame\Period\Visualizer\DecimalNumber;
use Bakame\Period\Visualizer\RomanNumber;
use Bakame\Period\Visualizer\AffixLabel;
use Bakame\Period\Visualizer\ReverseLabel;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new DecimalNumber(5);
$labelGenerator = new RomanNumber($labelGenerator, RomanNumber::LOWER);
$labelGenerator = new AffixLabel($labelGenerator, '', '.');
$labelGenerator = new ReverseLabel($labelGenerator);

$viewer = new Viewer($labelGenerator);
$viewer->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 vi. [------------------------------------------------------------------------------)
 v.  [-----------------------------------)
~~~

#### Custom Strategy

You can create your own strategy by implementing the `Bakame\Period\Visualizer\Contract\LabelGenerator` interface like shown below:

~~~php
use Bakame\Period\Visualizer\AffixLabel;
use Bakame\Period\Visualizer\Contract\LabelGenerator;
use Bakame\Period\Visualizer\Viewer;
use League\Period\Period;
use League\Period\Sequence;

$samelabel = new class implements LabelGenerator {
    public function generate(Sequence $sequence): array
    {
        return array_fill(0, count($sequence), $this->format('foobar'));
    }
        
    public function format($str): string
    {
        return (string) $str;
    }
};

$labelGenerator = new AffixLabel($samelabel, '', '.');
$viewer = new Viewer($labelGenerator);

$viewer->sequence(new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-01-01', '2018-01-15')
));
~~~

results:

~~~bash
 foobar. [------------------------------------------------------------------------------)
 foobar. [-----------------------------------)
~~~

### Customize the output

Under the hood, the `Viewer` class uses the `ConsoleOutput` class to generate your graph.

~~~php
use Bakame\Period\Visualizer\ConsoleOutput;
use Bakame\Period\Visualizer\Tuple;
use League\Period\Period;

$output = new ConsoleOutput();
echo $output->display(new Tuple([
    ['first', new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00')],
    ['last', new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00')],
]));
~~~

results:

~~~bash
 first [----------------------------------------------------)                          
 last                            [----------------------------------------------------)
~~~

The `ConsoleOutput::display` methods expects a tuple as its unique argument where:

- the first value of the tuple represents the label name which must be a `string`.
- the second and last value represents a `Period` or `Sequence` object.

The `ConsoleOutput` class can be customized by providing a `ConsoleConfig` which defines the output settings.

~~~php
use Bakame\Period\Visualizer\ConsoleConfig;
use Bakame\Period\Visualizer\ConsoleOutput;
use Bakame\Period\Visualizer\Contract\LabelGenerator;
use Bakame\Period\Visualizer\Viewer;
use League\Period\Period;

$config = (new ConsoleConfig())
    ->withStartExcluded('🍕')
    ->withStartIncluded('🍅')
    ->withEndExcluded('🎾')
    ->withEndIncluded('🍔')
    ->withWidth(30)
    ->withSpace('💩')
    ->withBody('😊')
    ->withColors('yellow', 'red')
;

$fixedLabels = new class implements LabelGenerator {
    public function generate(Sequence $sequence): array
    {
        return array_map([$this, 'format'], ['first', 'last']);
    }
    
    public function format($str): string
    {
        return (string) $str;
    }
};

$output = new ConsoleOutput($config);
$viewer = new Viewer($fixedLabels, $output);

$viewer->sequence(new Sequence(
    new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00', Period::EXCLUDE_ALL),
    new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00', Period::INCLUDE_ALL)
));
~~~

results:

~~~bash
 first 🍕😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊🎾💩💩💩💩💩💩💩💩💩💩
 last  💩💩💩💩💩💩💩💩💩💩🍅😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊😊🍔
~~~

*On a POSIX compliant console the first line will be yellow and the second red*

**`ConsoleConfig` is immutable, modifying its properties returns a new instance with the updated values.**

Changelog
-------

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

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
