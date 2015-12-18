<?php
/**
 * Part of the Robin Radic's PHP packages.
 *
 * MIT License and copyright information bundled with this package
 * in the LICENSE file or visit http://radic.mit-license.com
 */
namespace Docit\Hooks\Git\Sync;

use Docit\Core\Project;
use Docit\Core\Traits\Hookable;
use Docit\Hooks\Git\Contracts\Syncer as SyncerContract;
use Docit\Support\Path;
use Illuminate\Contracts\Cache\Repository as Cache;
use Sebwite\Git\Contracts\Manager;
use Symfony\Component\Yaml\Yaml;
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\SemVerException;
use vierbergenlars\SemVer\version;

/**
 * This is the class Syncer.
 *
 * @package        Docit\Hooks
 * @author         Docit
 * @copyright      Copyright (c) 2015, Docit. All rights reserved
 */
class Syncer implements SyncerContract
{
    use Hookable;

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var string
     */
    protected $remote;

    /**
     * @var \Sebwite\Git\Remotes\Manager
     */
    protected $git;

    /**
     * @var \Docit\Core\Project
     */
    protected $project;

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    public function __construct(Project $project, Manager $git, Cache $cache)
    {
        $this->project = $project;
        $this->git     = $git;
        $this->cache   = $cache;
        $this->files   = $project->getFiles();

        $this->remote = 'github';

        $this->runHook('git:syncer', [ $this ]);
    }

    public function syncWithProgress(\Closure $tick)
    {
        $current = 0;
        foreach ($this->getBranchesToSync() as $branch) {
            $this->syncRef($branch, 'branch');
            $current++;
            $tick($current);
        }
        foreach ($this->getVersionsToSync() as $version) {
            $this->syncRef($version, 'tag');
            $current++;
            $tick($current);
        }
    }

    public function syncAll()
    {
        $this->syncBranches();
        $this->syncVersions();
    }

    public function syncBranches()
    {
        foreach ($this->getBranchesToSync() as $branch) {
            $this->syncRef($branch, 'branch');
        }
    }

    public function syncVersions()
    {
        foreach ($this->getVersionsToSync() as $version) {
            $this->syncRef($version, 'tag');
        }
    }


    public function syncRef($ref, $type)
    {
        $this->runHook('git:syncer:start', [ $this, $ref, $type ]);
        $owner     = $this->setting('owner');
        $repo      = $this->setting('repository');
        $docPath   = $this->setting('sync.paths.docs');
        $indexPath = $this->setting('sync.paths.docs');

        $remote = $this->client($this->setting('remote'));
        $rfs    = $remote->getFilesystem($repo, $owner, $ref);

        $files = $rfs->allFiles($this->setting('sync.paths.docs'));

        $indexExists = $rfs->exists(Path::join($this->setting('sync.paths.docs'), 'index.md'));
        $menuExists  = $rfs->exists(Path::join($this->setting('sync.paths.docs'), 'menu.yml'));


        $a = 'b';
        if (! $indexExists || ! $menuExists) {
            return;
        }

        $destinationDir  = Path::join($this->project->getPath(), $ref);
        $menuContent     = $rfs->get($this->setting('sync.paths.menu')); //#base64_decode($menu[ 'content' ]);
        $menuArray       = Yaml::parse($menuContent);
        $unfilteredPages = [ ];
        $this->extractDocumentsFromMenu($menuArray[ 'menu' ], $unfilteredPages);


        $this->ensureDirectory($destinationDir);
        foreach ($rfs->allFiles($this->setting('sync.paths.docs')) as $path) {
            $localPath = Path::makeRelative($path, $this->setting('sync.paths.docs'));
            $localPath = Path::join($destinationDir, $localPath);
            $dir       = Path::getDirectory($localPath);
            $this->ensureDirectory($dir);
            $this->files->put($localPath, $rfs->get($path));
        }

        if ($type === 'branch') {
            $branch = $remote->getBranch($this->setting('repository'), $ref, $this->setting('owner'));
            $this->cache->forever(md5($this->project->getName() . $branch[ 'name' ]), $branch[ 'sha' ]);
        }
        $this->runHook('git:syncer:done', [ $this, $ref, $type ]);
    }

    public function getBranchesToSync()
    {
        $allowedBranches = $this->setting('sync.constraints.branches');
        if (count($allowedBranches) === 0) {
            return [ ];
        }

        $branchesToSync = [ ];
        $remote         = $this->client($this->setting('remote'));
        $repo           = $this->setting('repository');
        $owner          = $this->setting('owner');
        $branches       = $remote->getBranches($repo, $owner);

        foreach ($branches as $branch => $sha) {
            if (! in_array('*', $allowedBranches, true) and ! in_array($branch, $allowedBranches, true)) {
                continue;
            }
            $cacheKey        = md5($this->project->getName() . $branch);
            $cached          = $this->cache->get($cacheKey, false);
            $destinationPath = Path::join($this->project->getPath(), $branch);

            if ($cached !== $sha || $cached === false || ! $this->files->exists($destinationPath)) {
                $branchesToSync[] = $branch;
            }
        }

        return $branchesToSync;
    }

    public function getVersionsToSync()
    {
        $versionsToSync      = [ ];
        $remote              = $this->client($this->setting('remote'));
        $currentVersions     = $this->project->getRefs();
        $allowedVersionRange = new expression($this->setting('sync.sync.versions'));
        $tags                = $remote->getTags($this->setting('repository'), $this->setting('owner')); #$this->remote->repositories()->tags();

        foreach ($tags as $tag => $sha) {
            try {
                $version = new version($tag);
            }             catch (SemVerException $e) {
                continue;
            }
            if ($version->satisfies($allowedVersionRange) === false or in_array($version->getVersion(), $currentVersions, true)) {
                continue;
            }
            $versionsToSync[] = $version;
        }

        return $versionsToSync;
    }

    public function extractDocumentsFromMenu($menuArray, &$documents = [ ])
    {
        foreach ($menuArray as $key => $val) {
            if (is_string($key) && is_string($val)) {
                $documents[] = $val;
            } elseif (is_string($key) && $key === 'children' && is_array($val)) {
                $this->extractDocumentsFromMenu($val, $documents);
            } elseif (isset($val[ 'name' ])) {
                if (isset($val[ 'document' ])) {
                    $documents[] = $val[ 'document' ];
                }
                if (isset($val[ 'href' ])) {
                //$item['href'] = $this->resolveLink($val['href']);
                }
                if (isset($val[ 'icon' ])) {
                //$item['icon'] = $val['icon'];
                }
                if (isset($val[ 'children' ]) && is_array($val[ 'children' ])) {
                    $this->extractDocumentsFromMenu($val[ 'children' ], $documents);
                }
            }
        }
    }


    protected function setting($key, $default = null)
    {
        return array_get($this->project->config('git_hook_settings'), $key, $default);
    }

    /**
     * client method
     *
     * @param null $remote
     * @return \Sebwite\Git\Remotes\Remote
     */
    protected function client($remote = null)
    {
        $remote = isset($remote) ? $remote : $this->remote;
        $c      = [
            'credentials' => config('docit.hooks.git.credentials.' . $remote)
        ];

        return $this->git->connection($remote);
    }

    protected function ensureDirectory($path)
    {

        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path);
        }
    }
}
