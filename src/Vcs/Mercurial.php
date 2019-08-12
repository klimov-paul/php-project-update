<?php

namespace KlimovPaul\PhpProjectUpdate\Vcs;

use KlimovPaul\PhpProjectUpdate\Log\LoggerAwareTrait;
use KlimovPaul\PhpProjectUpdate\Helpers\Shell;

/**
 * Mercurial represents Mercurial (Hg) version control system.
 *
 * @see https://mercurial.selenic.com/
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Mercurial implements VcsContract
{
    use LoggerAwareTrait;

    /**
     * @var string path to the 'hg' bin command.
     * By default simple 'hg' is used assuming it available as global shell command.
     * It could be '/usr/bin/hg' for example.
     */
    public $binPath = 'hg';

    /**
     * Returns currently active Mercurial branch name for the project.
     *
     * @param string $projectRoot VCS project root directory path.
     * @return string branch name.
     */
    public function getCurrentBranch($projectRoot)
    {
        $result = Shell::execute('(cd {projectRoot}; {binPath} branch)', [
            '{binPath}' => $this->binPath,
            '{projectRoot}' => $projectRoot,
        ]);

        return $result->outputLines[0];
    }

    /**
     * {@inheritdoc}
     */
    public function hasRemoteChanges($projectRoot): bool
    {
        $result = Shell::execute("(cd {projectRoot}; {binPath} incoming -b {branch} --newest-first --limit 1)", [
            '{binPath}' => $this->binPath,
            '{projectRoot}' => $projectRoot,
            '{branch}' => $this->getCurrentBranch($projectRoot),
        ]);
        $this->getLogger()->info($result->toString());

        return $result->isOk();
    }

    /**
     * {@inheritdoc}
     */
    public function applyRemoteChanges($projectRoot): bool
    {
        $result = Shell::execute('(cd {projectRoot}; {binPath} pull -b {branch} -u)', [
            '{binPath}' => $this->binPath,
            '{projectRoot}' => $projectRoot,
            '{branch}' => $this->getCurrentBranch($projectRoot),
        ]);
        $this->getLogger()->info($result->toString());

        return $result->isOk();
    }
}
