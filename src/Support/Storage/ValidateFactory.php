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

namespace W7\Validate\Support\Storage;

use Exception;
use W7\Validate\Support\Concerns\ValidateFactoryInterface;
use W7\Validate\Validate;

class ValidateFactory implements ValidateFactoryInterface
{
    public function getValidate(string $controller, string $scene = '')
    {
        $haveLink = false;
        $validate = '';

        $validateLink = ValidateMiddlewareConfig::instance()->getValidateLink($controller);
        if (!empty($validateLink)) {
            # 为指定的控制器方法指定了验证器
            if (isset($validateLink[$scene]) || isset($validateLink['!__other__'])) {
                if (isset($validateLink['!__other__'])) {
                    $method = '!__other__';
                } else {
                    $method = $scene;
                }

                # 为指定的验证器指定了验证场景
                if (is_array($validateLink[$method])) {
                    if (count($validateLink[$method]) >= 2) {
                        $validate = $validateLink[$method][0];
                        $scene    = $validateLink[$method][1];
                        $haveLink = true;
                    }
                } else {
                    $validate = $validateLink[$method];
                    $haveLink = true;
                }
            }
        }

        if (false === $haveLink) {
            # 处理指定了路径的控制器
            $controllerPath = '';
            $validatePath   = '';
            foreach (ValidateMiddlewareConfig::instance()->getAutoValidatePath() as $_controllerPath => $_validatePath) {
                if (false !== strpos($controller, $_controllerPath)) {
                    $controllerPath = $_controllerPath;
                    $validatePath   = $_validatePath;
                    break;
                }
            }
            if (empty($controllerPath)) {
                return false;
            }

            $validate   = str_replace($controllerPath, '', $controller);
            $_namespace = explode('\\', $validate);
            $fileName   = str_replace('Controller', 'Validate', array_pop($_namespace));
            $validate   = $validatePath . (!empty($_namespace) ? implode('\\', $_namespace) . '\\' : '') . $fileName;
        }

        if (class_exists($validate)) {
            if (is_subclass_of($validate, Validate::class)) {
                /** @var Validate $validator */
                $validator = new $validate();
                $validator->scene($scene);
                return $validator;
            }

            throw new Exception("The given 'Validate' " . $validate . ' has to be a subtype of W7\Validate\Validate');
        }
        return false;
    }
}
