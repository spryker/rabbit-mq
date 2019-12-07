<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Communication\Plugin\HealthCheck;

use Generated\Shared\Transfer\HealthCheckServiceResponseTransfer;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Shared\HealthCheckExtension\Dependency\Plugin\HealthCheckPluginInterface;

/**
 * @method \Spryker\Zed\RabbitMq\Business\RabbitMqBusinessFactory getFactory()
 */
class BrokerHealthCheckPlugin extends AbstractPlugin implements HealthCheckPluginInterface
{
    protected const BROKER_HEALTH_CHECK_SERVICE_NAME = 'broker';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getName(): string
    {
        return static::BROKER_HEALTH_CHECK_SERVICE_NAME;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\HealthCheckServiceResponseTransfer
     */
    public function check(): HealthCheckServiceResponseTransfer
    {
        return $this->getFactory()->createHealthChecker()->executeHealthCheck();
    }
}
