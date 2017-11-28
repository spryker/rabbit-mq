<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model;

use Spryker\Client\Queue\Model\Adapter\AdapterInterface;
use Spryker\Client\RabbitMq\Model\Manager\ManagerInterface;

interface RabbitMqAdapterInterface extends AdapterInterface, ManagerInterface
{
}
