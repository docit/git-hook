<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Docit\Hooks\Git\Http\Controllers;

use Docit\Core\Contracts\Factory as Docit;
use Docit\Hooks\Git\Contracts\Factory;
use Docit\Support\Arr;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * This is the GithubController.
 *
 * @package        Docit\Hooks
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class WebhookController extends Controller
{
    /**
     * @var \Docit\Core\Factory
     */
    protected $docit;

    /**
     * @var \Docit\Hooks\Git\Contracts\Factory|\Docit\Hooks\Git\Factory
     */
    protected $factory;

    /**
     * WebhookController constructor.
     *
     * @param \Docit\Core\Contracts\Factory $docit
     * @param \Docit\Hooks\Git\Factory      $factory
     */
    public function __construct(Docit $docit, Factory $factory)
    {

        $this->docit   = $docit;
        $this->factory = $factory;
    }

    public function bitbucket()
    {
        $this->docit->log('info', 'docit.hooks.git.webhook.call', [ 'remote' => 'bitbucket' ]);

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
        $this->docit->log('info', 'docit.hooks.git.webhook.call', [ 'remote' => 'github' ]);

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

        foreach ($this->docit->getProjects() as $project) {
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

            $this->docit->log('info', 'docit.hooks.git.webhook.call', [ 'remote' => $remote ]);

            return response('', 200);
        }
    }
}
