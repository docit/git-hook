<?php
/**
 * Part of Robin Radic's PHP packages.
 *
 * MIT License and copyright information bundled with this package
 * in the LICENSE file or visit http://radic.mit-license.org
 */
namespace Codex\Hooks\Git\Console;

use Codex\Core\Contracts\Factory;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Sebwite\Support\Command;

/**
 * This is the CoreListCommand class.
 *
 * @package                   Codex\Core
 * @version                   1.0.0
 * @author                    Robin Radic
 * @license                   MIT License
 * @copyright                 2015, Robin Radic
 * @link                      https://github.com/robinradic
 */
class GitHookSyncCommand extends Command
{
    use DispatchesJobs;

    protected $signature = 'codex:git:sync {--queue : Put the sync job on the queue}';

    protected $description = 'Synchronise all Github projects.';

    /** @var \Codex\Core\Factory */
    protected $factory;

    protected $queue;

    public function __construct(Factory $factory, Queue $queue)
    {
        parent::__construct();
        $this->factory = $factory;
        $this->queue   = $queue;
    }

    public function handle()
    {

        $projects = [ ];
        $choices  = [ ];
        foreach ($this->factory->getProjects() as $project) {
            if ($project->config('enable_git_hook', false) === true) {
                $projects[] = $project;
                $choices[]  = $project->getName();
            }
        }
        $project = $this->choice('Pick the git enabled project you wish to sync', $choices);

        if ($this->option('queue')) {
            app('codex.hooks.git')->createSyncJob($project);
            $this->comment('Created sync job and pushed it onto the queue.');
        } else {
            $this->comment('Starting synchronisation. This might take a while.');
            app('codex.hooks.git')->gitSyncer($this->factory->getProject($project))->syncAll();
        }
    }
}
