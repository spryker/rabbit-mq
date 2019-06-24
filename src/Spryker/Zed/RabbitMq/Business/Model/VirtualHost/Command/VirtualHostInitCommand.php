<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Command;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Zed\RabbitMq\Business\Model\Init\Command\InitCommandInterface;
use Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Reader\VirtualHostReaderInterface;
use Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Writer\VirtualHostWriterInterface;

class VirtualHostInitCommand implements InitCommandInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Reader\VirtualHostReaderInterface
     */
    protected $reader;

    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Writer\VirtualHostWriterInterface
     */
    protected $writer;

    /**
     * @param \Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Reader\VirtualHostReaderInterface $reader
     * @param \Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Writer\VirtualHostWriterInterface $writer
     */
    public function __construct(VirtualHostReaderInterface $reader, VirtualHostWriterInterface $writer)
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
