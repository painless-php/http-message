<?php

namespace PainlessPHP\Http\Message\Concern;

use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

trait CreatableFromArray
{
    public static function createFromArray(array $data) : self
    {
        // TODO: cache reflection class?
        // TODO: separate into hidden methods (of another class to separate from user class)
        // TODO: better context messaging for any constructor errors (trycatch + pass context info on recursion)

        $constructor = new ReflectionMethod(static::class, '__construct');
        $parameters = $constructor->getParameters();

        if(($result = self::createDirectlyIfPossible($parameters, $data)) !== null) {
            return $result;
        }

        foreach($parameters as $parameter) {
            $type = $parameter->getType();

            if(! ($type instanceof ReflectionNamedType)) {
                continue;
            }

            $targetClass = $type->getName();
            $value = $data[$parameter->getName()] ?? ($parameter->isOptional() ? $parameter->getDefaultValue() : null);

            // WARN: class_uses is not recursive for inheritance
            if(is_array($value) && ! $type->isBuiltin()) {
                // If target class uses this trait, use it to construct said class
                if(in_array(CreatableFromArray::class, class_uses($targetClass))) {
                    $data[$parameter->getName()] = ($targetClass)::createFromArray($value);
                    continue;
                }
                $targetConstructorParams = (new ReflectionMethod($targetClass, '__construct'))->getParameters();

                // Check for single param constructor
                if(count($targetConstructorParams) !== 1) {
                    continue;
                }

                // Use given data as single argument for constructor if possible
                $constructorArgType = $targetConstructorParams[0]->getType();
                if($constructorArgType instanceof ReflectionNamedType && $constructorArgType->getName() === 'array') {
                    $data[$parameter->getName()] = new $targetClass($value);
                }
            }
        }

        return new self(...$data);
    }

    /**
     * @param array<ReflectionParameter> $parameters
     */
    private static function createDirectlyIfPossible(array $parameters, array $data) : ?self
    {
        if(count($parameters) !== 1) {
            return null;
        }

        $constructorArgType = $parameters[0]->getType();

        if(! ($constructorArgType instanceof ReflectionNamedType)) {
            return null;
        }

        if($constructorArgType->getName() !== 'array') {
            return null;
        }

        // @phpstan-ignore arguments.count, argument.type
        return new self($data);
    }
}
