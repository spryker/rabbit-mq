<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\User\Command;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Zed\RabbitMq\Business\Model\Init\Command\InitCommandInterface;
use Spryker\Zed\RabbitMq\Business\Model\User\Reader\UserReaderInterface;
use Spryker\Zed\RabbitMq\Business\Model\User\Writer\UserWriterInterface;

class UserInitCommand implements InitCommandInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\User\Reader\UserReaderInterface
     */
    protected $reader;

    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\User\Writer\UserWriterInterface
     */
    protected $writer;

    /**
     * @param \Spryker\Zed\RabbitMq\Business\Model\User\Reader\UserReaderInterface $reader
     * @param \Spryker\Zed\RabbitMq\Business\Model\User\Writer\UserWriterInterface $writer
     */
    public function __construct(UserReaderInterface $reader, UserWriterInterface $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return bool
     */
    public function exec(QueueConnectionTransfer $queueConnectionTransfer): bool
    {
        if ($this->reader->has($queueConnectionTransfer)) {
            return true;
        }

        return $this->writer->add($queueConnectionTransfer);
    }
}
