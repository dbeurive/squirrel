<?php

use PHPUnit\Framework\TestCase;
use dbeurive\Squirrel\Configuration;
use dbeurive\Squirrel\Destination;
use dbeurive\Squirrel\Task;


class ConfigurationTest extends TestCase
{
    public function testConstructOk1() {
        $configuration_file_path = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'squirrel1.json';
        $conf = new Configuration($configuration_file_path, __DIR__);

        $destinations = $conf->getDestinations();
        /** @var Destination $d1 */
        $d1 = $destinations['server1'];
        /** @var Destination $d2 */
        $d2 = $destinations['server2'];

        $tasks = $conf->getTasks();
        /** @var Task $t1 */
        $t1 = $tasks['task1'];
        /** @var Task $t2 */
        $t2 = $tasks['task2'];

        $log = $conf->getLog();


        $this->assertEquals($conf->getDestinationsNames(), array('server1', 'server2'));
        $this->assertEquals(array_keys($destinations), array('server1', 'server2'));

        $this->assertEquals($conf->getTasksNames(), array('task1', 'task2'));
        $this->assertEquals(array_keys($tasks), array('task1', 'task2'));

        $this->assertEquals($conf->getDestinationHost('server1'), 'host1');
        $this->assertEquals($conf->getDestinationHost('server2'), 'host2');
        $this->assertEquals($d1->getHost(), 'host1');
        $this->assertEquals($d2->getHost(), 'host2');

        $this->assertEquals($conf->getDestinationUser('server1'), 'user1');
        $this->assertEquals($conf->getDestinationUser('server2'), 'user2');
        $this->assertEquals($d1->getUser(), 'user1');
        $this->assertEquals($d2->getUser(), 'user2');

        $this->assertEquals($conf->getDestinationPassword('server1'), 'password1');
        $this->assertEquals($conf->getDestinationPassword('server2'), 'password2');
        $this->assertEquals($d1->getPassword(), 'password1');
        $this->assertEquals($d2->getPassword(), 'password2');

        $this->assertEquals($conf->getDestinationPort('server1'), 21);
        $this->assertEquals($conf->getDestinationPort('server2'), 210);
        $this->assertEquals($d1->getPort(), 21);
        $this->assertEquals($d2->getPort(), 210);

        $this->assertEquals($conf->getDestinationPath('server1'), '/files/backups1');
        $this->assertEquals($conf->getDestinationPath('server2'), '/files/backups2');
        $this->assertEquals($d1->getPath(), '/files/backups1');
        $this->assertEquals($d2->getPath(), '/files/backups2');

        $this->assertEquals($conf->getDestinationTimeout('server1'), 60);
        $this->assertEquals($conf->getDestinationTimeout('server2'), 600);
        $this->assertEquals($d1->getTimeout(), 60);
        $this->assertEquals($d2->getTimeout(), 600);

        $this->assertEquals($conf->getDestinationRetry('server1'), 3);
        $this->assertEquals($conf->getDestinationRetry('server2'), 30);
        $this->assertEquals($d1->getRetry(), 3);
        $this->assertEquals($d2->getRetry(), 30);

        $this->assertEquals($conf->getDestinationSleep('server1'), 1);
        $this->assertEquals($conf->getDestinationSleep('server2'), 10);
        $this->assertEquals($d1->getSleep(), 1);
        $this->assertEquals($d2->getSleep(), 10);

        // ------------------------------------------------

        $this->assertEquals($conf->getTaskLocalInputDirectory('task1'), '/home/task1/backups');
        $this->assertEquals($conf->getTaskLocalInputDirectory('task2'),  __DIR__ . DIRECTORY_SEPARATOR . 'backups');
        $this->assertEquals($t1->getLocalInputDirectory(), '/home/task1/backups');
        $this->assertEquals($t2->getLocalInputDirectory(),  __DIR__ . DIRECTORY_SEPARATOR . 'backups');

        $this->assertEquals($conf->getTaskLocalDoneDirectory('task1'), '/home/task1/backups-done');
        $this->assertEquals($conf->getTaskLocalDoneDirectory('task2'), __DIR__ . DIRECTORY_SEPARATOR . 'done');
        $this->assertEquals($t1->getLocalDoneDirectory(), '/home/task1/backups-done');
        $this->assertEquals($t2->getLocalDoneDirectory(), __DIR__ . DIRECTORY_SEPARATOR . 'done');

        $this->assertEquals($conf->getTaskFileId('task1'), 'mask1');
        $this->assertEquals($conf->getTaskFileId('task2'), 'mask2');
        $this->assertEquals($t1->getFileId(), 'mask1');
        $this->assertEquals($t2->getFileId(), 'mask2');

        $this->assertEquals($conf->getTaskKeepCount('task1'), 10);
        $this->assertEquals($conf->getTaskKeepCount('task2'), 100);
        $this->assertEquals($t1->getKeepCount(), 10);
        $this->assertEquals($t2->getKeepCount(), 100);

        $this->assertEquals($conf->getTaskDestinations('task1'), array('server1', 'server2'));
        $this->assertEquals($conf->getTaskDestinations('task2'), array('server1'));
        $this->assertEquals(array_keys($t1->getDestinations()), array('server1', 'server2'));
        $this->assertEquals(array_keys($t2->getDestinations()), array('server1'));

        $this->assertEquals($conf->getTaskOnError('task1'), 'on-error1');
        $this->assertEquals($conf->getTaskOnError('task2'), 'on-error2');
        $this->assertEquals($t1->getOnError(), 'on-error1');
        $this->assertEquals($t2->getOnError(), 'on-error2');

        $this->assertEquals($conf->getTaskOnSuccess('task1'), 'on-success1');
        $this->assertEquals($conf->getTaskOnSuccess('task2'), 'on-success2');
        $this->assertEquals($t1->getOnSuccess(), 'on-success1');
        $this->assertEquals($t2->getOnSuccess(), 'on-success2');

        // ------------------------------------------------

        $this->assertEquals($log->getDirectory(), '/tmp');
        $this->assertEquals($log->getLevel(), \dbeurive\Log\Logger::LEVEL_INFO);
        $this->assertEquals($log->getName(), "squirrel.log");
        $this->assertTrue($log->fileTimestamped());
    }

