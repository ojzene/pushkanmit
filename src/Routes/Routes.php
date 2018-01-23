<?php

if ($mode == 'production' || $mode == 'debug') {
    $app->group('/api', function () use ($app) {
        $app->group('/v1', function () use ($app) {
            $app->post('/push/save', 'NotifyController:saveToken');
            $app->post('/push/all', 'NotifyController:pushToAll');
            $app->post('/push/one', 'NotifyController:pushToOne');
            $app->post('/push/around', 'NotifyController:messageToTopic');
            $app->post('/topic/remove', 'NotifyController:removeUserFromTopic');
        });
    });
}