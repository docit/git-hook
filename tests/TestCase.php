<?php

namespace Docit\Tests\GitHook;

use Sebwite\Testbench\AbstractTestCase;

abstract class TestCase extends AbstractTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getServiceProviderClass($app)
    {
        return \Docit\Hooks\Git\HookServiceProvider::class;
    }
}
