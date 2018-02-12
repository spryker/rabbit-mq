<?php
/**
 * Created by PhpStorm.
 * User: karolygerner
 * Date: 12.February.2018
 * Time: 11:41
 */

namespace Spryker\Shared\RabbitMq;


interface RabbitMqConfigInterface
{
    /**
     * Value is used to indicate that the default pool should be used for the publish of the corresponding message.
     */
    const QUEUE_POOL_NAME_DEFAULT = 'QUEUE_POOL_NAME_DEFAULT';
}
