# Changelog

All Notable changes to `Period Visualizer` will be documented in this file

## 0.2.1 - 2018-12-20

- Bug Fix `ConsoleOutput::render` method.

## 0.2.0 - 2018-12-20

- The `OutputInterface::render` and the `OutputInterface::display` array signature has changed.

The array format is that of a tuple where:
	- the first value represents the period or the sequence label
	- the second value represents the period or the sequence

- The `Matrix` and the `Viewer` class are updated accordingly with refactoring to improve the calculation speed.
- The `ConsoleConfig` accepts a `default` color keywords to tell that the output should fellow the default colors from the console.
- The `default` color keyword replaces the `white` color keyword as the default keyword used if no color is specified.

## 0.1.0 - 2018-12-19

- Initial release
