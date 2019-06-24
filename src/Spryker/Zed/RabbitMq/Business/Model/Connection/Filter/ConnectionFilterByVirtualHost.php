<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Connection\Filter;

class ConnectionFilterByVirtualHost implements ConnectionFilterInterface
{
    protected const KEY_VIRTUAL_HOST = 'virtualHost';

    /**
     * @var string
     */
    protected $virtualHost;

    /**
     * @param string $virtualHost
     */
    public function __construct(string $virtualHost)
    {
        $this->virtualHost = $virtualHost;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $connectionConfigurations
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function filter(array $connectionConfigurations): array
    {
        return array_filter($connectionConfigurations, function ($connectionConfig) {
            /** @var \Generated\Shared\Transfer\QueueConnectionTransfer $connectionConfig */
            return $connectionConfig->getVirtualHost() === $this->virtualHost;
        });
    }
}
