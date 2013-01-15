<?php
/**
 * This file is part of the inroute package
 *
 * Copyright (c) 2013 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package itbz\inroute
 */

namespace itbz\inroute;

use itbz\inroute\Exception\RuntimeExpection;
use Mustache_Engine;
use Symfony\Component\Finder\Finder;

/**
 * The Inrout router generator
 *
 * Takes user input and generates php code that returns a custom Inroute object.
 * User input includes files or directories to scan for classes, an optional
 * www-root, a caller classname (if the default caller is not used) and a
 * Pimple DI-container.
 * 
 * @package itbz\inroute
 */
class RouterGenerator
{
    /**
     * Mustache instance
     *
     * @var Mustache_Engine
     */
    private $mustache;

    /**
     * List of classes to process
     *
     * @var array
     */
    private $reflectionClasses = array();

    /**
     * Root path
     *
     * @var string
     */
    private $root = '';

    /**
     * Caller classname
     *
     * @var string
     */
    private $caller = 'DefaultCaller';

    /**
     * The Inrout router generator
     *
     * @param Mustache_Engine $mustache
     */
    public function __construct(Mustache_Engine $mustache)
    {
        $this->mustache = $mustache;
    }

    /**
     * Scan dir and process found classes
     *
     * @param string $dirname
     *
     * @return RouterGenerator instance for chaining
     */
    public function addDir($dirname)
    {
        $finder = new Finder();
        $finder->in($dirname)->files()->name('*.php');
        foreach ($finder as $file) {
            $this->addFile($file->getRealpath());
        }

        return $this;
    }

    /**
     * Scan file and process found classes
     *
     * @param string $filename
     *
     * @return RouterGenerator instance for chaining
     *
     * @throws RuntimeException If $filename is not readable
     */
    public function addFile($filename)
    {
        if (!file($filename) or !is_readable($filename)) {
            $msg = "'$filename' is not a readable file";
            throw new RuntimeExpection($msg);
        }

        $currentClasses = get_declared_classes();
        include $filename;
        $includedClasses = array_diff(get_declared_classes(), $currentClasses);
        foreach ($includedClasses as $classname) {
            $this->addClass($classname);
        }

        return $this;
    }

    /**
     * Add class for processing
     *
     * @param string $classname
     *
     * @return RouterGenerator instance for chaining
     */
    public function addClass($classname)
    {
        $reflClass = new ReflectionClass($classname);
        if ($reflClass->isInroute()) {
            $this->reflectionClasses[] = $reflClass;
        }

        return $this;
    }

    /**
     * Set root path
     *
     * @param string $root
     *
     * @return RouterGenerator instance for chaining
     */
    public function setRoot($root)
    {
        assert('is_string($root)');
        $this->root = $root;

        return $this;
    }

    /**
     * Get root path
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set caller classname
     *
     * @param string $caller
     *
     * @return RouterGenerator instance for chaining
     */
    public function setCaller($caller)
    {
        assert('is_string($caller)');
        $this->caller = $caller;

        return $this;
    }

    /**
     * Get caller classname
     *
     * @return string
     */
    public function getCaller()
    {
        return $this->caller;
    }

    /**
     * Get loaded reflection classes
     *
     * @return array
     */
    public function getReflectionClasses()
    {
        return $this->reflectionClasses;
    }

    /**
     * Get code for the generated DIC
     *
     * @return string
     */
    public function getDependencyContainerCode()
    {
        $factories = array();
        foreach ($this->getReflectionClasses() as $refl) {
            $factories[] = array(
                'name' => $refl->getFactoryName(),
                'class' => $refl->getName(),
                'signature' => $refl->getSignature(),
                'params' => $refl->getInjections()
            );
        }

        return $this->mustache->loadTemplate('Dependecies')
            ->render(array('factories' => $factories));
    }

    /**
     * Get code for the generated route map
     *
     * @return string
     *
     * @todo ReflectionClass->getRoutes måste returnera enligt rätt form
     */
    public function getRouteCode()
    {
        $routes = array();
        foreach ($this->getReflectionClasses() as $refl) {
            foreach ($refl->getRoutes() as $route) {
                $routes[] = array(
                    'name' => $route['desc'],
                    'path' => '/',
                    'method' => 'GET',
                    'cntrlfactory' => $refl->getFactoryName(),
                    'cntrlmethod' => $route['name']
                );
            }
        }

        return $this->mustache->loadTemplate('routes')
            ->render(array('routes' => $routes, 'root' => $this->getRoot()));
    }

    /**
     * Get static bootstrap code
     *
     * @return string
     */
    public function getStaticCode()
    {
        return $this->mustache->loadTemplate('static')
            ->render(array('caller' => $this->getCaller()));
    }

    /**
     * Generate code
     *
     * @return string The generated code
     */
    public function generate()
    {
        return $this->getDependencyContainerCode()
            . $this->getRouteCode()
            . $this->getStaticCode();
    }
}