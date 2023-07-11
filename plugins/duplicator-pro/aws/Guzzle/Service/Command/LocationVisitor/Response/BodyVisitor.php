<?php

namespace DuplicatorPro\Guzzle\Service\Command\LocationVisitor\Response;

use DuplicatorPro\Guzzle\Service\Command\CommandInterface;
use DuplicatorPro\Guzzle\Http\Message\Response;
use DuplicatorPro\Guzzle\Service\Description\Parameter;

/**
 * Visitor used to add the body of a response to a particular key
 */
class BodyVisitor extends AbstractResponseVisitor
{
    public function visit(
        CommandInterface $command,
        Response $response,
        Parameter $param,
        &$value,
        $context =  null
    ) {
        $value[$param->getName()] = $param->filter($response->getBody());
    }
}
