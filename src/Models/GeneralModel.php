<?php
namespace App\Models;

use Exception;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Client;
// use GuzzleHttp\Stream\Stream;
use \GuzzleHttp\Exception\ConnectException as ce;
use \GuzzleHttp\Exception\RequestException as re;
use App\Config\Auth;
use stdClass;

class GeneralModel
{
    // needed in the model to make http request
    public function make_guzzle_request($url, $method, $token = null, $body = null, $query = null)
    {
        if (!empty($url) || !empty($token) || !empty($method)) {
            try {
                $client = new Client();

//                if ($query !== null) {
//                    $request = $client->createRequest($method, $url, $query);
//                } else {
//                    $request = $client->createRequest($method, $url);
//                }

                $request = is_null($query) ?
                    $client->request($method, $url) :
                    $client->request($method, $url, $query);
                $request->setHeader('Content-type', 'application/json');

                if ($token == null || empty($token)) {
                } else {
                    $request->setHeader('Authorization', $token);
                }

                if ($method == "POST" || $method == "PUT") {
                    if ($body == null || empty($body)) {
                    } else {
                        $request->setBody($body);
                        // $request->setBody(Stream::factory($body));
                    }
                } elseif ($method == "GET") {
                    goto sendrequest;
                }
                sendrequest:
                $response = $client->send($request);
                $json = $response->json();
                return $json;
            } catch (ce $e) {
                //print_r($e); die();
                $error = 8081;
                return $error;
            } catch (re $e) {
                //print_r($e); die();
                $error = 8082;
                return $error;
            }

        } else {
            $error = 5005;
            return $error;
        }
    }

    // needed in the controller
    public function try_get($req_res, $model_method_array, $i, $output_format, $input=null, $params=null)
    {
        try{
            $request = $req_res[0];
            $response = $req_res[1];
            $output_data = [];

//          $body = $request->getBody();
//          $input = json_decode($body, true);

            $get_model_name = array_keys($model_method_array)[0];
            $get_model_value = $model_method_array[$get_model_name];

            $class_name = "App\\Models\\".$get_model_name;
            $my_obj = new $class_name();

            $get_model_single = $get_model_value[$i];

            if(empty($input) && empty($params)) {
                $output_data = $my_obj->$get_model_single();
            }
            elseif (!empty($input) && empty($params)) {
                $output_data = $my_obj->$get_model_single($input);
            } elseif (empty($input) && !empty($params)) {
                $output_data = $my_obj->$get_model_single($params);
            } elseif (!empty($input) && !empty($params)) {
                $output_data = $my_obj->$get_model_single($input, $params);
            }

            if ($output_data["status"] == "success" || $output_data['success'] == "true") { $httpstatus = 200; } else { $httpstatus = 400; }

            // return (new GeneralModel)->state_output_format($request, $response, $output_data);

            if ($output_format == "xml") {
                $xml_result = $this->output_xml($output_data, new \SimpleXMLElement('<root/>'))->asXML();
                return $response->withHeader('Content-Type', 'application/xml')
                    ->write($xml_result)
                    ->withStatus($httpstatus);
            } elseif ($output_format == "json") {
                return $response->withHeader('Content-Type', 'application/json')
                    ->withJson($output_data)
                    ->withStatus($httpstatus);
            }
            else {
                $result = [ 'status' => 'failed', 'message' => 'invalid output response specified' ];
                return $response->withHeader('Content-Type', 'application/json')
                    ->withJson($result)
                    ->withStatus(400);
            }

        } catch (\ResourceNotFoundException $e) {
            return $response->withStatus(404);

        } catch(\Exception $e){
            return $response->withStatus(400)
                ->withHeader('X-Statuses-Reason', $e->getMessage());
        }
    }

    public function output_xml(array $arr, \SimpleXMLElement $xml)
    {
        foreach ($arr as $k => $v) {
            is_array($v)
                ? $this->output_xml($v, $xml->addChild($k))
                : $xml->addChild($k, $v);
        }
        return $xml;
    }

