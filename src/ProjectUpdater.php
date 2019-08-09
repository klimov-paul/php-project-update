<?php

namespace KlimovPaul\PhpProjectUpdate;

use InvalidArgumentException;
use KlimovPaul\PhpProjectUpdate\Log\LoggerAwareTrait;
use KlimovPaul\PhpProjectUpdate\Vcs\Git;
use KlimovPaul\PhpProjectUpdate\Vcs\Mercurial;
use KlimovPaul\PhpProjectUpdate\Vcs\VcsContract;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * ProjectUpdater
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ProjectUpdater implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string path to project root directory, which means VCS root directory.
     * For example: '/var/www/myproject'
     */
    public $projectRootPath;
    /**
     * @var array project web path stubs configuration.
     * Each path configuration should have following keys:
     *
     * - 'path': string, path to web root folder
     * - 'link': string, path for the symbolic link, which should point to the web root
     * - 'stub': string, path to folder, which contains stub for the web
     *
     * Yii aliases can be used for all these keys.
     * For example:
     *
     * ```php
     * [
     *     [
     *         'path' => '/home/www/myproject/public',
     *         'link' => '/var/www/vhosts/myproject',
     *         'stub' => '/home/www/myproject/webstub',
     *     ]
     * ]
     * ```
     */
    public $webPaths = [];
    /**
     * @var array list of commands, which should be executed before project update begins.
     * If command is a string it will be executed as shell command, otherwise as PHP callback.
     * For example:
     *
     * ```php
     * [
     *     'mysqldump -h localhost -u root myproject > /path/to/backup/myproject.sql',
     *     'composer install',
     *     'php artisan migrate --force',
     *     'yarn install',
     *     'yarn run prod',
     * ],
     * ```
     */
    public $commands = [];
    /**
     * @var array list of keywords, which presence in the shell command output is considered as
     * its execution error.
     */
    public $shellResponseErrorKeywords = [
        'error',
        'exception',
        'ошибка',
    ];
    /**
     * @var array list of possible version control systems (VCS) in format: `vcsFolderName => classConfig`.
     * VCS will be detected automatically based on which folder is available inside {@see projectRootPath}
     */
    public $versionControlSystems = [
        '.git' => [
            '__class' => Git::class,
        ],
        '.hg' => [
            '__class' => Mercurial::class,
        ],
    ];

    public function handle()
    {
        $this->validateWebPaths();

        $versionControlSystem = $this->detectVersionControlSystem($this->projectRootPath);

        $changesDetected = $versionControlSystem->hasRemoteChanges($this->projectRootPath, $log);
    }

    /**
     * Detects version control system used for the project.
     *
     * @param string $path project root path.
     * @return \KlimovPaul\PhpProjectUpdate\Vcs\VcsContract version control system instance.
     * @throws \RuntimeException on failure.
     */
    protected function detectVersionControlSystem($path): VcsContract
    {
        foreach ($this->versionControlSystems as $folderName => $config) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $folderName)) {
                return Factory::make($config);
            }
        }

        throw new RuntimeException("Unable to detect version control system: neither of '" . implode(', ', array_keys($this->versionControlSystems)) . "' is present under '{$path}'.");
    }

    /**
     * Validates {@see webPaths} value.
     * @throws InvalidArgumentException on invalid configuration.
     */
    protected function validateWebPaths()
    {
        foreach ($this->webPaths as $webPath) {
            if (!isset($webPath['path'], $webPath['link'], $webPath['stub'])) {
                throw new InvalidArgumentException("Web path configuration should contain keys: 'path', 'link', 'stub'");
            }
            if (!is_dir($webPath['path'])) {
                throw new InvalidArgumentException("'{$webPath['path']}' is not a directory.");
            }
            if (!is_dir($webPath['stub'])) {
                throw new InvalidArgumentException("'{$webPath['stub']}' is not a directory.");
            }
            if (!is_link($webPath['link'])) {
                throw new InvalidArgumentException("'{$webPath['link']}' is not a symbolic link.");
            }
            if (!in_array(readlink($webPath['link']), [$webPath['path'], $webPath['stub']])) {
                throw new InvalidArgumentException("'{$webPath['link']}' does not pointing to actual web or stub directory.");
            }
        }
    }

    /**
     * Links web roots to the stub directories.
     * @see webPaths
     */
    protected function linkWebStubs()
    {
        foreach ($this->webPaths as $webPath) {
            if (is_link($webPath['link'])) {
                unlink($webPath['link']);
            }
            symlink($webPath['stub'], $webPath['link']);
        }
    }

    /**
     * Links web roots to the actual web directories.
     * @see webPaths
     */
    protected function linkWebPaths()
    {
        foreach ($this->webPaths as $webPath) {
            if (is_link($webPath['link'])) {
                unlink($webPath['link']);
            }
            symlink($webPath['path'], $webPath['link']);
        }
    }
}
