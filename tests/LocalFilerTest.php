<?php

use PHPUnit\Framework\TestCase;
use dbeurive\Squirrel\FileScanner;

class LocalFilerTest extends TestCase
{
    function testGetFilesOk1() {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'LocalFilerTest';
        $filer = new FileScanner($dir, 'file.txt');
        $files = $filer->getFiles();

        $this->assertCount(3, $files);

        $expected1 = realpath(sprintf('%s%s%s', $dir, DIRECTORY_SEPARATOR, '20000101010101-file.txt'));
        $expected2 = realpath(sprintf('%s%s%s', $dir, DIRECTORY_SEPARATOR, '20000101010102-file.txt'));
        $expected3 = realpath(sprintf('%s%s%s', $dir, DIRECTORY_SEPARATOR, '20000101010103-file.txt'));

        $this->assertContains($expected1, $files);
        $this->assertContains($expected2, $files);
        $this->assertContains($expected3, $files);
    }
}