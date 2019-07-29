<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionConfigFilter;

interface ConnectionConfigFilterInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $connectionsConfig
     * @param string|null $locale
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function filterByLocale(array $connectionsConfig, ?string $locale): array;
}
