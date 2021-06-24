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
use Illuminate\Translation\FileLoader;
use Illuminate\Validation\Factory;
use Illuminate\Validation\PresenceVerifierInterface;
use ReflectionClass;

final class ValidateConfig
{
    /**
     * Custom rules namespace prefixes
     * @var array
     */
    protected $rulesPath = [];

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

    protected static $instance;

    public static function instance(): ValidateConfig
    {
        if (empty(self::$instance)) {
            self::$instance = new ValidateConfig();
        }

        return self::$instance;
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
            $this->factory = new Factory($this->getTranslator(), $this->getContainer());
            if ($this->getPresenceVerifier()) {
                $this->factory->setPresenceVerifier($this->getPresenceVerifier());
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
}
