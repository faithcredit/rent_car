<?php

namespace DuplicatorPro\Guzzle\Service\Command\LocationVisitor\Request;

use DuplicatorPro\Guzzle\Http\Message\RequestInterface;
use DuplicatorPro\Guzzle\Http\Message\PostFileInterface;
use DuplicatorPro\Guzzle\Service\Command\CommandInterface;
use DuplicatorPro\Guzzle\Service\Description\Parameter;

/**
 * Visitor used to apply a parameter to a POST file
 */
class PostFileVisitor extends AbstractRequestVisitor
{
    public function visit(CommandInterface $command, RequestInterface $request, Parameter $param, $value)
    {
        $value = $param->filter($value);
        if ($value instanceof PostFileInterface) {
            $request->addPostFile($value);
        } else {
            $request->addPostFile($param->getWireName(), $value);
        }
    }
}
