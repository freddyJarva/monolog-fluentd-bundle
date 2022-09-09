<?php

/*
 * This file is part of "vt/monolog-fluentd-bundle".
 *
 * (c) VT S.p.A. <oss@vt.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace VT\MonologFluentdBundle\Tests\Monolog\Handler;

use Fluent\Logger\Entity;
use Fluent\Logger\FluentLogger;
use Monolog\Logger;
use VT\MonologFluentdBundle\Monolog\Handler\FluentdHandler;
use PHPUnit\Framework\TestCase;

class FluentdHandlerTest extends TestCase
{
    public function testHandle()
    {
        $level = Logger::DEBUG;
        $record = $this->getRecord($level, 'Test message', ['x' => 1]);

        $spyLogger = $this->createMock(FluentLogger::class);
        $spyLogger
            ->expects($this->once())
            ->method('post2')
            ->with(
                $this->createTestEntity($record, $level)
            )
            ->willReturn(true)
        ;

        $handler = new FluentdHandler($spyLogger, $level, true);
        $handler->setTagFormat('XXX test XXX.{{level_name}}');
        $handler->setExceptions(false);

        $handler->handle($record);
    }

    /**
     * @param int    $level
     * @param string $message
     * @param array  $context
     *
     * @return array
     */
    private function getRecord($level = Logger::WARNING, $message = 'test', array $context = [])
    {
        return [
            'channel' => 'test',
            'message' => $message,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'datetime' => \DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true))),
            'context' => $context,
            'extra' => [],
        ];
    }

    /**
     * @param $record
     * @param $level
     *
     * @return Entity
     */
    private function createTestEntity($record, $level)
    {
        $record['level'] = FluentdHandler::toPsr3Level($level);

        return new Entity(
            'XXX test XXX.'.Logger::getLevelName($level),
            $record,
            $record['datetime']->getTimestamp()
        );
    }
}
