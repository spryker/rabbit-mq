<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Dependency\Guzzle;

interface RabbitMqToGuzzleInterface
{
    /**
     * @param string $uri
     * @param array<string, mixed> $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($uri, array $options = []);

    /**
     * @param string $uri
     * @param array<string, mixed> $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function put($uri, array $options = []);
}
