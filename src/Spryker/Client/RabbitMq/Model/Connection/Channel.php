<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use PhpAmqpLib\Channel\AMQPChannel;

class Channel implements ChannelInterface
{
    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected AMQPChannel $channel;

    /**
     * @var array<string>
     */
    protected array $stores = [];

    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     *
     * @return $this
     */
    public function setChannel(AMQPChannel $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    /**
     * @param array<string> $stores
     *
     * @return $this
     */
    public function setStores(array $stores)
    {
        $this->stores = $stores;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getStores(): array
    {
        return $this->stores;
    }
}
