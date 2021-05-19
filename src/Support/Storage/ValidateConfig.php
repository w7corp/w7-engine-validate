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

final class ValidateConfig
{
    /**
     * 自定义规则命名空间前缀
     * @var array
     */
    protected array $rulesPath = [];

    /**
     * 翻译器
     * @var Translator
     */
    protected Translator $translator;

    /**
     * 容器
     * @var Container
     */
    protected Container $container;

    /**
     * 验证器工厂
     * @var Factory
     */
    protected Factory $factory;

    /**
     * 存在验证器
     * @var PresenceVerifierInterface
     */
    protected PresenceVerifierInterface $verifier;

    /**
     * 框架类型
     * 1 Laravel 2 Rangine
     * @var int
     */
    protected int $framework = 0;

    protected static ValidateConfig $instance;

    public static function instance(): ValidateConfig
    {
        if (empty(self::$instance)) {
            self::$instance = new ValidateConfig();
        }

        return self::$instance;
    }

    /**
     * 设置框架类型
     * @param int $type 1 Laravel 2 Rangine
     */
    public function setFramework(int $type): ValidateConfig
    {
        $this->framework = $type;
        return $this;
    }

    /**
     * 设置验证器工厂
     * @param Factory $factory
     * @return ValidateConfig
     */
    public function setFactory(Factory $factory): ValidateConfig
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * 获取验证器工厂
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
                        throw new \RuntimeException('Framework Type Error');
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
     * 设置存在验证器的实现。
     * @param PresenceVerifierInterface $presenceVerifier
     * @return $this
     */
    public function setPresenceVerifier(PresenceVerifierInterface $presenceVerifier): ValidateConfig
    {
        $this->verifier = $presenceVerifier;
        return $this;
    }

    /**
     * 获取存在验证器
     * @return PresenceVerifierInterface|null
     */
    private function getPresenceVerifier(): ?PresenceVerifierInterface
    {
        return $this->verifier ?? null;
    }

    /**
     * 提供容器
     * @param Container $container
     * @return ValidateConfig
     */
    public function setContainer(Container $container): ValidateConfig
    {
        $this->container = $container;
        return $this;
    }

    /**
     * 获取容器
     * @return Container|null
     */
    private function getContainer(): ?Container
    {
        return $this->container ?? null;
    }

    /**
     * 提供翻译器
     * @param Translator $translator
     * @return $this
     */
    public function setTranslator(Translator $translator): ValidateConfig
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * 获取翻译器
     * @return Translator
     */
    private function getTranslator(): Translator
    {
        if (empty($this->translator)) {
            $reflection = new \ReflectionClass(ClassLoader::class);
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
     * 设置自定义规则命名空间前缀,<b>如设置多个则全部生效</b>
     * @param string $rulesPath 自定义规则命名空间前缀
     * @return $this
     */
    public function setRulesPath(string $rulesPath): ValidateConfig
    {
        $this->rulesPath[] = $rulesPath;
        $this->rulesPath   = array_unique($this->rulesPath);
        return $this;
    }

    /**
     * 获取自定义规则命名空间前缀
     * @return array
     */
    public function getRulePath(): array
    {
        return $this->rulesPath;
    }
}
