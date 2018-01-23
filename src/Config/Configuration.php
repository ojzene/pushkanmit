<?php
namespace App\Config;
class Configuration
{
    public function config($mode)
    {
        $boolean_type = "";

        define("KEY","");
        switch ($mode)
        {
            case "production":
                $boolean_type = false;
                break;

            case "debug":
                $boolean_type = true;
                break;
        }

        $configuration =
            [
                'settings' => [
                    'displayErrorDetails' => $boolean_type,
                ],
            ];

        return $configuration;
    }
}