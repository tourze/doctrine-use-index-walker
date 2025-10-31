<?php

declare(strict_types=1);

namespace Tourze\DoctrineUseIndexWalker;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\ORM\Query\AST\FromClause;
use Doctrine\ORM\Query\SqlWalker;

/**
 * 快速实现在查询中添加 USE INDEX 的解决方案
 */
class UseIndexWalker extends SqlWalker
{
    public const HINT_USE_INDEX = 'UseIndexWalker.UseIndex';

    public const HINT_FORCE_INDEX = 'UseIndexWalker.ForceIndex';

    public function walkFromClause(FromClause $fromClause): string
    {
        $sql = parent::walkFromClause($fromClause);

        // 这个目前只有MySQL支持
        if ($this->getConnection()->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);
            if (null !== $index && false !== $index && is_string($index)) {
                $result = preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX (' . $index . ')', $sql);

                return $result ?? $sql;
            }

            $index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX);
            if (null !== $index && false !== $index && is_string($index)) {
                $result = preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $sql);

                return $result ?? $sql;
            }
        }

        return $sql;
    }
}
