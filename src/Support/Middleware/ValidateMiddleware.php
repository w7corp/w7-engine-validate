<?php

namespace W7\Validate\Support\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\Core\Middleware\MiddlewareAbstract;
use W7\Validate\Support\Storage\ValidateConfig;
use W7\Validate\Validate;

class ValidateMiddleware extends MiddlewareAbstract
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$scene     = null;
		$validator = $this->getValidate($request);
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
	
	final public function getValidate(ServerRequestInterface $request)
	{
		$route      = $request->getAttribute('route');
		$controller = $route['controller'] ?? '';
		$scene      = $route['method']     ?? '';
		
		$validate   = str_replace(ValidateConfig::instance()->controllerPath, '', $controller);
		$_namespace = explode('\\', $validate);
		$fileName   = str_replace('Controller', 'Validate', array_pop($_namespace));
		$validate   = ValidateConfig::instance()->validatePath . implode('\\', $_namespace) . '\\' . $fileName;
		
		if (class_exists($validate)) {
			if (is_subclass_of($validate, Validate::class)) {
				/** @var Validate $validator */
				$validator = new $validate($request);
				$validator->scene($scene);
				return $validator;
			}
			
			throw new Exception("The given 'Validate' " . $validate . ' has to be a subtype of W7\Validate\Validate');
		}
		return false;
	}
}
