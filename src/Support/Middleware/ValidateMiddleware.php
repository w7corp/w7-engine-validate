<?php

namespace W7\Validate\Support\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\Core\Facades\Container;
use W7\Core\Middleware\MiddlewareAbstract;
use W7\Validate\Support\Storage\ValidateRelation;

class ValidateMiddleware extends MiddlewareAbstract
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$scene = null;
		/** @var ValidateRelation $validateRelation */
		$validateRelation = Container::get(ValidateRelation::class);
		$validator        = $validateRelation->getValidate($request);
		if (false === $validator) {
			$data = [];
		} else {
			$data    = array_merge([], $request->getQueryParams(), $request->getParsedBody(), $request->getUploadedFiles());
			$data    = $validator->check($data);
			$request = $validator->getRequest();
		}
		$request = $request->withAttribute('validate', $data);
		return $handler->handle($request);
	}
}
