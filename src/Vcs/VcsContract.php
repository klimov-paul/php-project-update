<?php

namespace KlimovPaul\PhpProjectUpdate\Vcs;

use Psr\Log\LoggerAwareInterface;

/**
 * VcsContract is an interface, which particular version control system implementation should match
 * in order to be used for project update.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface VcsContract extends LoggerAwareInterface
{
    /**
     * Checks, if there are some changes in remote repository.
     * Operation should be logged.
     *
     * @param string $projectRoot VCS project root directory path.
     * @return bool whether there are changes in remote repository.
     */
    public function hasRemoteChanges($projectRoot): bool;

    /**
     * Applies changes from remote repository.
     * Operation should be logged.
     *
     * @param string $projectRoot VCS project root directory path.
     * @return bool whether the changes have been applied successfully.
     */
    public function applyRemoteChanges($projectRoot): bool;
}
