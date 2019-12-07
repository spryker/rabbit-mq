<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\HealthCheck;

use \Exception;
use Generated\Shared\Transfer\HealthCheckServiceResponseTransfer;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface;

class HealthCheck implements HealthCheckInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface
     */
    protected $queueAdapter;

    /**
     * @param \Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface $queueAdapter
     */
    public function __construct(RabbitMqAdapterInterface $queueAdapter)
    {
        $this->queueAdapter = $queueAdapter;
    }

    /**
     * @return \Generated\Shared\Transfer\HealthCheckServiceResponseTransfer
     */
    public function executeHealthCheck(): HealthCheckServiceResponseTransfer
    {
        $healthCheckServiceResponseTransfer = (new HealthCheckServiceResponseTransfer())
            ->setStatus(true);

        try {
            $this->queueAdapter->getConnectionStatus();
        } catch (Exception $e) {
            return $healthCheckServiceResponseTransfer
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }

        return $healthCheckServiceResponseTransfer
            ->setStatus(true);
    }
}
