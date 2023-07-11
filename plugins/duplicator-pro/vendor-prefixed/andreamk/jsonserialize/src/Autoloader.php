<?php

/**
 * JsonSerialize class
 *
 * @package Amk\JsonSerialize
 */
namespace VendorDuplicator\Amk\JsonSerialize;

/**
 * Autoloader class
 */
final class Autoloader
{
    const ROOT_NAMESPACE = __NAMESPACE__ . '\\';
    /**
     * Register autoloader function
     *
     * @return void
     */
    public static function register()
    {
        \spl_autoload_register(array(__CLASS__, 'load'));
        // @phpstan-ignore-line
    }
    /**
     * Load class
     *
     * @param string $className class name
     *
     * @return bool return true if class is loaded
     */
    public static function load($className)
    {
        if (\strpos($className, self::ROOT_NAMESPACE) !== 0) {
            return \false;
        }
        foreach (self::getNamespacesMapping() as $namespace => $mappedPath) {
            if (\strpos($className, $namespace) !== 0) {
                continue;
            }
            $filepath = self::getFilenameFromClass($className, $namespace, $mappedPath);
            if (\file_exists($filepath)) {
                include $filepath;
                return \true;
            }
        }
        return \false;
    }
    /**
     * Return PHP file full class from class name
     *
     * @param string $class      Name of class
     * @param string $namespace  Base namespace
     * @param string $mappedPath Base path
     *
     * @return string
     */
    protected static function getFilenameFromClass($class, $namespace, $mappedPath)
    {
        $subPath = \str_replace('\\', '/', \substr($class, \strlen($namespace))) . '.php';
        $subPath = \ltrim($subPath, '/');
        return \rtrim($mappedPath, '\\/') . '/' . $subPath;
    }
    /**
     * Return namespace mapping
     *
     * @return string[]
     */
    protected static function getNamespacesMapping()
    {
        // the order is important, it is necessary to insert the longest namespaces first
        return [self::ROOT_NAMESPACE => __DIR__ . '/'];
    }
}
