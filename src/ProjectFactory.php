<?php

namespace KlimovPaul\PhpProjectUpdate;

use KlimovPaul\PhpProjectUpdate\Helpers\Arr;
use KlimovPaul\PhpProjectUpdate\Helpers\Factory;
use RuntimeException;

/**
 * ProjectFactory
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ProjectFactory
{
    /**
     * @var array $config application config.
     */
    private $config = [];

    /**
     * Constructor.
     *
     * @param array $config application config.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create(string $name): Project
    {
        $config = Arr::merge(
            [
                '__class' => Project::class,
            ],
            $this->loadConfigFile($name)
        );

        return Factory::make($config);
    }

    private function loadConfigFile(string $name): self
    {
        $filename = $this->config['basePath'] . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $name . '.php';

        if (!file_exists($filename)) {
            throw new RuntimeException("Configuration file '{$filename}' does not exist.");
        }

        $data = require $filename;

        if (!is_array($data)) {
            throw new RuntimeException("Configuration file '{$filename}' does not return an array.");
        }

        return $this;
    }
}
