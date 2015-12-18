<?php

namespace Docit\Hooks\Git\Console;

use Docit\Hooks\Git\Sync\Syncer;
use Guzzle\Http\Message\Request;
use Sebwite\Support\Command;

class GitHookTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var  string
     */
    protected $signature = 'docit:git:test';

    /**
     * The console command description.
     *
     * @var  string
     */
    protected $description = 'git-hook test command';

    /**
     * Execute the console command.
     *
     * @return  mixed
     */
    public function handle()
    {
        $project = app('docit')->getProject('sebwite-platform');
        $s = $project->gitSyncer();
        $s->syncAll();

        $this->line('Test');
    }
}
