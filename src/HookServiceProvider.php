<?php

namespace Codex\Hooks\Git;

use Codex\Core\Traits\ProvidesCodex;
use Codex\Hooks\Git\Hook\FactoryHook;
use Codex\Hooks\Git\Hook\ProjectHook;
use Sebwite\Support\ServiceProvider;

/**
 * The main service provider
 *
 * @author        Sebwite
 * @copyright     Copyright (c) 2015, Sebwite
 * @license       https://tldrlegal.com/license/mit-license MIT
 * @package       Codex\GitHook
 */
class HookServiceProvider extends ServiceProvider
{
    use ProvidesCodex;

    protected $dir = __DIR__;

    protected $configFiles = [ 'codex.hooks.git' ];

    protected $providers = [
        Providers\ConsoleServiceProvider::class,
        Providers\RouteServiceProvider::class,
        \Sebwite\Git\GitServiceProvider::class
    ];

    protected $singletons = [
        'codex.hooks.git'        => Factory::class,
        'codex.hooks.git.syncer' => \Codex\Hooks\Git\Syncer::class
    ];

    protected $aliases = [
        'codex.hooks.git'        => Contracts\Factory::class,
        'codex.hooks.git.syncer' => Contracts\Syncer::class
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

        // Add the hook which merges the codex config.
        $this->addCodexHook('factory:ready', FactoryHook::class);

        // And add the hook providing the  `gith` method for projects to retreive a gitsync instance for that specific project
        $this->addCodexHook('project:ready', ProjectHook::class);
    }
}
