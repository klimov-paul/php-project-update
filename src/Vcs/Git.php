<?php

namespace KlimovPaul\PhpProjectUpdate\Vcs;

use KlimovPaul\PhpProjectUpdate\Helpers\Shell;
use KlimovPaul\PhpProjectUpdate\Log\LoggerAwareTrait;
use RuntimeException;

/**
 * Git represents GIT version control system.
 *
 * @see https://git-scm.com/
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Git implements VcsContract
{
    use LoggerAwareTrait;

    /**
     * @var string path to the 'git' bin command.
     * By default simple 'git' is used assuming it available as global shell command.
     * It could be '/usr/bin/git' for example.
     */
    public $binPath = 'git';
    /**
     * @var string name of the GIT remote, which should be used to get changes.
     */
    public $remoteName = 'origin';

    /**
     * Returns currently active GIT branch name for the project.
     *
     * @param string $projectRoot VCS project root directory path.
     * @return string branch name.
     * @throws \RuntimeException on failure.
     */
    public function getCurrentBranch($projectRoot)
    {
        $result = Shell::execute('(cd {projectRoot}; {binPath} branch)', [
            '{binPath}' => $this->binPath,
            '{projectRoot}' => $projectRoot,
        ]);

        foreach ($result->outputLines as $line) {
            if (($pos = stripos($line, '* ')) === 0) {
                return trim(substr($line, $pos + 2));
            }
        }

        throw new RuntimeException('Unable to detect current GIT branch: ' . $result->toString());
    }

    /**
     * {@inheritdoc}
     */
    public function hasRemoteChanges($projectRoot): bool
    {
        $placeholders = [
            '{binPath}' => $this->binPath,
            '{projectRoot}' => $projectRoot,
            '{remote}' => $this->remoteName,
            '{branch}' => $this->getCurrentBranch($projectRoot),
        ];

        $fetchResult = Shell::execute('(cd {projectRoot}; {binPath} fetch {remote})', $placeholders);
        $this->getLogger()->info($fetchResult->toString());

        $result = Shell::execute('(cd {projectRoot}; {binPath} diff --numstat HEAD {remote}/{branch})', $placeholders);
        $this->getLogger()->info($result->toString());

        return ($result->isOk() && !$result->isOutputEmpty());
    }

    /**
     * {@inheritdoc}
     */
    public function applyRemoteChanges($projectRoot): bool
    {
        $result = Shell::execute('(cd {projectRoot}; {binPath} merge {remote}/{branch})', [
            '{binPath}' => $this->binPath,
            '{projectRoot}' => $projectRoot,
            '{remote}' => $this->remoteName,
            '{branch}' => $this->getCurrentBranch($projectRoot),
        ]);
        $this->getLogger()->info($result->toString());

        return $result->isOk();
    }
}
