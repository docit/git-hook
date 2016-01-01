<?php


Route::group([ 'as' => 'codex.hooks.git.webhook.', 'prefix' => 'git-hook-webhook' ], function () {

    Route::any('github', [ 'as' => 'github', 'uses' => 'WebhookController@github' ]);
    Route::any('bitbucket', [ 'as' => 'bitbucket', 'uses' => 'WebhookController@bitbucket' ]);
});
