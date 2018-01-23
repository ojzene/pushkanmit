<?php

/**
 * Created by PhpStorm.
 * User: funmi
 * Date: 3/10/17
 * Time: 10:49 AM
 */
use \Psr\Log\LogLevel as LogLevel;
trait ApplicationLogger
{
    use \Psr\Log\LoggerTrait;

    protected $logger;

    public function log($level, $message, array $context = array())
    {
//        if(property_exists($this,'application') && is_object($this->application))
    }

}