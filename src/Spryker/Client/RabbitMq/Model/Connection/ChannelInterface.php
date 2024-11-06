<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use PhpAmqpLib\Channel\AMQPChannel;

interface ChannelInterface
{
    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel(): AMQPChannel;

    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     *
     * @return $this
     */
    public function setChannel(AMQPChannel $channel);

    /**
     * @param array<string> $stores
     *
     * @return $this
     */
    public function setStores(array $stores);

    /**
     * @return array<string>
     */
    public function getStores(): array;
}
