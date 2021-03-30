<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

interface ConnectionInitializerInterface
{
    /**
     * @return void
     */
    public function setupConnection(): void;
}
