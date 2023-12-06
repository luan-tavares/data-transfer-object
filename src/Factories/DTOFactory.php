<?php

namespace LTL\DataTransferObject\Factories;

use Error;
use LTL\DataTransferObject\DataTransferObject;
use LTL\DataTransferObject\Exceptions\CastDTOException;
use LTL\DataTransferObject\Exceptions\DataTransferObjectException;
use LTL\DataTransferObject\Interfaces\CastInterface;
use ReflectionClass;
use ReflectionProperty;

abstract class DTOFactory
{
    public static function build(array $data, string $dtoClass): DataTransferObject
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if(error_reporting() === 0) {
                return;
            }

            if(error_reporting() & $severity) {
                throw new DataTransferObjectException($message);
            }
        });

        $object = self::resolve($data, $dtoClass);

        restore_error_handler();
       
        return $object;
    }

    private static function resolve(array $data, string $dtoClass): DataTransferObject
    {
        $reflection = new ReflectionClass($dtoClass);

        /**
         * @var DataTransferObject $object
         */
        $object = $reflection->newInstanceWithoutConstructor();

        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_READONLY);

        $errors = [];

        foreach ($properties as $property) {
            $name = $property->name;

            try {
                $value = self::setProperty($property, $data);
                $property->setValue($object, $value);
            } catch(Error $exception) {
                $errors[$name] = $exception->getMessage();
            } catch(CastDTOException $exception) {
                $object->setError($name, $exception->getMessage());
            }
        }

        if(!empty($errors)) {
            throw new DataTransferObjectException($errors);
        }
        
        return $object;
    }

    private static function setProperty(ReflectionProperty $property, array $data): mixed
    {
        $name = $property->name;

        if(!array_key_exists($name, $data)) {
            throw new Error('Property not initialized');
        }

        $value = $data[$name];

        if(empty($attributes = $property->getAttributes())) {
            return $value;
        }

        foreach ($attributes as $attribute) {
             
            $castClass = $attribute->getName();
            $reflectionCast = new ReflectionClass($castClass);

            $castInterface = CastInterface::class;

            if(!$reflectionCast->implementsInterface($castInterface)) {
                throw new Error("Cast \"{$castClass}\" not implements \"{$castInterface}\".");
            }

            $objectCast = $reflectionCast->newInstance();

            $value =  $objectCast->cast($name, $value);

        }
      
        return $value;
    }
}
