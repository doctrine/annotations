<?php

namespace Doctrine\AnnotationsTests;

use Doctrine\Annotations\Configuration;

/**
 * Base testcase class.
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Annotations\Configuration
     */
    protected $config;

    protected function setUp()
    {
        $this->config = new Configuration();
    }
}