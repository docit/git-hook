<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Codex\Hooks\Git\Hook;

use Codex\Core\Contracts\Hook;
use Codex\Core\Project;

/**
 * This is the Hook.
 *
 * @package        Codex\Core
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class ProjectHook implements Hook
{


    /**
     * handle
     *
     * @param \Codex\Core\Project $project
     */
    public function handle(Project $project)
    {
        $that = $this;
        // Add a method on the project class that creates a new GitSync for that specific project
        Project::macro('gitSyncer', function () {

            /** @var Project $this */
            return app('codex.hooks.git')->gitSyncer($this);
        });


    }
}
