<?php

namespace Docit\GitHook\Console;

use Sebwite\Support\Command;

class GitHookTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var  string
     */
    protected $signature = 'git-hook:test';

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
        $this->line('Test');
    }
}
