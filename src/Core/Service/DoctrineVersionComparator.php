<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Doctrine\Migrations\Version\Comparator;
use Doctrine\Migrations\Version\Version;

class DoctrineVersionComparator implements Comparator
{
    /**
     * Ignores the namespace of the version so plugins.
     * Allows users to depend on forumify tables existing and not being changed by a later migration.
     *
     * @param Version $a
     * @param Version $b
     * @return int
     */
    public function compare(Version $a, Version $b): int
    {
        $aStr = (string)$a;
        $aVersion = substr($aStr, strpos($aStr, '\\') + 1);

        $bStr = (string)$b;
        $bVersion = substr($bStr, strpos($bStr, '\\') + 1);

        return strcmp($aVersion, $bVersion);
    }
}
