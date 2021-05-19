<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2021 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Validate\Support\Middleware\Rangine;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\Core\Route\Route;
use W7\Facade\Context;
use W7\Core\Middleware\MiddlewareAbstract;
use W7\Http\Message\Server\Request;
use W7\Validate\Support\Storage\ValidateMiddlewareConfig;

class ValidateMiddleware extends MiddlewareAbstract
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Route $route */
        $route        = $request->getAttribute('route');
        $routeHandler = $route->handler;

        if (!is_array($routeHandler) || 2 !== count($routeHandler)) {
            throw new \RuntimeException('Routing information retrieval failed');
        }

        list($controller, $scene) = $routeHandler;

        $validator = ValidateMiddlewareConfig::instance()->getValidateFactory()->getValidate($controller, $scene);

        if ($validator) {
            $data = array_merge([], $request->getQueryParams(), $request->getParsedBody(), $request->getUploadedFiles());
            $data = $validator->check($data);
            /** @var Request $request */
            $request = $request->withAttribute('__validate__data__', $data);
            Context::setRequest($request);
        }
        
        return $handler->handle($request);
    }
}
