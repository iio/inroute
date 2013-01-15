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

use Aura\Router\Map;
use itbz\inroute\Exception\RuntimeExpection;

/**
 * Inroute base class
 *
 * @package itbz\inroute
 */
class Inroute
{
    /**
     * Aura map object
     *
     * @var Map
     */
    private $map;

    /**
     * Inroute base class
     *
     * @param Map $map
     */
    public function __construct(Map $map)
    {
        $this->map = $map;
    }

    /**
     * Dispatch request
     *
     * @param string $path
     * @param array $server Usually $_SERVER
     *
     * @return mixed Whatever the caller returns
     *
     * @throws RuntimeException If no route is found
     */
    public function dispatch($path, array $server)
    {
        assert('is_string($path)');
        $route = $this->map->match($path, $server);

        if (!$route) {
            $msg = "No route found for <$path>";
            throw new RuntimeException($msg);
        }

        // TODO make special route object

        return $route->values['controller']($route);
    }
}