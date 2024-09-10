<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Helper;

use Generated\Shared\Transfer\RabbitMqOptionTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;

class QueueEstablishmentHelper implements QueueEstablishmentHelperInterface
{
    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOptionTransfer
     *
     * @return array<mixed>
     */
    public function createQueue(AMQPChannel $channel, RabbitMqOptionTransfer $queueOptionTransfer): array
    {
        $queueParams = $this->convertTransferToArray($queueOptionTransfer);

        $channel
            ->queue_declare(
                $queueParams['queue_name'],
                $queueParams['passive'],
                $queueParams['durable'],
                $queueParams['exclusive'],
                $queueParams['auto_delete'],
                $queueParams['no_wait'],
                new AMQPTable($queueParams['arguments']),
            );

        return $queueParams;
    }

    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOptionTransfer
     *
     * @return void
     */
    public function createExchange(AMQPChannel $channel, RabbitMqOptionTransfer $queueOptionTransfer): void
    {
        $exchangeParams = $this->convertTransferToArray($queueOptionTransfer);

        $channel
            ->exchange_declare(
                $exchangeParams['queue_name'],
                $exchangeParams['type'],
                $exchangeParams['passive'],
                $exchangeParams['durable'],
                $exchangeParams['auto_delete'],
            );
    }

    /**
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOptionTransfer
     *
     * @return array<mixed>
     */
    protected function convertTransferToArray(RabbitMqOptionTransfer $queueOptionTransfer): array
    {
        $queueParams = $queueOptionTransfer->toArray();
        $queueParams['no_wait'] = filter_var($queueParams['no_wait'], FILTER_VALIDATE_BOOLEAN); // Just a precaution for BC

        return $queueParams;
    }
}
