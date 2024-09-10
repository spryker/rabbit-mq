<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter;

interface QueueConnectionTransferFilterInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\QueueConnectionTransfer> $queueConnectionTransfers
     * @param string|null $localeCode
     *
     * @return array<\Generated\Shared\Transfer\QueueConnectionTransfer>
     */
    public function filterByLocaleCode(array $queueConnectionTransfers, ?string $localeCode): array;
}
