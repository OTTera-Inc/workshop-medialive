<?php

declare(strict_types=1);

use App\Application\Actions\Channel\ChangeInputAction;
use App\Application\Actions\Channel\ListChannelsAction;
use App\Application\Actions\Channel\InsertAdBreakAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/channels', function (Group $group) {
        $group->get('', ListChannelsAction::class);
        $group->post('/{id}/input', ChangeInputAction::class);
        $group->post('/{id}/ad-break', InsertAdBreakAction::class);
    });
};
