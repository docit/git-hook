<?php

namespace Docit\GitHook\Providers;

use Sebwite\Support\ConsoleServiceProvider as BaseConsoleProvider;

/**
* This is the ConsoleServiceProvider.
*
* @author        Sebwite
* @copyright  Copyright (c) 2015, Sebwite
* @license      https://tldrlegal.com/license/mit-license MIT
* @package      Docit\GitHook
*/
class ConsoleServiceProvider extends BaseConsoleProvider
{
    /**
     * @var  string
     */
    protected $namespace = 'Docit\\GitHook\\Console';

    /**
     * @var  string
     */
    protected $prefix = 'docit.git-hook.commands.';

    /**
     * @var  array
     */
    protected $commands = [
        'test'   => 'GitHookTest'
    ];
}
