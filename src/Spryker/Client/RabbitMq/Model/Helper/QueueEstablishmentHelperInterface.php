<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Helper;

use Generated\Shared\Transfer\RabbitMqOptionTransfer;
use PhpAmqpLib\Channel\AMQPChannel;

interface QueueEstablishmentHelperInterface
{
    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOptionTransfer
     *
     * @return array
     */
    public function createQueue(AMQPChannel $channel, RabbitMqOptionTransfer $queueOptionTransfer);

    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOptionTransfer
     *
     * @return \Generated\Shared\Transfer\RabbitMqOptionTransfer
     */
    public function createExchange(AMQPChannel $channel, RabbitMqOptionTransfer $queueOptionTransfer);
}
