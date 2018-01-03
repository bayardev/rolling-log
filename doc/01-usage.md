# Using RollingLog

- [Installation](#installation)
- [Usage](#usage)

## Installation

RollingLog is available on Packagist ([bayardev/rolling-log](http://packagist.org/packages/bayardev/rolling-log))
and as such installable via [Composer](http://getcomposer.org/).

```bash
composer require bayardev/rolling-log
```

If you do not use Composer, you can grab the code from [GitHub](https://github.com/bayardev/rolling-log), and use any
PSR-0 compatible autoloader (e.g. the [Symfony2 ClassLoader component](https://github.com/symfony/ClassLoader))
to load RollingLog classes.

## Usage

You have to configure an event listener or an event subscriber to get RollinLog log event messages automatically for you.
There are many kind of events taht you can listen. For each one you can find a man page :

- [Doctrine](events/01-doctrine.md)

