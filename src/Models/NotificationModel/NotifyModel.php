<?php
namespace App\Models\NotificationModel;

use App\Config\Auth;
use App\Statuses\Statuses;
use RedBeanPHP\R;
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;
use sngrl\PhpFirebaseCloudMessaging\Notification;

class NotifyModel
{
    public function saveToken($input)
    {
        if (empty($input)) {
            $status = (new Statuses)->getStatusWithError(6001, 5005);
            return $result = ['status' => 'failed', 'success' => false, 'message' => $status, 'code' => 5005];
        } else {
            $phone_number = (string)$input['phone_number'];
            $devicetoken = (string)$input['token'];
            $deviceid = (string)$input['deviceid'];
            $save_date = date("Y-m-d h:i:sa");

            $savetoken = R::dispense("devices");
            $savetoken->id = "";
            $savetoken->token = $devicetoken;
            $savetoken->deviceid = $deviceid;
            $savetoken->phone_number = $phone_number;
            $savetoken->date_saved = $save_date;
            $check_exist = R::findOne('devices', 'phone_number=?', [$phone_number]);
            if(!count($check_exist)) {
                R::store($savetoken);

                $topic_name = $phone_number;

                $savetopics = R::dispense("topics");
                $savetopics->id = "";
                $savetopics->phone_number = $phone_number;
                $savetopics->topic_name = $topic_name;
                $savetopics->date_created = $save_date;
                R::store($savetopics);

                $this::subscribeUserToTopic($topic_name, $devicetoken);
                $this::messageToTopic($topic_name);
            }
            else if (count($check_exist)) {
                $dbtoken = $check_exist['token'];
                $dbtime = $check_exist['date_saved'];

                if($dbtoken != $devicetoken) {
                    $check_exist['token'] = $devicetoken;
                    $check_exist['date_saved'] = $save_date;
                    R::store($check_exist);
                }

            }
        }
    }

    public function pushToAll($input)
    {
        $result = [];
        if (empty($input)) {
            $status = (new Statuses)->getStatusWithError(6001, 5005);
            return ['status' => 'failed', 'message' => $status, 'code' => 5005];
        } else {
            $message_text = (string)$input['message'];
            $message_title = (string)$input['title'];

            $server_key = (new Auth)->server_api_key;
            $client = new Client();
            $client->setApiKey($server_key);
            $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

            $find_devices = R::findAll('devices');
            $count = count($find_devices);
            if ($count) {
                $data = [];
                foreach ($find_devices as $key) {
                    unset($key['id']);
                    $data[] = $key;
                    $token = $key['token'];

                    $message = new Message();
                    $message->setPriority('high');
                    $message->addRecipient(new Device($token));
                    $message
                        ->setNotification(new Notification($message_title, $message_text))
                        ->setData(['key' => 'value']);
                    $response = $client->send($message);

                    $result = [
                        'status' => true,
                        'success' => true,
                        'message' => "Push to registered device",
                        'code' => $response->getStatusCode(),
                        'count' => $count,
                        "data" => $response->getBody()->getContents()
                    ];
                }

                return $result;

            } else {
                $status = (new Statuses)->getStatusWithError(6000, 5011);
                return $response = [
                    'status' => false,
                    'success' => false,
                    'message' => "No device found yet",
                    'code' => $status["code"]
                ];
            }
        }
    }

    public function pushToOne($input)
    {
        if (empty($input)) {
            $status = (new Statuses)->getStatusWithError(6001, 5005);
            return ['status' => 'failed', 'message' => $status, 'code' => 5005];
        } else {
            $phone_number = (string)$input['phone_number'];
            $message_text = (string)$input['message'];
            $message_title = (string)$input['title'];

            $server_key = (new Auth)->server_api_key;
            $client = new Client();
            $client->setApiKey($server_key);
            $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

            $find_device = R::findOne('devices', 'phone_number=?', [$phone_number]);
            $count = count($find_device);
            if ($count) {
                $token = $find_device['token'];

                $message = new Message();
                $message->setPriority('high');
                $message->addRecipient(new Device($token));
                $message
                    ->setNotification(new Notification($message_title, $message_text))
                    ->setData(['key' => 'value']);
                $response = $client->send($message);

                return [
                    'status' => true,
                    'success' => true,
                    'message' => "Device records",
                    'code' => $response->getStatusCode(),
                    'count' => $count,
                    "data" => $response->getBody()->getContents()
                ];

            } else {
                $status = (new Statuses)->getStatusWithError(6000, 5011);
                return [
                    'status' => false,
                    'success' => false,
                    'message' => "No such device found",
                    'code' => $status["code"]
                ];
            }
        }
    }

