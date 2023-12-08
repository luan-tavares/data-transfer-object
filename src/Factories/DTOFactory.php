<?php

namespace LTL\DataTransferObject\Factories;

use Error;
use LTL\DataTransferObject\DataTransferObject;
use LTL\DataTransferObject\Exceptions\DataTransferObjectException;
use LTL\DataTransferObject\Exceptions\ValidationDTOException;
use LTL\DataTransferObject\Interfaces\CastInterface;
use LTL\DataTransferObject\Interfaces\ValidateInterface;
use ReflectionClass;
use ReflectionProperty;

abstract class DTOFactory
{
    public static function build(DataTransferObject $object, array $data): void
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if(error_reporting() === 0) {
                return;
            }

            if(error_reporting() & $severity) {
                throw new DataTransferObjectException($message);
            }
        });

        self::resolve($data, $object);

        restore_error_handler();
    }

    private static function resolve(array $data, DataTransferObject $object): void
    {
        $reflection = new ReflectionClass($object);

        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_READONLY);

        $errors = [];

        foreach ($properties as $property) {
            $name = $property->name;

            try {
                $value = self::setProperty($property, $data);
                $property->setValue($object, $value);
            } catch(ValidationDTOException $exception) {
                $errors[$name] = $exception->getMessage();
            } catch(Error $exception) {
                throw new DataTransferObjectException($exception->getMessage());
            }
        }

        if(!empty($errors)) {
            throw new ValidationDTOException($errors);
        }
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
             
            $attributeName = $attribute->getName();
            $reflectionAttribute = new ReflectionClass($attributeName);

            $castInterface = CastInterface::class;

            if($reflectionAttribute->implementsInterface($castInterface)) {
                /**
                 * @var CastInterface $attributeName
                 */
                $value =  $attributeName::cast($value);
                continue;
            }

            $validateInterface = ValidateInterface::class;

            if($reflectionAttribute->implementsInterface($validateInterface)) {
                /**
                 * @var ValidateInterface $objectAttribute
                 */
                $objectAttribute = $reflectionAttribute->newInstance(...$attribute->getArguments());
                $objectAttribute->validate($value);
                continue;
            }

            throw new Error("Cast \"{$attributeName}\" not implements \"{$castInterface}\" or \"{$validateInterface}\".");
        }
      
        return $value;
    }
}
