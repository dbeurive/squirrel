<?php

use PHPUnit\Framework\TestCase;
use dbeurive\Squirrel\Timestamp;

class TimestampTest extends TestCase
{
    function testCmp() {

        $tests = array(
            array('20190202222222', '20180202222222'),
            array('20190302222222', '20190202222222'),
            array('20190203222222', '20190202222222'),
            array('20190202232222', '20190202222222'),
            array('20190202222322', '20190202222222'),
            array('20190202222223', '20190202222222'),
        );

        $a = new Timestamp('20190101000000');
        $b = new Timestamp('20190101000000');
        $this->assertEquals(0, Timestamp::cmp($a, $b));

        /** @var array $_test */
        foreach ($tests as $_test) {
            $a = new Timestamp($_test[0]);
            $b = new Timestamp($_test[1]);
            $this->assertEquals(+1, Timestamp::cmp($a, $b));
            $this->assertEquals(-1, Timestamp::cmp($b, $a));
        }
    }
}