    public function get_model_methods($model_name)
    {
        $class_name = 'App\\Models\\'.$model_name;

        if(class_exists($class_name)) {
            $my_obj = new $class_name();
            $class_methods = get_class_methods($my_obj);

            $array_method = []; $i = 0;
            foreach ($class_methods as $method_name) {
                $array_method[$model_name][$i] = $method_name;
                $i++;
            }
            $result = [ 'status' => true, 'message' => $array_method ];
        }
        elseif(!class_exists($class_name)) {
            $result = [ 'status' => false, 'message' => "Model Class Specified does not exist" ];
        }

        return $result;
    }

    public function state_output_format(Request $request, Response $response, $data)
    {
        $mediaType = (new Auth)->output_format;
        switch ($mediaType) {
            case 'xml':
                $xml_result = $this->output_xml($data, new \SimpleXMLElement('<root/>'))->asXML();
                $response->getBody()->write($xml_result);
                break;
            case 'json':
                $response->getBody()->write(json_encode($data));
                break;
            default:
                $data = [ 'status' => 'failed', 'message' => 'invalid output response specified' ];
                $response->getBody()->write(json_encode($data));
                break;
        }
        return $response->withHeader("Content-Type", $mediaType);
    }

    public function encrypt3Des($data, $key)
    {
        //Generate a key from a hash
        $key = md5(utf8_encode($key), true);

        //Take first 8 bytes of $key and append them to the end of $key.
        $key .= substr($key, 0, 8);

        //Pad for PKCS7
        $blockSize = mcrypt_get_block_size('tripledes', 'ecb');
        $len = strlen($data);
        $pad = $blockSize - ($len % $blockSize);
        $data = $data . str_repeat(chr($pad), $pad);

        //Encrypt data
        $encData = mcrypt_encrypt('tripledes', $key, $data, 'ecb');

        //return $this->strToHex($encData);

        return base64_encode($encData);
    }

    public function decrypt3Des($data, $secret)
    {
        //Generate a key from a hash
        $key = md5(utf8_encode($secret), true);

        //Take first 8 bytes of $key and append them to the end of $key.
        $key .= substr($key, 0, 8);

        $data = base64_decode($data);

        $data = mcrypt_decrypt('tripledes', $key, $data, 'ecb');

        $block = mcrypt_get_block_size('tripledes', 'ecb');
        $len = strlen($data);
        $pad = ord($data[$len - 1]);

        return substr($data, 0, strlen($data) - $pad);
    }

    // set the http header to json by default
    public function check_couchdb($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content_type' => 'application/json',
            'Accept' => '*/*'
        ));

        $response = curl_exec($ch);
        return $response;
    }

    // get all the databases in the couchdb
    public function get_all_database($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content_type' => 'application/json',
            'Accept' => '*/*'
        ));
        $response = curl_exec($ch);
        return $response;
    }

    // create database in the couch db
    public function create_doc($url,$doc_name,$doc_values){
       try {
           $client = new CouchClient($url, $doc_name);
           $client->createDatabase();
           $doc = new stdClass();
           $doc->email = $doc_values['email'];
           $doc->token= $doc_values['token'];
           $doc->expire = $doc_values['expire'];
           $client->storeDoc($doc);
           return $doc->_id;
       }catch (Exception $exception){
           return  "Something weird happened: ".$exception->getMessage()." (errcode=".$exception->getCode().")\n";
       }
    }

    public function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }


    public function httpGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_HTTPHEADER, 'Content-Type: application/json');
        //  curl_setopt($ch,CURLOPT_HEADER, false);
        $output=curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function httpGetWithErros($url)
    {
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $output=curl_exec($ch);

        if(!$output)
        {
            echo "Error Number:".curl_errno($ch)."<br>";
            echo "Error String:".curl_error($ch);
        }
        curl_close($ch);
        return $output;
    }

    public function httpPost($url,$params)
    {
        // print_r($params); die();

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, 'Content-Type: application/json');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $output=curl_exec($ch);
        if (curl_errno($ch)) {
            $result = "Error: " . curl_error($ch);
            curl_close($ch);
        } else {
            curl_close($ch);
            $result = $output;
        }
        return $result;
    }

    public function httpPostWithHeader($url,$params,$token)
    {

        $headr = array();
        $headr[] = 'Content-Type: application/json';
        $headr[] = 'Authorization: '.$token;

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headr);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $output=curl_exec($ch);
        return $output;

        curl_close($ch);
    }

}