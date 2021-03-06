<?php
/**
 * Part of the Codex PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */
namespace Codex\Hooks\Git;

use Codex\Core\Contracts\Factory as Codex;
use Codex\Core\Project;
use Codex\Core\Traits\Hookable;
use Codex\Hooks\Git\Commands\SyncProject;
use Codex\Hooks\Git\Contracts\Factory as FactoryContract;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\Queue;
use Sebwite\Git\Contracts\Manager;

/**
 * This is the class Factory.
 *
 * @package        Codex\Hooks
 * @author         Codex
 * @copyright      Copyright (c) 2015, Codex. All rights reserved
 */
class Factory implements FactoryContract
{
    use Hookable;
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Codex\Core\Contracts\Factory
     */
    protected $codex;

    /**
     * @var \Illuminate\Contracts\Queue\Queue
     */
    protected $queue;

    /**
     * @var \Sebwite\Git\Contracts\Manager|\Sebwite\Git\Remotes\Manager
     */
    protected $git;

    /**
     * Factory constructor.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $files
     * @param \Codex\Core\Contracts\Factory               $codex
     * @param \Illuminate\Contracts\Queue\Queue           $queue
     * @param \Illuminate\Contracts\Cache\Repository      $cache
     * @param \Sebwite\Git\Contracts\Manager              $git
     */
    public function __construct(Filesystem $files, Codex $codex, Queue $queue, Manager $git)
    {
        $this->files = $files;
        $this->codex = $codex;
        $this->queue = $queue;
        $this->git   = $git;

        $this->runHook('git:factory:done', [ $this ]);
    }

    public function gitSyncer(Project $project)
    {
        $syncer = app()->make(Syncer::class, [
            'project' => $project
        ]);

        return $syncer;
    }

    public function createSyncJob($project)
    {
        if ($project instanceof Project) {
            $project = $project->getName();
        }
        $this->queue->push(SyncProject::class, compact('project'));
    }

    /**
     * get fsm value
     *
     * @return Filesystem
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set the fsm value
     *
     * @param Filesystem $files
     * @return Factory
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * get codex value
     *
     * @return Codex
     */
    public function getCodex()
    {
        return $this->codex;
    }

    /**
     * Set the codex value
     *
     * @param Codex $codex
     * @return Factory
     */
    public function setCodex($codex)
    {
        $this->codex = $codex;

        return $this;
    }

    /**
     * get queue value
     *
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Set the queue value
     *
     * @param Queue $queue
     * @return Factory
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * get cache value
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set the cache value
     *
     * @param Cache $cache
     * @return Factory
     */
    public function setCache($cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * get git value
     *
     * @return Manager|\Sebwite\Git\Remotes\Manager
     */
    public function getGit()
    {
        return $this->git;
    }

    /**
     * Set the git value
     *
     * @param Manager|\Sebwite\Git\Remotes\Manager $git
     * @return Factory
     */
    public function setGit($git)
    {
        $this->git = $git;

        return $this;
    }
}
