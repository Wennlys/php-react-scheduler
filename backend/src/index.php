<?php declare(strict_types=1);
header("Access-Control-Allow-Origin:*");

require __DIR__ . '/../vendor/autoload.php';

use Laminas\Diactoros\ResponseFactory;
use League\Route\RouteGroup;
use Laminas\Diactoros\Response;

use Source\Core\Connection;
use Source\Core\MongoConnection;
use Source\Middleware\AuthMiddleware;
use Source\App\SessionStoreController;
use Source\App\FileStoreController;
use Source\App\AppointmentStoreController;
use Source\App\UserIndexController;
use Source\App\UserShowController;
use Source\App\UserStoreController;
use Source\App\UserUpdateController;
use Source\App\UserDestroyController;
use Source\App\UserIndexProvidersController;
use Source\App\AppointmentIndexController;
use Source\App\AppointmentIndexScheduleController;
use Source\App\NotificationProviderIndexController;
use Source\App\NotificationProviderUpdateController;
use Source\App\AppointmentCancelController;
use Source\App\AppointmentIndexAvailableController;
use Source\Middleware\DefaultMiddleware;

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$responseFactory = new ResponseFactory();

$container = new League\Container\Container;

$container->add(UserIndexController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(UserIndexProvidersController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(UserShowController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(UserStoreController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(UserUpdateController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(UserDestroyController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(SessionStoreController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(FileStoreController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(AppointmentIndexController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(AppointmentCancelController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(AppointmentStoreController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(MongoConnection::getInstance())
    ->addArgument(Response::class);

$container->add(AppointmentIndexScheduleController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(AppointmentIndexAvailableController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(Response::class);

$container->add(NotificationProviderIndexController::class)
    ->addArgument(Connection::getInstance())
    ->addArgument(MongoConnection::getInstance())
    ->addArgument(Response::class);

$container->add(NotificationProviderUpdateController::class)
    ->addArgument(MongoConnection::getInstance())
    ->addArgument(Response::class);

$container->add(AuthMiddleware::class)
    ->addArgument(Response::class);

$container->add(Connection::class);
$container->add(MongoConnection::class);
$container->add(Response::class);

/** @var League\Route\Strategy\StrategyInterface $strategy */
$strategy = (new League\Route\Strategy\JsonStrategy($responseFactory))
    ->addDefaultResponseHeader('content-type', 'application/json')
    ->setContainer($container);

/** @var Router $router */
$router = (new League\Route\Router())->setStrategy($strategy);

$router->middleware(new DefaultMiddleware(new Response()));

$router->options('/{routes:.+}', function ($response) {
    return $response;
});

$router->map('POST', '/users', 'Source\App\UserStoreController::store');
$router->map('POST', '/sessions', 'Source\App\SessionStoreController::store');

$router->group(
    '/users',
    function (RouteGroup $route) {
        $route->map('GET', '/', 'Source\App\UserIndexController::index');
        $route->map('GET', '/show', 'Source\App\UserShowController::show');
        $route->map('PUT', '/', 'Source\App\UserUpdateController::update');
        $route->map('DELETE', '/', 'Source\App\UserDestroyController::destroy');
    }
)->middleware(new AuthMiddleware(new Response));

$router->group(
    '/providers',
    function (RouteGroup $route) {
        $route->map('GET', '/', 'Source\App\UserIndexProvidersController::index');
        $route->map(
            'GET',
            '/{providerId:number}/available',
            'Source\App\AppointmentIndexAvailableController::index'
        );
    }
)->middleware(new AuthMiddleware(new Response));

$router->group(
    '/notifications',
    function (RouteGroup $route) {
        $route->map('GET', '/', 'Source\App\NotificationProviderIndexController::index');
        $route->map('PUT', '/{id:.+}', 'Source\App\NotificationProviderUpdateController::update');
    }
)->middleware(new AuthMiddleware(new Response));

$router->map('GET', '/schedule', 'Source\App\AppointmentIndexScheduleController::index')
    ;

$router->group(
    '/appointments',
    function (RouteGroup $route) {
        $route->map('GET', '/', 'Source\App\AppointmentIndexController::index');
        $route->map('POST', '/', 'Source\App\AppointmentStoreController::store');
        $route->map('DELETE', '/{id:number}', 'Source\App\AppointmentCancelController::cancel');
    }
)->middleware(new AuthMiddleware(new Response));

$router->group(
    '/files',
    function (RouteGroup $route) {
        $route->map('POST', '/', 'Source\App\FileStoreController::store');
    }
)->middleware(new AuthMiddleware(new Response));

$response = $router->dispatch($request);

// send the response to the browser
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);

