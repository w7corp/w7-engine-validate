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

use Composer\Autoload\ClassLoader;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use Illuminate\Translation\FileLoader;
use Illuminate\Validation\Factory;
use Illuminate\Validation\PresenceVerifierInterface;
use ReflectionClass;
use RuntimeException;

final class ValidateConfig
{
    /**
     * Custom rules namespace prefixes
     * @var array
     */
    protected $rulesPath = [];

    /**
     * Automatic loading of validator rules
     * @var array
     */
    protected $autoValidatePath = [];

    /**
     * Translator
     * @var Translator
     */
    protected $translator;

    /**
     * Containers
     * @var Container
     */
    protected $container;

    /**
     * Validator Factory
     * @var Factory
     */
    protected $factory;

    /**
     * Presence Validator
     * @var PresenceVerifierInterface
     */
    protected $verifier;

    /**
     * Frame Type
     * 1 Laravel 2 Rangine
     * @var int
     */
    protected $framework = 0;

    /**
     * Validator specific association
     * @var array
     */
    protected $validateLink = [];

    protected static $instance;

    public static function instance(): ValidateConfig
    {
        if (empty(self::$instance)) {
            self::$instance = new ValidateConfig();
        }

        return self::$instance;
    }

    /**
     * Set the frame type
     *
     * @param int $type 1 Laravel 2 Rangine
     */
    public function setFramework(int $type): ValidateConfig
    {
        $this->framework = $type;
        return $this;
    }

    /**
     * Provide validator factory
     *
     * @param Factory $factory
     * @return ValidateConfig
     */
    public function setFactory(Factory $factory): ValidateConfig
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * Get Validator Factory
     *
     * @return Factory
     */
    public function getFactory(): Factory
    {
        if (empty($this->factory)) {
            if ($this->framework > 0) {
                switch ($this->framework) {
                    case 1:
                        $this->factory = App::make('validator');
                        break;
                    case 2:
                        $this->factory = \W7\Facade\Container::singleton("W7\Contract\Validation\ValidatorFactoryInterface");
                        break;
                    default:
                        throw new RuntimeException('Framework Type Error');
                }
            } else {
                $this->factory = new Factory($this->getTranslator(), $this->getContainer());
                if ($this->getPresenceVerifier()) {
                    $this->factory->setPresenceVerifier($this->getPresenceVerifier());
                }
            }
        }

        return $this->factory;
    }

    /**
     * Set the presence verifier implementation.
     *
     * @param PresenceVerifierInterface $presenceVerifier
     * @return $this
     */
    public function setPresenceVerifier(PresenceVerifierInterface $presenceVerifier): ValidateConfig
    {
        $this->verifier = $presenceVerifier;
        return $this;
    }

    /**
     * Get presence verifier
     *
     * @return PresenceVerifierInterface|null
     */
    private function getPresenceVerifier(): ?PresenceVerifierInterface
    {
        return $this->verifier ?? null;
    }

    /**
     * Provide containers
     *
     * @param Container $container
     * @return ValidateConfig
     */
    public function setContainer(Container $container): ValidateConfig
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Get container
     *
     * @return Container|null
     */
    private function getContainer(): ?Container
    {
        return $this->container ?? null;
    }

    /**
     * Provide translator
     *
     * @param Translator $translator
     * @return $this
     */
    public function setTranslator(Translator $translator): ValidateConfig
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * Get Translator
     *
     * @return Translator
     */
    private function getTranslator(): Translator
    {
        if (empty($this->translator)) {
            $reflection = new ReflectionClass(ClassLoader::class);
            $vendorDir  = dirname($reflection->getFileName(), 2);
            $langPath   = $vendorDir . DIRECTORY_SEPARATOR . 'laravel-lang' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;
            if (file_exists($langPath . 'locales')) {
                $langPath .= 'locales';
            } else {
                $langPath .= 'src';
            }
            $loader = new FileLoader(new Filesystem(), $langPath);
            return new \Illuminate\Translation\Translator($loader, 'zh_CN');
        }

        return $this->translator;
    }

    /**
     * Set up auto-load validator rules
     *
     * @param string $controllerPath Controller path
     * @param string $validatePath   Validator path
     * @return $this
     */
    public function setAutoValidatePath(string $controllerPath, string $validatePath): ValidateConfig
    {
        if ('\\' !== substr($controllerPath, -1)) {
            $controllerPath = $controllerPath . '\\';
        }

        if ('\\' !== substr($validatePath, -1)) {
            $validatePath = $validatePath . '\\';
        }

        $this->autoValidatePath[$controllerPath] = $validatePath;
        return $this;
    }

    /**
     * Set Validator Association
     *
     * @param string|string[] $controller Controller namespace
     *                                    To specify a method, pass an array with the second element being the method name
     * @param string|string[] $validate   Validator namespace
     *                                    To specify a scene, pass an array with the second element being the scene name
     * @return $this
     */
    public function setValidateLink($controller, $validate): ValidateConfig
    {
        if (is_array($controller)) {
            $controllers = $controller;
            $controller  = $controllers[0];
            $method      = $controllers[1];
            # The "\" symbol must not be present in the array
            $controller = md5($controller);
            if (count($controllers) >= 2) {
                if (isset($this->validateLink[$controller])) {
                    $_validate = $this->validateLink[$controller];
                    $_validate = array_merge($_validate, [
                        $method => $validate
                    ]);
                    $this->validateLink[$controller] = $_validate;
                } else {
                    $this->validateLink[$controller] = [
                        $method => $validate
                    ];
                }
            }
        } else {
            $controller = md5($controller);
            if (isset($this->validateLink[$controller])) {
                $this->validateLink[$controller]['!__other__'] = $validate;
            } else {
                $this->validateLink[$controller] = [
                    '!__other__' => $validate
                ];
            }
        }
        return $this;
    }

    /**
     * Get validator specific associations
     *
     * @param string|null $controller Validator full namespace
     * @return array
     */
    public function getValidateLink(?string $controller = null): array
    {
        if (null === $controller) {
            return $this->validateLink;
        }
        return $this->validateLink[md5($controller)] ?? [];
    }

    /**
     * Set the custom rules namespace prefix, If more than one exists, they all take effect
     *
     * @param string $rulesPath Custom rules namespace prefixes
     * @return $this
     */
    public function setRulesPath(string $rulesPath): ValidateConfig
    {
        $this->rulesPath[] = $rulesPath;
        $this->rulesPath   = array_unique($this->rulesPath);
        return $this;
    }

    /**
     * Get custom rules namespace prefixes
     *
     * @return array
     */
    public function getRulePath(): array
    {
        return $this->rulesPath;
    }

    /**
     * Get auto-load validator rules
     *
     * @return array
     */
    public function getAutoValidatePath(): array
    {
        return $this->autoValidatePath;
    }
}
