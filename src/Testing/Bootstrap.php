<?php

declare(strict_types=1);

namespace Forumify\Testing;

use DG\BypassFinals;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Shared PHPUnit bootstrap for forumify test suites.
 *
 * Your tests/bootstrap.php only needs to require the autoloader and call this:
 *
 *     require dirname(__DIR__) . '/vendor/autoload.php';
 *     Forumify\Testing\Bootstrap::boot();
 */
final class Bootstrap
{
    /**
     * @param string|null $projectDir Root of the package under test. Auto-detected when null.
     * @param string|null $dataDir Directory holding test fixtures, exposed as the TEST_DATA_DIR
     *      constant. Defaults to tests/Data, falling back to tests/data.
     */
    public static function boot(?string $projectDir = null, ?string $dataDir = null): void
    {
        $projectDir ??= \dirname(ForumifyTestKernel::locateTestAppDir());

        (new Dotenv())->bootEnv($projectDir . '/.env');

        umask(0000);

        if (!\defined('TEST_DATA_DIR')) {
            \define('TEST_DATA_DIR', $dataDir ?? self::locateDataDir($projectDir));
        }

        BypassFinals::enable();
    }

    private static function locateDataDir(string $projectDir): string
    {
        foreach (['/tests/Data', '/tests/data'] as $candidate) {
            if (is_dir($projectDir . $candidate)) {
                return $projectDir . $candidate;
            }
        }

        return $projectDir . '/tests/Data';
    }
}