    public function testConstructOk2() {
        $configuration_file_path = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'squirrel2.json';
        $conf = new Configuration($configuration_file_path, __DIR__);

        $destinations = $conf->getDestinations();
        /** @var Destination $d1 */
        $d1 = $destinations['server1'];
        /** @var Destination $d2 */
        $d2 = $destinations['server2'];

        $tasks = $conf->getTasks();
        /** @var Task $t1 */
        $t1 = $tasks['task1'];
        /** @var Task $t2 */
        $t2 = $tasks['task2'];

        $log = $conf->getLog();

        $this->assertEquals($conf->getDestinationPort('server1'), Configuration::VALUE_DEFAULT_PORT);
        $this->assertEquals($conf->getDestinationPort('server2'), Configuration::VALUE_DEFAULT_PORT);
        $this->assertEquals($d1->getPort(), Configuration::VALUE_DEFAULT_PORT);
        $this->assertEquals($d2->getPort(), Configuration::VALUE_DEFAULT_PORT);

        $this->assertEquals($conf->getDestinationTimeout('server1'), Configuration::VALUE_DEFAULT_TIMEOUT);
        $this->assertEquals($conf->getDestinationTimeout('server2'), Configuration::VALUE_DEFAULT_TIMEOUT);
        $this->assertEquals($d1->getTimeout(), Configuration::VALUE_DEFAULT_TIMEOUT);
        $this->assertEquals($d2->getTimeout(), Configuration::VALUE_DEFAULT_TIMEOUT);

        $this->assertEquals($conf->getDestinationRetry('server1'), Configuration::VALUE_DEFAULT_RETRY);
        $this->assertEquals($conf->getDestinationRetry('server2'), Configuration::VALUE_DEFAULT_RETRY);
        $this->assertEquals($d1->getRetry(), Configuration::VALUE_DEFAULT_RETRY);
        $this->assertEquals($d2->getRetry(), Configuration::VALUE_DEFAULT_RETRY);

        $this->assertEquals($conf->getDestinationSleep('server1'), Configuration::VALUE_DEFAULT_SLEEP);
        $this->assertEquals($conf->getDestinationSleep('server2'), Configuration::VALUE_DEFAULT_SLEEP);
        $this->assertEquals($d1->getSleep(), Configuration::VALUE_DEFAULT_SLEEP);
        $this->assertEquals($d2->getSleep(), Configuration::VALUE_DEFAULT_SLEEP);

        // ------------------------------------------------

        $this->assertEquals($conf->getTaskOnError('task1'), Configuration::VALUE_DEFAULT_ON_ERROR);
        $this->assertEquals($conf->getTaskOnError('task2'), Configuration::VALUE_DEFAULT_ON_ERROR);
        $this->assertEquals($t1->getOnError(), Configuration::VALUE_DEFAULT_ON_ERROR);
        $this->assertEquals($t2->getOnError(), Configuration::VALUE_DEFAULT_ON_ERROR);

        $this->assertEquals($conf->getTaskOnSuccess('task1'), Configuration::VALUE_DEFAULT_ON_SUCCESS);
        $this->assertEquals($conf->getTaskOnSuccess('task2'), Configuration::VALUE_DEFAULT_ON_SUCCESS);
        $this->assertEquals($t1->getOnSuccess(), Configuration::VALUE_DEFAULT_ON_SUCCESS);
        $this->assertEquals($t2->getOnSuccess(), Configuration::VALUE_DEFAULT_ON_SUCCESS);

        $this->assertEquals($log->getLevel(), \dbeurive\Log\Logger::LEVEL_INFO);
        $this->assertFalse($log->fileTimestamped());
    }
}