<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Codex\Hooks\Git\Http\Controllers;

use Codex\Core\Contracts\Factory as Codex;
use Codex\Hooks\Git\Contracts\Factory;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Sebwite\Support\Arr;

/**
 * This is the GithubController.
 *
 * @package        Codex\Hooks
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class WebhookController extends Controller
{
    /**
     * @var \Codex\Core\Factory
     */
    protected $codex;

    /**
     * @var \Codex\Hooks\Git\Contracts\Factory|\Codex\Hooks\Git\Factory
     */
    protected $factory;

    /**
     * WebhookController constructor.
     *
     * @param \Codex\Core\Contracts\Factory $codex
     * @param \Codex\Hooks\Git\Factory      $factory
     */
    public function __construct(Codex $codex, Factory $factory)
    {

        $this->codex   = $codex;
        $this->factory = $factory;
    }

    public function bitbucket()
    {
        $this->codex->log('info', 'codex.hooks.git.webhook.call', [ 'remote' => 'bitbucket' ]);

        $headers = Arr::only(request()->headers->all(), [
            'x-request-uuid',
            'x-event-key',
            'user-agent',
            'x-hook-uuid'
        ]);
        $data    = array_dot(request()->all());

        $valid =
            $headers[ 'user-agent' ][ 0 ] === 'Bitbucket-Webhooks/2.0' &&
            $headers[ 'x-event-key' ][ 0 ] === 'repo:push' &&
            isset($data[ 'repository.name' ]);

        if (! $valid) {
            return response('Invalid headzors', 500);
        }

        return $this->applyToGitProjects('bitbucket', function () use ($data) {

            return $data[ 'repository.name' ];
        });
    }

    /**
     * webhook
     *
     * @param $type
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function github()
    {
        $this->codex->log('info', 'codex.hooks.git.webhook.call', [ 'remote' => 'github' ]);

        $headers = [
            'delivery'   => request()->header('x-github-delivery'),
            'event'      => request()->header('x-github-event'),
            'user-agent' => request()->header('user-agent'),
            'signature'  => request()->header('x-hub-signature')
        ];
        $data    = array_dot(request()->all());

        return $this->applyToGitProjects('github', function ($project) use ($data, $headers) {

            $hash = hash_hmac('sha1', file_get_contents("php://input"), $project->config('git_hook_settings.sync.webhook_secret'));

            if ($headers[ 'signature' ] === "sha1=$hash") {
                return strtolower($data[ 'repository.full_name' ]);
            } else {
                return response('Invalid hash', 403);
            }
        });
    }

    protected function applyToGitProjects($remote, \Closure $closure)
    {

        foreach ($this->codex->getProjects() as $project) {
            if ($project->config('enable_git_hook', false) === false || $project->config('git_hook_settings.webhook.enabled', false) === false) {
                continue;
            }

            $config      = $project->config('git_hook_settings');
            $projectRepo = $project->config('git_hook_settings.owner') . '/' . $project->config('git_hook_settings.repository');

            $hook = call_user_func_array($closure, [ $project ]);

            if ($hook instanceof Response) {
                return $hook;
            }

            if ($hook[ 'repository' ] !== $projectRepo) {
                continue;
            }

            $this->factory->createSyncJob($project);

            $this->codex->log('info', 'codex.hooks.git.webhook.call', [ 'remote' => $remote ]);

            return response('', 200);
        }
    }
}
