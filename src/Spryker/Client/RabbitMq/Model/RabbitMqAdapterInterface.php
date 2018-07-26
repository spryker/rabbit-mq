<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model;

use Spryker\Client\Queue\Model\Adapter\AdapterInterface;
use Spryker\Client\RabbitMq\Model\Manager\ManagerInterface;

interface RabbitMqAdapterInterface extends AdapterInterface, ManagerInterface
{
}
