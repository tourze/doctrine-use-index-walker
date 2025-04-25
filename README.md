# Doctrine Use Index Walker

这个库提供了一个 Doctrine 查询 SQL Walker，可以在 MySQL 查询中自动添加 `USE INDEX` 或 `FORCE INDEX` 提示。

## 安装

使用 Composer 安装:

```bash
composer require tourze/doctrine-use-index-walker
```

## 基本用法

这个包提供了一个简单的方法来为 Doctrine 查询添加 MySQL 的索引提示，以优化查询性能：

```php
<?php

use Tourze\DoctrineUseIndexWalker\UseIndexWalker;
use Doctrine\ORM\Query;

// 创建一个查询
$query = $entityManager->createQuery('SELECT u FROM User u WHERE u.name = :name');
$query->setParameter('name', 'john');

// 添加 USE INDEX 提示
$query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, [UseIndexWalker::class]);
$query->setHint(UseIndexWalker::HINT_USE_INDEX, 'idx_user_name');

// 或者添加 FORCE INDEX 提示
$query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, [UseIndexWalker::class]);
$query->setHint(UseIndexWalker::HINT_FORCE_INDEX, 'idx_user_name');

// 执行查询
$result = $query->getResult();
```

## 注意事项

- 该功能只在 MySQL 数据库平台上生效
- 如果同时设置了 `USE INDEX` 和 `FORCE INDEX`，`USE INDEX` 将优先生效
- 索引提示的语法取决于 MySQL 版本，请参考 MySQL 文档进行使用

## 如何工作

当 Walker 被应用到查询时，它会检查查询是否使用了 MySQL 数据库平台，并根据提供的提示修改 SQL 语句：

1. 如果设置了 `UseIndexWalker::HINT_USE_INDEX`，会添加 `USE INDEX (index_name)` 到 FROM 子句
2. 如果设置了 `UseIndexWalker::HINT_FORCE_INDEX`，会添加 `FORCE INDEX (index_name)` 到 FROM 子句

## 许可

MIT
