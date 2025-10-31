# Doctrine Use Index Walker

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-use-index-walker.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-use-index-walker)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/doctrine-use-index-walker.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-use-index-walker)
[![License](https://img.shields.io/packagist/l/tourze/doctrine-use-index-walker.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-use-index-walker)
[![Build Status](https://img.shields.io/travis/tourze/doctrine-use-index-walker/master.svg?style=flat-square)](https://travis-ci.org/tourze/doctrine-use-index-walker)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/doctrine-use-index-walker.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/doctrine-use-index-walker)
[![Coverage Status](https://img.shields.io/coveralls/github/tourze/doctrine-use-index-walker/master.svg?style=flat-square)](https://coveralls.io/github/tourze/doctrine-use-index-walker?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-use-index-walker.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-use-index-walker)

A Doctrine ORM SQL Walker for automatically adding `USE INDEX` or `FORCE INDEX` hints to MySQL queries, helping you optimize SQL performance with minimal code changes.

## Features

- Automatically injects `USE INDEX` or `FORCE INDEX` hints into Doctrine queries
- Works only on MySQL platforms, compatible with Doctrine ORM 3.0+
- Simple usage via query hints
- Fully compatible with native Doctrine query API

## Installation

Install via Composer:

```bash
composer require tourze/doctrine-use-index-walker
```

Requirements:

- PHP 8.2 or higher
- Doctrine ORM 3.0+
- Doctrine DBAL 4.0+

## Quick Start

```php
<?php

use Tourze\DoctrineUseIndexWalker\UseIndexWalker;
use Doctrine\ORM\Query;

// Create a query
$query = $entityManager->createQuery('SELECT u FROM User u WHERE u.name = :name');
$query->setParameter('name', 'john');

// Add USE INDEX hint
$query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, [UseIndexWalker::class]);
$query->setHint(UseIndexWalker::HINT_USE_INDEX, 'idx_user_name');

// Or add FORCE INDEX hint
$query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, [UseIndexWalker::class]);
$query->setHint(UseIndexWalker::HINT_FORCE_INDEX, 'idx_user_name');

// Execute query
$result = $query->getResult();
```

## Notes

- Only works on MySQL database platforms
- If both `USE INDEX` and `FORCE INDEX` are set, `USE INDEX` takes precedence
- Index hint syntax depends on your MySQL version; refer to the MySQL documentation for details

## How It Works

When the walker is applied to a query, it checks if the platform is MySQL and modifies the SQL accordingly:

1. If `UseIndexWalker::HINT_USE_INDEX` is set, adds `USE INDEX (index_name)` to the FROM clause
2. If `UseIndexWalker::HINT_FORCE_INDEX` is set, adds `FORCE INDEX (index_name)` to the FROM clause

## Contributing

Feel free to open issues or pull requests. Please follow PSR coding standards and include tests where possible.

## License

MIT License
