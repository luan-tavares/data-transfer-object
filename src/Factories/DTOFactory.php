<?php

namespace LTL\DataTransferObject\Factories;

use Error;
use LTL\DataTransferObject\DataTransferObject;
use LTL\DataTransferObject\Exceptions\DataTransferObjectException;
use LTL\DataTransferObject\Exceptions\NotInitializedDTOException;
use LTL\DataTransferObject\Exceptions\ValidationDTOException;
use LTL\DataTransferObject\Interfaces\CastInterface;
use LTL\DataTransferObject\Interfaces\ValidateInterface;
use ReflectionClass;
use ReflectionProperty;

abstract class DTOFactory
{
    public static function build(DataTransferObject $object, array $data): void
    {
        $reflection = new ReflectionClass($object);

        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_READONLY);

        $errors = [];
        $notInitialized = [];

        foreach ($properties as $property) {
            $name = $property->name;

            try {
                $value = self::setProperty($property, $data);
                $property->setValue($object, $value);
            } catch(ValidationDTOException $exception) {
                $errors[$name] = $exception->getMessage();
            } catch(NotInitializedDTOException $exception) {
                $notInitialized[] = $exception->getMessage();
            } catch(Error $exception) {
                throw new DataTransferObjectException($exception->getMessage());
            }
        }

        if(!empty($notInitialized)) {
            $notInitialized = implode('", "', $notInitialized);

            throw new DataTransferObjectException("Properties \"{$notInitialized}\" must be initialized");
        }

        if(!empty($errors)) {
            throw new ValidationDTOException($errors);
        }
    }

    private static function setProperty(ReflectionProperty $property, array $data): mixed
    {
        $name = $property->name;

        if(!array_key_exists($name, $data)) {
            throw new NotInitializedDTOException($name);
        }

        $value = $data[$name];

        $attributes = $property->getAttributes();

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
