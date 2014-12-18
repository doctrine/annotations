<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\FileCacheReader;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileCacheReaderTest extends AbstractReaderTest
{
    private $cacheDir;

    protected function getReader()
    {
        $this->cacheDir = sys_get_temp_dir() . "/annotations_". uniqid();
        @mkdir($this->cacheDir);
        return new FileCacheReader(new AnnotationReader(), $this->cacheDir);
    }

    public function tearDown()
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cacheDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach($files as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }
        rmdir($this->cacheDir);
    }

    /**
     * @group DCOM-81
     */
    public function testAttemptToCreateAnnotationCacheDir()
    {
        $this->cacheDir = sys_get_temp_dir() . "/not_existed_dir_". uniqid();

        $this->assertFalse(is_dir($this->cacheDir));

        new FileCacheReader(new AnnotationReader(), $this->cacheDir);

        $this->assertTrue(is_dir($this->cacheDir));
    }
}
