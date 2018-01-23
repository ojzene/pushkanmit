<?php

/**
 * Created by PhpStorm.
 * User: funmi
 * Date: 3/10/17
 * Time: 10:44 AM
 */
abstract class Base
{
//    use
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    // return list of properties to serialise
    protected function getSerializers($skip = array())
    {
        $vars = get_object_vars($this);
        $servars = array();

        foreach ($vars as $k => $var) {
            if (in_array($k, $skip)) {
                continue;
            }
            if ($k == 'options' || $k == 'loggers') {
                continue;
            }
            $servars[] = $k;
        }
        return $servars;
    }

    // returns serialized string
    public function serialized()
    {
        $properties = $this->getSerializers();

        $data = array();

        foreach ($properties as $property) {
            $data[$property] = $this->$property;
        }
        return json_encode($data);
    }

    // return un serialized object state
    public function unSerialised($json_string)
    {
        $data = json_decode($json_string, true);
        foreach ($data as $key => $values) {
            $this->$key = $values;
        }
        return true;
    }

    // abstract function to get the social login start auth url
    abstract public function getLoginStartUrl($redirectUrl);

    // get array of GET/POST argurments posted from the social network
    public function getFinalExtraInputs()
    {
        return array();
    }

    // check if the social login is completed
    abstract public function completeLogin($extraInputs = array());

    // Get the social network user id if success
    public function getUserId()
    {
        $profile = $this->getUserProfile();
        return $profile['userid'];
    }

    // Get the full detail of the user from the social network
    abstract public function getUserProfile();

    // this is to check if configurations is set
    public function isConfigured()
    {
        return true;
    }

    public function isActive()
    {
        if (!$this->isConfigured()) {
            return false;
        }
        return true;
    }

}