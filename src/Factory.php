<?php

namespace KlimovPaul\PhpProjectUpdate;

use InvalidArgumentException;
use ReflectionClass;

/**
 * Factory allows creation of any object from its array definition.
 *
 * Keys in definition array are processed by following rules:
 *
 * - '__class': string, full qualified name of the class to be instantiated.
 * - '__construct()': array, arguments to be bound during constructor invocation.
 * - 'methodName()': array, list of arguments to be passed to the object method, which name defined via key.
 * - 'fieldOrProperty': mixed, value to be assigned to the public field or passed to the setter method.
 * - '()': callable, PHP callback to be invoked once object has been instantiated and all other configuration applied to it.
 *
 * For example:
 *
 * ```php
 * Factory::make([
 *     '__class' => Item::class,
 *     '__construct()' => ['constructorArgument' => 'initial'],
 *     'publicField' => 'value assigned to public field',
 *     'virtualProperty' => 'value passed to setter method',
 *     'someMethod()' => ['method argument1', 'method argument2'],
 *     '()' => function (Item $item) {
 *          // final adjustments
 *      },
 * ]);
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Factory
{
    /**
     * {@inheritdoc}
     */
    public static function make($definition)
    {
        if (is_array($definition)) {
            if (!isset($definition['__class'])) {
                throw new InvalidArgumentException('Array definition must contain "__class" key.');
            }

            $class = $definition['__class'];
            $constructArgs = $definition['__construct()'] ?? [];

            unset($definition['__class']);
            unset($definition['__construct()']);
            $config = $definition;
        } else {
            $class = $definition;
            $constructArgs = [];
            $config = [];
        }

        $object = static::instantiate($class, $constructArgs);

        return static::configure($object, $config);
    }

    /**
     * {@inheritdoc}
     */
    public static function configure($object, iterable $config)
    {
        $finalHandler = null;

        foreach ($config as $action => $arguments) {
            if ($action === '()') {
                $finalHandler = $arguments;

                continue;
            }

            if (substr($action, -2) === '()') {
                // method call
                $result = call_user_func_array([$object, substr($action, 0, -2)], $arguments);

                // handle immutable methods
                $object = static::chooseNewObject($object, $result);

                continue;
            }

            if (method_exists($object, $setter = 'set'.$action)) {
                // setter
                $result = call_user_func([$object, $setter], $arguments);

                // handle immutable methods
                $object = static::chooseNewObject($object, $result);

                continue;
            }

            // property
            if (property_exists($object, $action) || method_exists($object, '__set')) {
                $object->$action = $arguments;

                continue;
            }

            throw new InvalidArgumentException('Class "' . get_class($object) . '" does not have property "' . $action . '"');
        }

        if ($finalHandler !== null) {
            $result = call_user_func($finalHandler, $object);

            // handle possible immutability
            $object = static::chooseNewObject($object, $result);
        }

        return $object;
    }

    protected static function instantiate(string $class, array $constructArgs)
    {
        if (empty($constructArgs)) {
            return new $class;
        }

        $reflection = new ReflectionClass($class);
        return $reflection->newInstanceArgs($constructArgs);
    }

    /**
     * Picks the new object to be used from original trusted one and new possible candidate.
     * This method is used to handle possible immutable creating methods, when method invocation
     * does not alters object state, but creates new object instead.
     *
     * @param  object  $original original object.
     * @param  object|mixed  $candidate candidate value.
     * @return object new object to be used.
     */
    private static function chooseNewObject($original, $candidate)
    {
        if (is_object($candidate) && $candidate !== $original && get_class($candidate) === get_class($original)) {
            return $candidate;
        }

        return $original;
    }
}
