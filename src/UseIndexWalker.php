<?php

declare(strict_types=1);

namespace Tourze\DoctrineUseIndexWalker;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Quick hack to allow adding a USE INDEX on the query
 */
class UseIndexWalker extends SqlWalker
{
    public const HINT_USE_INDEX = 'UseIndexWalker.UseIndex';

    public const HINT_FORCE_INDEX = 'UseIndexWalker.ForceIndex';

    public function walkFromClause($fromClause): string
    {
        $sql = parent::walkFromClause($fromClause);

        // 这个目前只有MySQL支持
        if ($this->getConnection()->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
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
}
