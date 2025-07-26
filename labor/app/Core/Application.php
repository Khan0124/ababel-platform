<?php

namespace App\Core;

class Application
{
    private static $instance = null;
    private $container = [];
    private $singletons = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function bind($key, $resolver)
    {
        $this->container[$key] = $resolver;
    }

    public function singleton($key, $resolver)
    {
        $this->singletons[$key] = $resolver;
    }

    public function get($key)
    {
        // Check if it's a singleton and already instantiated
        if (isset($this->singletons[$key])) {
            if (is_callable($this->singletons[$key])) {
                $this->singletons[$key] = call_user_func($this->singletons[$key]);
            }
            return $this->singletons[$key];
        }

        // Check regular container
        if (isset($this->container[$key])) {
            if (is_callable($this->container[$key])) {
                return call_user_func($this->container[$key]);
            }
            return $this->container[$key];
        }

        throw new \Exception("Service {$key} not found in container");
    }

    public function make($class, array $parameters = [])
    {
        $reflection = new \ReflectionClass($class);
        
        if (!$reflection->isInstantiable()) {
            throw new \Exception("Class {$class} is not instantiable");
        }

        $constructor = $reflection->getConstructor();
        
        if (is_null($constructor)) {
            return new $class;
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);
        
        return $reflection->newInstanceArgs($dependencies);
    }

    private function resolveDependencies(array $parameters, array $primitives = [])
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType();
            
            if (is_null($dependency) || $dependency->isBuiltin()) {
                if (array_key_exists($parameter->name, $primitives)) {
                    $dependencies[] = $primitives[$parameter->name];
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve primitive parameter {$parameter->name}");
                }
            } else {
                $dependencies[] = $this->make($dependency->getName());
            }
        }

        return $dependencies;
    }
}