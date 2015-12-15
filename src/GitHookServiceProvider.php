<?php

namespace Docit\GitHook;

use Sebwite\Support\ServiceProvider;

/**
* The main service provider
*
* @author        Sebwite
* @copyright  Copyright (c) 2015, Sebwite
* @license      https://tldrlegal.com/license/mit-license MIT
* @package      Docit\GitHook
*/
class GitHookServiceProvider extends ServiceProvider
{
    protected $dir = __DIR__;

    protected $configFiles = [ 'docit.git-hook' ];

    protected $providers = [
        \Docit\GitHook\Providers\ConsoleServiceProvider::class
    ];
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $app = parent::boot();
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $app = parent::register();
    }
}
