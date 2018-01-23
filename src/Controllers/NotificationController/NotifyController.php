<?php
namespace App\Controllers\NotificationController;
use App\Config\Auth;
use App\Models\GeneralModel;
use App\Statuses\Statuses;
use Exception;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class NotifyController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
        $this->output_format = (new Auth)->output_format;
        $this->method_names = (new GeneralModel())->get_model_methods("NotificationModel\NotifyModel");
    }

    public function saveToken(Request $request, Response $response, $args)
    {
        try {
            $req_res = [$request, $response];
            $method_identity = $this->method_names;

            $data = (array)$request->getParsedBody();

            if (empty($data)) {
                $status = (new Statuses)->getStatusWithError(6001, 5005);
                $result = [
                    "status" => false,
                    "message" => $status
                ];
                return (new GeneralModel)->state_output_format($request, $response, $result);
            } else {
                if ($method_identity["status"] == true) {
                    $resp = (new GeneralModel)->try_get($req_res, $method_identity["message"], 0, $this->output_format, $data);
                    return $resp;
                } elseif ($method_identity["status"] == false) {
                    return $response->withHeader("Content-type", "application/json")
                        ->withJson($method_identity)
                        ->withStatus(400);
                }
            }

        } catch (Exception $exception) {
            return $exception;
        }
    }

    public function pushToAll(Request $request, Response $response, $args)
    {
        try {
            $req_res = [$request, $response];
            $method_identity = $this->method_names;

            $data = (array)$request->getParsedBody();

            if (empty($data)) {
                $status = (new Statuses)->getStatusWithError(6001, 5005);
                $result = [
                    "status" => false,
                    "message" => $status
                ];
                return (new GeneralModel)->state_output_format($request, $response, $result);
            } else {
                if ($method_identity["status"] == true) {
                    $resp = (new GeneralModel)->try_get($req_res, $method_identity["message"], 1, $this->output_format, $data);
                    return $resp;
                } elseif ($method_identity["status"] == false) {
                    return $response->withHeader("Content-type", "application/json")
                        ->withJson($method_identity)
                        ->withStatus(400);
                }
            }

        } catch (Exception $exception) {
            return $exception;
        }
    }

    public function pushToOne(Request $request, Response $response, $args)
    {
        try {
            $req_res = [$request, $response];
            $method_identity = $this->method_names;

            $data = (array)$request->getParsedBody();

            if (empty($data)) {
                $status = (new Statuses)->getStatusWithError(6001, 5005);
                $result = [
                    "status" => false,
                    "message" => $status
                ];
                return (new GeneralModel)->state_output_format($request, $response, $result);
            } else {
                if ($method_identity["status"] == true) {
                    $resp = (new GeneralModel)->try_get($req_res, $method_identity["message"], 2, $this->output_format, $data);
                    return $resp;
                } elseif ($method_identity["status"] == false) {
                    return $response->withHeader("Content-type", "application/json")
                        ->withJson($method_identity)
                        ->withStatus(400);
                }
            }

        } catch (Exception $exception) {
            return $exception;
        }
    }

    public function messageToTopicAround(Request $request, Response $response, $args)
    {
        try {
            $req_res = [$request, $response];
            $method_identity = $this->method_names;

            $data = (array)$request->getParsedBody();

            if (empty($data)) {
                $status = (new Statuses)->getStatusWithError(6001, 5005);
                $result = [
                    "status" => false,
                    "message" => $status
                ];
                return (new GeneralModel)->state_output_format($request, $response, $result);
            } else {
                if ($method_identity["status"] == true) {
                    $resp = (new GeneralModel)->try_get($req_res, $method_identity["message"], 3, $this->output_format, $data);
                    return $resp;
                } elseif ($method_identity["status"] == false) {
                    return $response->withHeader("Content-type", "application/json")
                        ->withJson($method_identity)
                        ->withStatus(400);
                }
            }

        } catch (Exception $exception) {
            return $exception;
        }
    }


    public function removeUserFromTopic(Request $request, Response $response, $args)
    {
        try {
            $req_res = [$request, $response];
            $method_identity = $this->method_names;

            $data = (array)$request->getParsedBody();

            if (empty($data)) {
                $status = (new Statuses)->getStatusWithError(6001, 5005);
                $result = [
                    "status" => false,
                    "message" => $status
                ];
                return (new GeneralModel)->state_output_format($request, $response, $result);
            } else {
                if ($method_identity["status"] == true) {
                    $resp = (new GeneralModel)->try_get($req_res, $method_identity["message"], 4, $this->output_format, $data);
                    return $resp;
                } elseif ($method_identity["status"] == false) {
                    return $response->withHeader("Content-type", "application/json")
                                    ->withJson($method_identity)
                                    ->withStatus(400);
                }
            }

        } catch (Exception $exception) {
            return $exception;
        }
    }
}