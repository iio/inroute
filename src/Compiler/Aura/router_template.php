

declare(strict_types = 1);

<?php if ($settings->getSetting('target-namespace')) { ?>
namespace <?= $settings->getSetting('target-namespace') ?>;
<?php } ?>

/**
 * NOTE: This file was auto-generated by inroute <?= \inroutephp\inroute\Package::VERSION ?>
 * and should not be edited directly.
 */
final class <?= $settings->getSetting('target-classname') ?> implements \inroutephp\inroute\Runtime\HttpRouterInterface
{
    use \inroutephp\inroute\Runtime\Aura\HttpRouterTrait;

    protected function loadRoutes(\Aura\Router\Map $map): void
    {
        \inroutephp\inroute\Package::validateVersion('<?= \inroutephp\inroute\Package::VERSION ?>');

        $mapper = new \inroutephp\inroute\Runtime\Aura\RouteMapper($map);

<?php foreach ($exportedRoutes as $route) { ?>
$mapper->mapRoute(<?= $route ?>);
<?php } ?>
    }
}