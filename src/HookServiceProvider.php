<?php

namespace Docit\Hooks\Git;

use Docit\Core\Traits\DocitProviderTrait;
use Sebwite\Support\ServiceProvider;

/**
 * The main service provider
 *
 * @author        Sebwite
 * @copyright     Copyright (c) 2015, Sebwite
 * @license       https://tldrlegal.com/license/mit-license MIT
 * @package       Docit\GitHook
 */
class HookServiceProvider extends ServiceProvider
{
    use DocitProviderTrait;

    protected $dir = __DIR__;

    protected $configFiles = [ 'docit.hooks.git' ];

    protected $providers = [
        Providers\ConsoleServiceProvider::class,
        Providers\RouteServiceProvider::class,
        \Sebwite\Git\GitServiceProvider::class
    ];

    protected $singletons = [
        'docit.hooks.git'        => Factory::class,
        'docit.hooks.git.syncer' => Sync\Syncer::class
    ];

    protected $aliases = [
        'docit.hooks.git'        => Contracts\Factory::class,
        'docit.hooks.git.syncer' => Contracts\Syncer::class
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

        $this->addRouteProjectNameExclusions('git-hook-webhook');

        // Add the hook which merges the docit config.
        $this->addDocitHook('factory:ready', FactoryHook::class);

        // And add the hook providing the  `gith` method for projects to retreive a gitsync instance for that specific project
        $this->addDocitHook('project:ready', ProjectHook::class);
    }
}
