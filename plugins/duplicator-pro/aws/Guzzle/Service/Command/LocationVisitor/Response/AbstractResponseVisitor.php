<?php

namespace DuplicatorPro\Guzzle\Service\Command\LocationVisitor\Response;

use DuplicatorPro\Guzzle\Service\Command\CommandInterface;
use DuplicatorPro\Guzzle\Http\Message\Response;
use DuplicatorPro\Guzzle\Service\Description\Parameter;

/**
 * {@inheritdoc}
 * @codeCoverageIgnore
 */
abstract class AbstractResponseVisitor implements ResponseVisitorInterface
{
    public function before(CommandInterface $command, array &$result) {}

    public function after(CommandInterface $command) {}

    public function visit(
        CommandInterface $command,
        Response $response,
        Parameter $param,
        &$value,
        $context =  null
    ) {}
}
