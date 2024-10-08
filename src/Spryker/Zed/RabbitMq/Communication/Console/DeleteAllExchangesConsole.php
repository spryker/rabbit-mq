<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Communication\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\RabbitMq\Business\RabbitMqFacadeInterface getFacade()
 */
class DeleteAllExchangesConsole extends Console
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'queue:exchanges:delete-all';

    /**
     * @var string
     */
    public const DESCRIPTION = 'This command will delete all exchanges.';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription(static::DESCRIPTION);

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->info('Delete all exchanges');

        if ($this->getFacade()->deleteAllExchanges($this->getMessenger())) {
            return static::CODE_SUCCESS;
        }

        return static::CODE_ERROR;
    }
}
