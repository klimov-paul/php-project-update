<?php

namespace KlimovPaul\PhpProjectUpdate\Vcs;

/**
 * VcsContract is an interface, which particular version control system implementation should match
 * in order to be used for project update.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface VcsContract
{
    /**
     * Checks, if there are some changes in remote repository.
     *
     * @param string $projectRoot VCS project root directory path.
     * @param string $log if parameter passed it will be filled with related log string.
     * @return bool whether there are changes in remote repository.
     */
    public function hasRemoteChanges($projectRoot, &$log = null): bool;

    /**
     * Applies changes from remote repository.
     *
     * @param string $projectRoot VCS project root directory path.
     * @param string $log if parameter passed it will be filled with related log string.
     * @return bool whether the changes have been applied successfully.
     */
    public function applyRemoteChanges($projectRoot, &$log = null): bool;
}
