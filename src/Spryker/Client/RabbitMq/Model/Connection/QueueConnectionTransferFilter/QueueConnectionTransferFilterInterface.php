<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter;

interface QueueConnectionTransferFilterInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $queueConnectionTransfers
     * @param string|null $localeCode
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function filterByLocaleCode(array $queueConnectionTransfers, ?string $localeCode): array;
}
