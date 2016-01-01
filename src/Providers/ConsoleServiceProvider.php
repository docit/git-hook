<?php

namespace Codex\Hooks\Git\Providers;

use Sebwite\Support\ConsoleServiceProvider as BaseConsoleProvider;

/**
* This is the ConsoleServiceProvider.
*
* @author        Sebwite
* @copyright  Copyright (c) 2015, Sebwite
* @license      https://tldrlegal.com/license/mit-license MIT
* @package      Codex\GitHook
*/
class ConsoleServiceProvider extends BaseConsoleProvider
{
    /**
     * @var  string
     */
    protected $namespace = 'Codex\\Hooks\\Git\\Console';

    /**
     * @var  string
     */
    protected $prefix = 'codex.hooks.git.commands.';

    /**
     * @var  array
     */
    protected $commands = [
        'sync'   => 'GitHookSync'
    ];
}