    public function sendDeviceToDevice($input)
    {
        if (empty($input)) {
            $status = (new Statuses)->getStatusWithError(6001, 5005);
            return ['status' => 'failed', 'message' => $status, 'code' => 5005];
        } else {
            $sender_phone_number = (string)$input['sender_phone_number'];  // optional
            $receiver_phone_number = (string)$input['receiver_phone_number'];
            $message_title = (string)$input['title'];
            $message_text = (string)$input['message'];

            $server_key = (new Auth)->server_api_key;
            $client = new Client();
            $client->setApiKey($server_key);
            $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

            $find_device = R::findOne('devices', 'phone_number=?', [$sender_phone_number]);
            $count = count($find_device);
            if ($count) {
                $findUser_topic = R::findOne('topics', 'phone_number=?', [$receiver_phone_number]);
                if(count($findUser_topic)) {
                    $rec_topic_name = $findUser_topic['topic_name'];

                    $message = new Message();
                    $message->setPriority('high');
                    $message->addRecipient(new Topic($rec_topic_name));
                    $message
                        ->setNotification(new Notification($message_title, $message_text))
                        ->setData(['key' => 'value']);
                    $response = $client->send($message);

                    return [
                        'status' => true,
                        'success' => true,
                        'message' => "Message to Topic Device records",
                        'code' => $response->getStatusCode(),
                        'count' => $count,
                        "data" => $response->getBody()->getContents()
                    ];
                } else {
                    $status = (new Statuses)->getStatusWithError(6000, 5011);
                    return [
                        'status' => false,
                        'success' => false,
                        'message' => "No such topic found",
                        'code' => $status["code"]
                    ];
                }

            } else {
                $status = (new Statuses)->getStatusWithError(6000, 5011);
                return [
                    'status' => false,
                    'success' => false,
                    'message' => "No such device found",
                    'code' => $status["code"]
                ];
            }
        }
    }

    public function removeUserFromTopic($input) {
        $phone_number = (string)$input['phone_number'];

        $server_key = (new Auth)->server_api_key;
        $client = new Client();
        $client->setApiKey($server_key);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

        $find_device = R::findOne('devices', 'phone_number=?', [$phone_number]);
        $count = count($find_device);
        if ($count) {
            $token = $find_device['token'];
            $findUser_topic = R::findOne('topics', 'phone_number=?', [$phone_number]);
            if (count($findUser_topic)) {
                $rec_topic_name = $findUser_topic['topic_name'];

                $response = $client->removeTopicSubscription($rec_topic_name, [$token]);
                return [
                    'status' => true,
                    'success' => true,
                    'message' => "User subscription removed from topic successfully",
                    'code' => $response->getStatusCode(),
                    'count' => $count,
                    "data" => $response->getBody()->getContents()
                ];
            } else {
                $status = (new Statuses)->getStatusWithError(6000, 5011);
                return [
                    'status' => false,
                    'success' => false,
                    'message' => "No such topic found",
                    'code' => $status["code"]
                ];
            }
        } else {
            $status = (new Statuses)->getStatusWithError(6000, 5011);
            return [
                'status' => false,
                'success' => false,
                'message' => "No such device found",
                'code' => $status["code"]
            ];
        }
    }

    public function messageToTopic($newtopic) {
        $server_key = (new Auth)->server_api_key;
        $client = new Client();
        $client->setApiKey($server_key);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

        $message = new Message();
        $message->setPriority('high');
        $message->addRecipient(new Topic($newtopic));
        $message
            ->setNotification(new Notification('12Gifts of the Season', 'Welcome to 12Gifts of the Season'))
            ->setData(['key' => 'value']);

        $response = $client->send($message);
        return [
            'status' => true,
            'success' => true,
            'message' => "Message to User Topic",
            'code' => $response->getStatusCode(),
            "data" => $response->getBody()->getContents()
        ];
    }

    public function subscribeUserToTopic($name_of_topic, $token) {
        $server_key = (new Auth)->server_api_key;
        $client = new Client();
        $client->setApiKey($server_key);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

        $response = $client->addTopicSubscription($name_of_topic, [$token]);
        return [
            'status' => true,
            'success' => true,
            'message' => "User subscribed to topic",
            'code' => $response->getStatusCode(),
            "data" => $response->getBody()->getContents()
        ];
    }

}