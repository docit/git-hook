<?php

namespace Codex\Tests\GitHook;

use Sebwite\Testbench\AbstractTestCase;

abstract class TestCase extends AbstractTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getServiceProviderClass($app)
    {
        return \Codex\Hooks\Git\HookServiceProvider::class;
    }
}
