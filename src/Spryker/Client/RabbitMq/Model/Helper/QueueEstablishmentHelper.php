<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Helper;

use Generated\Shared\Transfer\RabbitMqOptionTransfer;
use PhpAmqpLib\Channel\AMQPChannel;

class QueueEstablishmentHelper implements QueueEstablishmentHelperInterface
{
    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOptionTransfer
     *
     * @return array
     */
    public function createQueue(AMQPChannel $channel, RabbitMqOptionTransfer $queueOptionTransfer)
    {
        $queueParams = $this->convertTransferToArray($queueOptionTransfer);

        $channel
            ->queue_declare(
                $queueParams['queue_name'],
                $queueParams['passive'],
                $queueParams['durable'],
                $queueParams['exclusive'],
                $queueParams['auto_delete']
            );

        return $queueParams;
    }

    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOptionTransfer
     *
     * @return void
     */
    public function createExchange(AMQPChannel $channel, RabbitMqOptionTransfer $queueOptionTransfer)
    {
        $exchangeParams = $this->convertTransferToArray($queueOptionTransfer);

        $channel
            ->exchange_declare(
                $exchangeParams['queue_name'],
                $exchangeParams['type'],
                $exchangeParams['passive'],
                $exchangeParams['durable'],
                $exchangeParams['auto_delete']
            );
    }

    /**
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOptionTransfer
     *
     * @return array
     */
    protected function convertTransferToArray(RabbitMqOptionTransfer $queueOptionTransfer)
    {
        return $queueOptionTransfer->toArray();
    }
}
