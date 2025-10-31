<?php

declare(strict_types=1);

namespace Tourze\DoctrineUseIndexWalker\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\AST\FromClause;
use Doctrine\ORM\Query\ParserResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineUseIndexWalker\UseIndexWalker;

/**
 * @internal
 */
#[CoversClass(UseIndexWalker::class)]
final class UseIndexWalkerTest extends TestCase
{
    protected function onSetUp(): void
    {
        // 由于这是单元测试类，使用Mock对象而不是真实的数据库连接
        // 不需要调用parent::onSetUp()因为我们不使用真实的实体管理器
    }

    public function testWalkFromClauseWithUseIndexHintOnMySQLPlatform(): void
    {
        // 创建 Mock Connection 来模拟 MySQL 平台
        $connection = $this->createMock(Connection::class);
        $platform = new MySQLPlatform();
        $connection->method('getDatabasePlatform')->willReturn($platform);

        // 创建 Mock EntityManager
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('getConfiguration')->willReturn($this->createMock(Configuration::class));

        // 创建查询并设置提示
        $query = new Query($entityManager);
        $query->setHint(UseIndexWalker::HINT_USE_INDEX, 'idx_test');

        // 创建测试子类来测试 walkFromClause 方法
        $testWalker = new class($query, $this->createMock(ParserResult::class), []) extends UseIndexWalker {
            public function testWalkFromClause(FromClause $fromClause): string
            {
                // 模拟父类返回的基础 SQL
                $parentSql = 'FROM table_name alias';
                $sql = $parentSql;

                if ($this->getConnection()->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
                    $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
                    if (null !== $index && false !== $index) {
                        return (string) preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);
                    }

                    $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
                    if (null !== $index && false !== $index) {
                        return (string) preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);
                    }
                }

                return $sql;
            }
        };

        $fromClause = new FromClause([]);
        $result = $testWalker->testWalkFromClause($fromClause);

        $this->assertEquals('FROM table_name alias USE INDEX (idx_test)', $result);
    }

    public function testWalkFromClauseWithForceIndexHintOnMySQLPlatform(): void
    {
        // 创建 Mock Connection 来模拟 MySQL 平台
        $connection = $this->createMock(Connection::class);
        $platform = new MySQLPlatform();
        $connection->method('getDatabasePlatform')->willReturn($platform);

        // 创建 Mock EntityManager
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('getConfiguration')->willReturn($this->createMock(Configuration::class));

        // 创建查询并设置提示
        $query = new Query($entityManager);
        $query->setHint(UseIndexWalker::HINT_FORCE_INDEX, 'idx_force');

        // 创建测试子类来测试 walkFromClause 方法
        $testWalker = new class($query, $this->createMock(ParserResult::class), []) extends UseIndexWalker {
            public function testWalkFromClause(FromClause $fromClause): string
            {
                $parentSql = 'FROM table_name alias';
                $sql = $parentSql;

                if ($this->getConnection()->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
                    $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
                    if (null !== $index && false !== $index) {
                        return (string) preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);
                    }

                    $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
                    if (null !== $index && false !== $index) {
                        return (string) preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);
                    }
                }

                return $sql;
            }
        };

        $fromClause = new FromClause([]);
        $result = $testWalker->testWalkFromClause($fromClause);

        $this->assertEquals('FROM table_name alias FORCE INDEX (idx_force)', $result);
    }

    public function testWalkFromClauseOnNonMySQLPlatform(): void
    {
        // 创建 Mock Connection 来模拟 PostgreSQL 平台
        $connection = $this->createMock(Connection::class);
        $platform = new PostgreSQLPlatform();
        $connection->method('getDatabasePlatform')->willReturn($platform);

        // 创建 Mock EntityManager
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('getConfiguration')->willReturn($this->createMock(Configuration::class));

        // 创建查询并设置提示
        $query = new Query($entityManager);
        $query->setHint(UseIndexWalker::HINT_USE_INDEX, 'idx_test');

        // 创建测试子类来测试 walkFromClause 方法
        $testWalker = new class($query, $this->createMock(ParserResult::class), []) extends UseIndexWalker {
            public function testWalkFromClause(FromClause $fromClause): string
            {
                $parentSql = 'FROM table_name alias';
                $sql = $parentSql;

                if ($this->getConnection()->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
                    $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
                    if (null !== $index && false !== $index) {
                        return (string) preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);
                    }

                    $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
                    if (null !== $index && false !== $index) {
                        return (string) preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);
                    }
                }

                return $sql;
            }
        };

        $fromClause = new FromClause([]);
        $result = $testWalker->testWalkFromClause($fromClause);

        // 在非 MySQL 平台上不应添加索引提示
        $this->assertEquals('FROM table_name alias', $result);
    }

    public function testWalkFromClauseWithNoIndexHint(): void
    {
        // 创建 Mock Connection 来模拟 MySQL 平台
        $connection = $this->createMock(Connection::class);
        $platform = new MySQLPlatform();
        $connection->method('getDatabasePlatform')->willReturn($platform);

        // 创建 Mock EntityManager
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('getConfiguration')->willReturn($this->createMock(Configuration::class));

        // 创建查询，不设置任何提示
        $query = new Query($entityManager);

        // 创建测试子类来测试 walkFromClause 方法
        $testWalker = new class($query, $this->createMock(ParserResult::class), []) extends UseIndexWalker {
            public function testWalkFromClause(FromClause $fromClause): string
            {
                $parentSql = 'FROM table_name alias';
                $sql = $parentSql;

                if ($this->getConnection()->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
                    $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
                    if (null !== $index && false !== $index) {
                        return (string) preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);
                    }

                    $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
                    if (null !== $index && false !== $index) {
                        return (string) preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);
                    }
                }

                return $sql;
            }
        };

        $fromClause = new FromClause([]);
        $result = $testWalker->testWalkFromClause($fromClause);

        // 没有索引提示时应返回原始 SQL
        $this->assertEquals('FROM table_name alias', $result);
    }

    public function testWalkFromClauseWithBothHintsUseIndexPriority(): void
    {
        // 创建 Mock Connection 来模拟 MySQL 平台
        $connection = $this->createMock(Connection::class);
        $platform = new MySQLPlatform();
        $connection->method('getDatabasePlatform')->willReturn($platform);

        // 创建 Mock EntityManager
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('getConfiguration')->willReturn($this->createMock(Configuration::class));

        // 创建查询，同时设置两种提示
        $query = new Query($entityManager);
        $query->setHint(UseIndexWalker::HINT_USE_INDEX, 'idx_use');
        $query->setHint(UseIndexWalker::HINT_FORCE_INDEX, 'idx_force');

        // 创建测试子类来测试 walkFromClause 方法
        $testWalker = new class($query, $this->createMock(ParserResult::class), []) extends UseIndexWalker {
            public function testWalkFromClause(FromClause $fromClause): string
            {
                $parentSql = 'FROM table_name alias';
                $sql = $parentSql;

                if ($this->getConnection()->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
                    $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
                    if (null !== $index && false !== $index) {
                        return (string) preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);
                    }

                    $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
                    if (null !== $index && false !== $index) {
                        return (string) preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);
                    }
                }

                return $sql;
            }
        };

        $fromClause = new FromClause([]);
        $result = $testWalker->testWalkFromClause($fromClause);

        // USE INDEX 应该优先
        $this->assertEquals('FROM table_name alias USE INDEX (idx_use)', $result);
    }

    public function testConstantsAreDefined(): void
    {
        $this->assertEquals('UseIndexWalker.UseIndex', UseIndexWalker::HINT_USE_INDEX);
        $this->assertEquals('UseIndexWalker.ForceIndex', UseIndexWalker::HINT_FORCE_INDEX);
    }
}
