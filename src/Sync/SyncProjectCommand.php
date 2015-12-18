<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */

namespace Docit\Hooks\Git\Sync;

use Docit\Core\Contracts\Factory;
use Illuminate\Contracts\Queue\Job;

/**
 * This is the DocitSyncGithubProject.
 *
 * @package        Docit\Core
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class SyncProjectCommand
{
    protected $docit;

    /**
     * @param \Docit\Core\Contracts\Factory|\Docit\Core\Factory $docit
     * @internal param \Illuminate\Contracts\Logging\Log $Log
     */
    public function __construct(Factory $docit)
    {
        $this->docit = $docit;
    }

    public function fire(Job $job, $data)
    {
        $this->docit->log('alert', 'docit.hooks.git.sync.project.command', [
            'jobName'     => $job->getName(),
            'jobAttempts' => $job->attempts(),
            'project'     => $data[ 'project' ]
        ]);

        if ($job->attempts() > 2) {
            $job->delete();
        }
        $this->docit->getProject($data[ 'project' ])->gitSyncer()->syncAll();
    }
}
