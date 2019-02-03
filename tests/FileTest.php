<?php

use PHPUnit\Framework\TestCase;
use dbeurive\Squirrel\File;
use dbeurive\Squirrel\Timestamp;

class FileTest extends TestCase
{
    const KEY_TIMESTAMP = 0;
    const KEY_ID = 1;

    function testIsBasenameOk() {
        $this->assertTrue(File::isBasename('20000101000000-file.txt'));
        $this->assertFalse(File::isBasename('20000101000000.txt'));
        $this->assertFalse(File::isBasename('2000010100000-file.txt'));
    }

    function testGetTimestampFromBasenameOk() {
        $this->assertEquals('20000101000000', File::getTimestampFromBasename('20000101000000-file.txt'));
    }

    function testGetIdFromBasenameOk() {
        $this->assertEquals('file.txt', File::getIdFromBasename('20000101000000-file.txt'));
    }

    function testBasenameToFileOk() {
        $expected = array(
            '20000101000000-file.txt' => array(
                self::KEY_TIMESTAMP => '20000101000000',
                self::KEY_ID => 'file.txt'
            ),
            '20000101111111-other-file.txt' => array(
                self::KEY_TIMESTAMP => '20000101111111',
                self::KEY_ID => 'other-file.txt'
            )
        );

        /**
         * @var string $_basename
         * @var array $_data
         */
        foreach ($expected as $_basename => $_data) {
            $file = File::basenameToFile($_basename);
            $timestamp = $_data[self::KEY_TIMESTAMP];
            $id = $_data[self::KEY_ID];
            $this->assertEquals($timestamp, $file->getTimestamp());
            $this->assertEquals($id, $file->getId());
        }
    }

    function testBasenamesToFilesOk() {
        $expected = array(
            '20000101000000-file.txt' => array(
                self::KEY_TIMESTAMP => '20000101000000',
                self::KEY_ID => 'file.txt'
            ),
            '20000101111111-other-file.txt' => array(
                self::KEY_TIMESTAMP => '20000101111111',
                self::KEY_ID => 'other-file.txt'
            )
        );

        $basenames = array_keys($expected);
        $files = File::basenamesToFiles($basenames);

        for ($i=0; $i<count($files); $i++) {
            $file = $files[$i];
            $timestamp = $expected[$basenames[$i]][self::KEY_TIMESTAMP];
            $id = $expected[$basenames[$i]][self::KEY_ID];
            $this->assertEquals($timestamp, $file->getTimestamp());
            $this->assertEquals($id, $file->getId());
        }
    }

    function testGatherByTimestampOK() {

        // Build the expected result.

        $basenames = array(
            '20180101000000-file-1.txt',
            '20180101000001-file-1.txt',
            '20180101000002-file-1.txt',
            '20180101000000-file-2.txt',
            '20180101000001-file-2.txt',
            '20180101000002-file-2.txt',
            '20180101000000-file-3.txt',
            '20180101000001-file-3.txt',
            '20180101000002-file-3.txt',

            '20190101000000-file-1.txt',
            '20190101000001-file-1.txt',
            '20190101000002-file-1.txt',
            '20190101000000-file-2.txt',
            '20190101000001-file-2.txt',
            '20190101000002-file-2.txt',
            '20190101000000-file-3.txt',
            '20190101000001-file-3.txt',
            '20190101000002-file-3.txt',

            '20190201000000-file-1.txt',
            '20190201000001-file-1.txt',
            '20190201000002-file-1.txt',
            '20190201000000-file-2.txt',
            '20190201000001-file-2.txt',
            '20190201000002-file-2.txt',
            '20190201000000-file-3.txt',
            '20190201000001-file-3.txt',
            '20190201000002-file-3.txt'
        );

        // Build the input for the method "gatherByTimestamp()".

        $input = array();
        /** @var string $_basename */
        foreach ($basenames as $_basename) {
            $timestamp = substr($_basename, 0, File::TIMESTAMP_LENGTH);
            $id = substr($_basename, File::TIMESTAMP_LENGTH+1);
            $input[] = new File(new Timestamp($timestamp), $id);
        }

        // Build the expected result.

        $expected_res = array(
            '20180101000000' => array(),
            '20180101000001' => array(),
            '20180101000002' => array(),
            '20190101000000' => array(),
            '20190101000001' => array(),
            '20190101000002' => array(),
            '20190201000000' => array(),
            '20190201000001' => array(),
            '20190201000002' => array()
        );

        /**
         * @var string $_timestamp
         * @var array $_conf
         */
        foreach ($expected_res as $_timestamp => &$_conf) {
            for ($i=1; $i<=3; $i++) {
                $_conf[] = new File(new Timestamp($_timestamp), "file-${i}.txt");
            }
        }

        // Run the method.

        $expired = array();

        $r = File::gatherByTimestamp($input);
        $this->assertEquals($expected_res, $r);
        $this->assertCount(0, $expired);

        $keep = count($expected_res);
        $r = File::gatherByTimestamp($input, $keep, $expired);
        $this->assertEquals($expected_res, $r);
        $this->assertCount(0, $expired);

        $keep = count($expected_res) - 1;
        $r = File::gatherByTimestamp($input, $keep, $expired);
        $this->assertEquals($expected_res, $r);
        $this->assertCount(1, $expired);
        $this->assertEquals('20180101000000', $expired[0]);

        $keep = count($expected_res) - 2;
        $r = File::gatherByTimestamp($input, $keep, $expired);
        $this->assertEquals($expected_res, $r);
        $this->assertCount(2, $expired);
        $this->assertEquals(array('20180101000000', '20180101000001'), $expired);

        $keep = count($expected_res) - 3;
        $r = File::gatherByTimestamp($input, $keep, $expired);
        $this->assertEquals($expected_res, $r);
        $this->assertCount(3, $expired);
        $this->assertEquals(array('20180101000000', '20180101000001', '20180101000002'), $expired);
    }

}