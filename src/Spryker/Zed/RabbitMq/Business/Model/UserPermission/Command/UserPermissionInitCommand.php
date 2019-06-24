<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\UserPermission\Command;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Zed\RabbitMq\Business\Model\Init\Command\InitCommandInterface;
use Spryker\Zed\RabbitMq\Business\Model\UserPermission\Writer\UserPermissionWriterInterface;

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */
class UserPermissionInitCommand implements InitCommandInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\UserPermission\Writer\UserPermissionWriterInterface
     */
    protected $writer;

    /**
     * @param \Spryker\Zed\RabbitMq\Business\Model\UserPermission\Writer\UserPermissionWriterInterface $writer
     */
    public function __construct(UserPermissionWriterInterface $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return bool
     */
    public function exec(QueueConnectionTransfer $queueConnectionTransfer): bool
    {
        return $this->writer->add($queueConnectionTransfer);
    }
}
