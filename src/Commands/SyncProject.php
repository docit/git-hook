<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */

namespace Codex\Hooks\Git\Commands;

use Codex\Core\Contracts\Factory;
use Illuminate\Contracts\Queue\Job;

/**
 * This is the CodexSyncGithubProject.
 *
 * @package        Codex\Core
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class SyncProject
{
    protected $codex;

    /**
     * @param \Codex\Core\Contracts\Factory|\Codex\Core\Factory $codex
     */
    public function __construct(Factory $codex)
    {
        $this->codex = $codex;
    }

    public function fire(Job $job, $data)
    {
        $this->codex->log('alert', 'codex.hooks.git.sync.project.command', [
            'jobName'     => $job->getName(),
            'jobAttempts' => $job->attempts(),
            'project'     => $data[ 'project' ]
        ]);

        if ($job->attempts() > 2) {
            $job->delete();
        }
        $this->codex->getProject($data[ 'project' ])->gitSyncer()->syncAll();
    }
}
