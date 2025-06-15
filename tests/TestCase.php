<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'testing';

        return parent::createApplication();
    }
}
