[![Build Status](https://img.shields.io/travis/frankfoerster/cakephp-migrations/master.svg?style=flat-square)](https://travis-ci.org/frankfoerster/cakephp-migrations.png?branch=master)

# Migrations Plugin for CakePHP 2.3+

This migrations plugin is based on the work of "CakeDC/migrations":https://github.com/CakeDC/migrations.

The main goals for the rewrite are:

- move the migration commands to their own functions "up" and "down"
- make all migration actions as atomic as possible to ease testing
- use PHP + CakePHP methods, models in your migrations
- no need for "before" or "after" callbacks since migrations are method calls
- remove migration mappings
instead use file names with numeric and datetime prefixes + unique migration class names

## Requirements

PHP 5.3+
CakePHP 2.3+

## Installation

via composer:

```
composer require frankfoerster/cakephp-migrations:~1.0
```

In your `app/Config/bootstrap.php` add:

```
CakePlugin::load('Migrations', array('bootstrap' => false, 'routes' => false));
```

## Usage via Shell

```
cd app
Console/cake Migrations.Migration migrate up
Console/cake Migrations.Migration migrate down
Console/cake Migrations.Migration migrate down 1
```

## License

Copyright (c) Frank Förster ([frankfoerster](https://github.com/frankfoerster))

Licensed under [The MIT License](https://github.com/frankfoerster/cakephp-migrations/blob/master/LICENSE)
Redistributions of files must retain the above copyright notice.
