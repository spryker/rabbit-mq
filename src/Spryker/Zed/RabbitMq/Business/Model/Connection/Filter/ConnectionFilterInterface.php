<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Connection\Filter;

interface ConnectionFilterInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $connectionConfigurations
     *
     * @return array
     */
    public function filter(array $connectionConfigurations): array;
}
