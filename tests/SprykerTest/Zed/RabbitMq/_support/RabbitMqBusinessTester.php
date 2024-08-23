<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\RabbitMq;

use Codeception\Actor;
use Generated\Shared\Transfer\QueueSendMessageTransfer;
use Spryker\Zed\Event\Communication\Plugin\Queue\EventQueueMessageProcessorPlugin;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(\PHPMD\PHPMD)
 */
class RabbitMqBusinessTester extends Actor
{
    use _generated\RabbitMqBusinessTesterActions;

    /**
     * @var string
     */
    protected const STORE_NAME_DE = 'DE';

    /**
     * @param array<string> $queueNames
     *
     * @return array<\Spryker\Zed\Queue\Dependency\Plugin\QueueMessageProcessorPluginInterface>
     */
    public function getMessageProcessorPlugins(array $queueNames): array
    {
        $plugins = [];

        foreach ($queueNames as $queueName) {
            $plugins[$queueName] = new EventQueueMessageProcessorPlugin();
        }

        return $plugins;
    }

    /**
     * @return \Generated\Shared\Transfer\QueueSendMessageTransfer
     */
    public function buildSendMessageTransfer(): QueueSendMessageTransfer
    {
        $queueSendMessageTransfer = new QueueSendMessageTransfer();
        $queueSendMessageTransfer->setBody(json_encode([
            'write' => [
                'key' => 'testKey',
                'value' => 'testValue',
                'resource' => 'testResource',
                'params' => [],
            ],
        ]));
        $queueSendMessageTransfer->setStoreName(static::STORE_NAME_DE);

        return $queueSendMessageTransfer;
    }
}
