# Doctrine Use Index Walker

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-use-index-walker.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-use-index-walker)
[![Build Status](https://img.shields.io/travis/tourze/doctrine-use-index-walker/master.svg?style=flat-square)](https://travis-ci.org/tourze/doctrine-use-index-walker)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/doctrine-use-index-walker.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/doctrine-use-index-walker)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-use-index-walker.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-use-index-walker)

简要描述：本库为 Doctrine ORM 查询自动添加 MySQL `USE INDEX` 或 `FORCE INDEX` 提示，帮助优化 SQL 查询性能。

## 功能特性

- 支持在 Doctrine 查询中自动插入 `USE INDEX` 或 `FORCE INDEX` 提示
- 仅对 MySQL 平台生效，兼容 Doctrine ORM 2.20+ 和 3.0+
- 操作简单，支持多种索引提示
- 兼容原生 Doctrine 查询 API

## 安装说明

使用 Composer 安装：

```bash
composer require tourze/doctrine-use-index-walker
```

依赖环境：

- PHP 7.4 及以上
- Doctrine ORM 2.20+ 或 3.0+
- Doctrine DBAL 4.0+

## 快速开始

```php
<?php

use Tourze\DoctrineUseIndexWalker\UseIndexWalker;
use Doctrine\ORM\Query;

// 创建查询
$query = $entityManager->createQuery('SELECT u FROM User u WHERE u.name = :name');
$query->setParameter('name', 'zhangsan');

// 添加 USE INDEX 提示
$query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, [UseIndexWalker::class]);
$query->setHint(UseIndexWalker::HINT_USE_INDEX, 'idx_user_name');

// 或添加 FORCE INDEX 提示
$query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, [UseIndexWalker::class]);
$query->setHint(UseIndexWalker::HINT_FORCE_INDEX, 'idx_user_name');

// 执行查询
$result = $query->getResult();
```

## 注意事项

- 仅在 MySQL 数据库平台生效
- 同时设置 `USE INDEX` 和 `FORCE INDEX` 时，优先使用 `USE INDEX`
- 索引提示语法依赖 MySQL 版本，具体请参考 MySQL 官方文档

## 工作原理

当 Walker 被应用于查询时，会检测数据库平台是否为 MySQL，并根据设置的 Hint 修改 SQL：

1. 设置 `UseIndexWalker::HINT_USE_INDEX` 时，FROM 子句自动加 `USE INDEX (index_name)`
2. 设置 `UseIndexWalker::HINT_FORCE_INDEX` 时，FROM 子句自动加 `FORCE INDEX (index_name)`

## 贡献指南

欢迎提交 Issue 和 PR，建议遵循 PSR 代码规范并补充单元测试。

## 版权和许可

MIT License
