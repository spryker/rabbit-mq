<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Init\Executor;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Zed\RabbitMq\Business\Model\Connection\ConnectionReaderInterface;

class InitExecutor implements InitExecutorInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\Init\Command\InitCommandInterface[]
     */
    protected $commands;

    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\Connection\ConnectionReaderInterface
     */
    protected $connectionReader;

    /**
     * @param \Spryker\Zed\RabbitMq\Business\Model\Init\Command\InitCommandInterface[] $initCommands
     * @param \Spryker\Zed\RabbitMq\Business\Model\Connection\ConnectionReaderInterface $connectionReader
     */
    public function __construct(
        array $initCommands,
        ConnectionReaderInterface $connectionReader
    ) {
        $this->commands = $initCommands;
        $this->connectionReader = $connectionReader;
    }

    /**
     * @return bool
     */
    public function init(): bool
    {
        $connectionTransferCollection = $this->connectionReader
            ->getFilteredConnectionTransferCollection();

        foreach ($connectionTransferCollection as $connectionTransfer) {
            if (!$this->executeCommands($connectionTransfer)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $connectionTransferCollection
     *
     * @return bool
     */
    protected function executeCommands(QueueConnectionTransfer $connectionTransferCollection): bool
    {
        foreach ($this->commands as $command) {
            if (!$command->exec($connectionTransferCollection)) {
                return false;
            }
        }

        return true;
    }
}
