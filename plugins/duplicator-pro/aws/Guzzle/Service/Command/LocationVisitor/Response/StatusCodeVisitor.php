<?php

namespace DuplicatorPro\Guzzle\Service\Command\LocationVisitor\Response;

use DuplicatorPro\Guzzle\Http\Message\Response;
use DuplicatorPro\Guzzle\Service\Description\Parameter;
use DuplicatorPro\Guzzle\Service\Command\CommandInterface;

/**
 * Location visitor used to add the status code of a response to a key in the result
 */
class StatusCodeVisitor extends AbstractResponseVisitor
{
    public function visit(
        CommandInterface $command,
        Response $response,
        Parameter $param,
        &$value,
        $context =  null
    ) {
        $value[$param->getName()] = $response->getStatusCode();
    }
}
