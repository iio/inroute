<?php

declare(strict_types = 1);

namespace inroutephp\inroute\Compiler\OpenApi;

use inroutephp\inroute\Compiler\CompilerPassInterface;
use inroutephp\inroute\Runtime\RouteInterface;
use OpenApi\Annotations\Operation;
use const OpenApi\Annotations\UNDEFINED;

final class OpenApiCompilerPass implements CompilerPassInterface
{
    public function processRoute(RouteInterface $route): RouteInterface
    {
        /** @var ?Operation $operation */
        $operation = $route->getAnnotation(Operation::CLASS);

        if ($operation) {
            $route = $route
                ->withRoutable(true)
                ->withHttpMethod($operation->method);

            if ($operation->path != UNDEFINED) {
                $route = $route->withPath($operation->path);
            }

            if ($operation->operationId != UNDEFINED) {
                $route = $route->withName($operation->operationId);
            }
        }

        return $route;
    }
}
