<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Connection;

use Generated\Shared\Transfer\QueueConnectionTransfer;

class ConnectionReader implements ConnectionReaderInterface
{
    /**
     * @var array
     */
    protected $connectionConfigurations;

    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\Connection\Filter\ConnectionFilterInterface[]
     */
    protected $connectionFilters = [];

    /**
     * @param array $connectionFilters
     * @param array $connectionConfigurations
     */
    public function __construct(array $connectionFilters, array $connectionConfigurations)
    {
        $this->connectionFilters = $connectionFilters;
        $this->connectionConfigurations = $connectionConfigurations;
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function getFilteredConnectionTransferCollection(): array
    {
        $connectionConfigurations = $this->getConnectionTransferCollection();

        if ($this->connectionFilters === []) {
            return $connectionConfigurations;
        }

        foreach ($this->connectionFilters as $connectionFilter) {
            $connectionConfigurations = $connectionFilter->filter($connectionConfigurations);
        }

        return $connectionConfigurations;
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function getConnectionTransferCollection(): array
    {
        $connectionTransferCollection = [];

        foreach ($this->connectionConfigurations as $connectionConfig) {
            $connectionTransfer = new QueueConnectionTransfer();
            $connectionTransfer = $connectionTransfer->fromArray(
                $connectionConfig,
                true
            );

            $connectionTransferCollection[] = $connectionTransfer;
        }

        return $connectionTransferCollection;
    }
}
