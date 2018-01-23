<?php
use App\Statuses\Statuses;

    if($mode == 'production' || $mode == 'maintenance' )
    {
        if ($mode == 'maintenance')
        {
            $container['notFoundHandler'] = function ($c)
            {
                return function ($request, $response) use ($c) {
                    $status_code = 6001;
                    $error_code = 8050;
                    $resultHandler = (new Statuses)->getStatusWithError($status_code, $error_code);
                    return $c['response']
                        ->withStatus(404)
                        ->withHeader('Content-Type', 'application/json')
                        ->withJson($resultHandler);
                };
            };
        }
        else
        {
            $container['notFoundHandler'] = function ($c) {
                return function ($request, $response) use ($c) {
                    $status_code = 6001;
                    $error_code = 8013;
                    $resultHandler = (new Statuses)->getStatusWithError($status_code, $error_code);
                    return $c['response']
                        ->withStatus(404)
                        ->withHeader('Content-Type', 'application/json')
                        ->withJson($resultHandler);
                };
            };


            $container['errorHandler'] = function ($c) {
                return function ($request, $response) use ($c) {
                    $status_code = 6001;
                    $error_code = 8012;
                    $resultHandler = (new Statuses)->getStatusWithError($status_code, $error_code);
                    return $c['response']
                        ->withStatus(500)
                        ->withHeader('Content-Type', 'application/json')
                        ->withJson($resultHandler);
                };
            };


            $container['notAllowedHandler'] = function ($c) {
                return function ($request, $response) use ($c) {
                    $status_code = 6001;
                    $error_code = 8080;
                    $resultHandler = (new Statuses)->getStatusWithError($status_code, $error_code);
                    return $c['response']
                        ->withStatus(405)
                        ->withHeader('Content-Type', 'application/json')
                        ->withJson($resultHandler);
                };
            };

            $container['phpErrorHandler'] = function ($c) {
                return function ($request, $response) use ($c) {
                    $status_code = 6001;
                    $error_code = 8012;
                    $resultHandler = (new Statuses)->getStatusWithError($status_code, $error_code);
                    return $c['response']
                        ->withStatus(500)
                        ->withHeader('Content-Type', 'application/json')
                        ->withJson($resultHandler);
                };
            };
        }
    }

    $container['NotifyController'] = function ($container){
        return new \App\Controllers\NotificationController\NotifyController($container);
    };

