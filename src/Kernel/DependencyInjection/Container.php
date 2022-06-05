<?php

namespace SmallStaticHttp\Kernel\DependencyInjection;

use SmallStaticHttp\Kernel\Configuration\Configuration;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{

    const PARAM_PREFIX = '$';
    const SERVICE_PREFIX = '@';
    const ENV_PREFIX = '%env:';

    protected static array $services = [];
    protected static array $parameters = [];

    public function loadParametersFromJson(string $json): bool
    {
        $params = json_decode($json, true);

        if ($params === false) {
            return false;
        }

        self::$parameters = array_merge($params, self::$parameters);

        return true;
    }

    /**
     * Get parameter
     * @param $name
     * @param array|null $next
     * @return mixed
     * @throws NotFoundException
     */
    public function getParameter($name, array $next = null)
    {
        // All params as next if null
        if ($next === null) {
            $next = self::$parameters;
        }

        // Get components
        $paramComponents = explode('.', $name);

        // Check component exists
        if (!isset($next[$paramComponents[0]])) {
            throw new NotFoundException("Parameter not found ($name)");
        }

        // Last component => return value
        if (count($paramComponents) == 1) {
            return $next[$paramComponents[0]];
        }

        // Find next value
        $next = $next[$paramComponents[0]];
        unset($paramComponents[0]);

        try {
            return self::getParameter(implode('.', $paramComponents), $next);
        } catch (NotFoundException $e) {
            throw new NotFoundException("Parameter not found ($name)");
        }

        return $value;
    }

    /**
     * Get service
     * @param string $id
     * @return $this|mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get(string $id): mixed
    {
        if ($id == 'container') {
            return $this;
        }

        // Is service already defined
        if (array_key_exists($id, static::$services)) {
            return self::$services[$id];
        }

        return $this->createService($id);
    }

    /**
     * Is service exists
     * @param string $id
     * @return bool
     */
    public function has($id): bool
    {
        // Check definition
        if (array_key_exists($id, Configuration::SERVICES_DEFINITION[$id])) {
            return true;
        } else {
            // Check by alias
            foreach (Configuration::SERVICES_DEFINITION as $serviceDefinition) {
                if (array_key_exists('alias', $serviceDefinition) && $serviceDefinition['alias'] == $id) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create service
     * @param string $id
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function createService(string $id)
    {
        // Check definition
        if (array_key_exists($id, Configuration::SERVICES_DEFINITION)) {
            // Check by id
            $serviceId = $id;
            $serviceDefinition = Configuration::SERVICES_DEFINITION[$id];
        } else {
            // Check by alias
            foreach (Configuration::SERVICES_DEFINITION as $serviceId => $serviceDefinition) {
                if (array_key_exists('alias', $serviceDefinition) && $serviceDefinition['alias'] == $id) {
                    break;
                }
            }
        }

        if (!isset($serviceDefinition)) {
            throw new NotFoundException("Service '$id' not found in services definition !");
        }

        // Create param array
        $params = [];
        if (array_key_exists('params', $serviceDefinition)) {
            foreach ($serviceDefinition['params'] as $param) {
                // Inject parameter
                if (substr($param, 0, strlen(self::PARAM_PREFIX)) == self::PARAM_PREFIX) {
                    $params[] = $this->getParameter(substr($param, strlen(self::PARAM_PREFIX)));
                // Inject service
                } elseif (substr($param, 0, strlen(self::SERVICE_PREFIX)) == self::SERVICE_PREFIX) {
                    $params[] = $this->get(substr($param, strlen(self::SERVICE_PREFIX)));
                // Inject environment var
                } elseif (substr($param, 0, strlen(self::ENV_PREFIX)) == self::ENV_PREFIX) {
                    $params[] = getenv(substr($param, strlen(self::ENV_PREFIX)));
                }
            }
        }

        // Get class name
        if (array_key_exists('class', $serviceDefinition)) {
            if (!class_exists($serviceDefinition['class'])) {
                throw new ContainerException("Class of definition does not exists for service '$serviceId'");
            }
            $class = $serviceDefinition['class'];
        } elseif (class_exists($serviceId)) {
            $class = $serviceId;
        } else {
            throw new ContainerException("Class not found for service '$serviceId'");
        }

        // Create service
        $service = new $class(...$params);

        // And register
        self::$services[$id] = $service;
        if (array_key_exists('alias', $serviceDefinition)) {
            self::$services[$serviceDefinition['alias']] = $service;
        }

        return $service;
    }

}