<?php

declare(strict_types=1);

$routeConfig = require dirname(__DIR__, 2) . '/appinfo/routes.php';
$controllerSource = file_get_contents(dirname(__DIR__, 2) . '/lib/Controller/AdminController.php');

assertTrue(is_string($controllerSource), 'The admin controller source must be readable.');

$expectedRoutes = [
    'admin#index' => 'GET',
    'admin#organizationForm' => 'GET',
    'admin#saveOrganization' => 'POST',
    'admin#calendarForm' => 'GET',
    'admin#saveCalendar' => 'POST',
];

$routesByName = [];
foreach ($routeConfig['routes'] ?? [] as $route)
{
    if (isset($route['name']))
    {
        $routesByName[$route['name']] = $route;
    }
}

foreach ($expectedRoutes as $routeName => $verb)
{
    assertTrue(isset($routesByName[$routeName]), sprintf('The protected admin route %s must remain registered.', $routeName));
    assertSame($verb, $routesByName[$routeName]['verb'] ?? null, sprintf('The admin route %s must keep its expected HTTP verb.', $routeName));
    assertSame('true', $routesByName[$routeName]['requirements']['admin'] ?? null, sprintf('The admin route %s must remain projected as an admin route.', $routeName));

    $methodName = substr($routeName, strpos($routeName, '#') + 1);
    $methodPosition = strpos($controllerSource, 'public function ' . $methodName . '(');
    assertTrue($methodPosition !== false, sprintf('The admin route %s must resolve to its controller method.', $routeName));

    $sourceBeforeMethod = substr($controllerSource, 0, $methodPosition);
    $docblockPosition = strrpos($sourceBeforeMethod, '/**');
    assertTrue($docblockPosition !== false, sprintf('The controller method %s must retain auditable security metadata.', $methodName));
    $methodMetadata = substr($controllerSource, $docblockPosition, $methodPosition - $docblockPosition);

    assertTrue(
        !str_contains($methodMetadata, 'NoAdminRequired'),
        sprintf('The admin route %s must not bypass Nextcloud administrator authentication.', $routeName),
    );

    if ($verb === 'POST')
    {
        assertTrue(
            !str_contains($methodMetadata, 'NoCSRFRequired'),
            sprintf('The writing admin route %s must keep Nextcloud CSRF protection enabled.', $routeName),
        );
    }
}
