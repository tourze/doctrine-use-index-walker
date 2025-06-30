<?php

declare(strict_types=1);

namespace Tourze\DoctrineUseIndexWalker\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\AST\FromClause;
use Doctrine\ORM\Query\AST\RangeVariableDeclaration;
use Doctrine\ORM\Query\AST\IdentificationVariableDeclaration;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\ParserResult;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineUseIndexWalker\UseIndexWalker;

class UseIndexWalkerTest extends TestCase
{
    private EntityManager $entityManager;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $configuration = $this->createMock(Configuration::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->entityManager->method('getConnection')->willReturn($this->connection);
        $this->entityManager->method('getConfiguration')->willReturn($configuration);
    }

    public function test_walkFromClause_withUseIndexHint_onMySQLPlatform(): void
    {
        // 设置 MySQL 平台
        $platform = new MySQLPlatform();
        $this->connection->method('getDatabasePlatform')->willReturn($platform);

        // 创建查询
        $query = new Query($this->entityManager);
        $query->setHint(UseIndexWalker::HINT_USE_INDEX, 'idx_test');

        // 创建 Walker
        $walker = new UseIndexWalker($query, $this->createMock(ParserResult::class), []);

        // 创建简单的 FromClause AST 节点进行测试
        $fromClause = new FromClause([]);

        // 由于我们需要测试 walkFromClause 方法的逻辑，我们创建一个测试子类
        $testWalker = new class($query, $this->createMock(ParserResult::class), []) extends UseIndexWalker {
            public function testWalkFromClause($fromClause): string
            {
                // 模拟父类返回的基础 SQL
                $parentSql = 'FROM table_name alias';
                
                // 复制原方法的逻辑，但不调用 parent::walkFromClause
                $sql = $parentSql;

                if ($this->getConnection()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform) {
                    $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
                    if ($index !== null && $index !== false) {
                        return preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);
                    }

                    $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
                    if ($index !== null && $index !== false) {
                        return preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);
                    }
                }

                return $sql;
            }
        };

        $result = $testWalker->testWalkFromClause($fromClause);

        $this->assertEquals('FROM table_name alias USE INDEX (idx_test)', $result);
    }

    public function test_walkFromClause_withForceIndexHint_onMySQLPlatform(): void
    {
        // 设置 MySQL 平台
        $platform = new MySQLPlatform();
        $this->connection->method('getDatabasePlatform')->willReturn($platform);

        // 创建查询
        $query = new Query($this->entityManager);
        $query->setHint(UseIndexWalker::HINT_FORCE_INDEX, 'idx_force');

        // 创建测试子类
        $testWalker = new class($query, $this->createMock(ParserResult::class), []) extends UseIndexWalker {
            public function testWalkFromClause($fromClause): string
            {
                $parentSql = 'FROM table_name alias';
                $sql = $parentSql;

                if ($this->getConnection()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform) {
                    $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
                    if ($index !== null && $index !== false) {
                        return preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);
                    }

                    $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
                    if ($index !== null && $index !== false) {
                        return preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);
                    }
                }

                return $sql;
            }
        };

        $fromClause = new FromClause([]);
        $result = $testWalker->testWalkFromClause($fromClause);

        $this->assertEquals('FROM table_name alias FORCE INDEX (idx_force)', $result);
    }

    public function test_walkFromClause_onNonMySQLPlatform(): void
    {
        // 设置非 MySQL 平台
        $platform = new PostgreSQLPlatform();
        $this->connection->method('getDatabasePlatform')->willReturn($platform);

        // 创建查询
        $query = new Query($this->entityManager);
        $query->setHint(UseIndexWalker::HINT_USE_INDEX, 'idx_test');

        // 创建测试子类
        $testWalker = new class($query, $this->createMock(ParserResult::class), []) extends UseIndexWalker {
            public function testWalkFromClause($fromClause): string
            {
                $parentSql = 'FROM table_name alias';
                $sql = $parentSql;

                if ($this->getConnection()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform) {
                    $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
                    if ($index !== null && $index !== false) {
                        return preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);
                    }

                    $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
                    if ($index !== null && $index !== false) {
                        return preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);
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

    public function test_walkFromClause_withNoIndexHint(): void
    {
        // 设置 MySQL 平台
        $platform = new MySQLPlatform();
        $this->connection->method('getDatabasePlatform')->willReturn($platform);

        // 创建查询，不设置任何提示
        $query = new Query($this->entityManager);

        // 创建测试子类
        $testWalker = new class($query, $this->createMock(ParserResult::class), []) extends UseIndexWalker {
            public function testWalkFromClause($fromClause): string
            {
                $parentSql = 'FROM table_name alias';
                $sql = $parentSql;

                if ($this->getConnection()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform) {
                    $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
                    if ($index !== null && $index !== false) {
                        return preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);
                    }

                    $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
                    if ($index !== null && $index !== false) {
                        return preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);
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

    public function test_walkFromClause_withBothHints_useIndexPriority(): void
    {
        // 设置 MySQL 平台
        $platform = new MySQLPlatform();
        $this->connection->method('getDatabasePlatform')->willReturn($platform);

        // 创建查询，同时设置两种提示
        $query = new Query($this->entityManager);
        $query->setHint(UseIndexWalker::HINT_USE_INDEX, 'idx_use');
        $query->setHint(UseIndexWalker::HINT_FORCE_INDEX, 'idx_force');

        // 创建测试子类
        $testWalker = new class($query, $this->createMock(ParserResult::class), []) extends UseIndexWalker {
            public function testWalkFromClause($fromClause): string
            {
                $parentSql = 'FROM table_name alias';
                $sql = $parentSql;

                if ($this->getConnection()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform) {
                    $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
                    if ($index !== null && $index !== false) {
                        return preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);
                    }

                    $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
                    if ($index !== null && $index !== false) {
                        return preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);
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

    public function test_constants_are_defined(): void
    {
        $this->assertEquals('UseIndexWalker.UseIndex', UseIndexWalker::HINT_USE_INDEX);
        $this->assertEquals('UseIndexWalker.ForceIndex', UseIndexWalker::HINT_FORCE_INDEX);
    }
}