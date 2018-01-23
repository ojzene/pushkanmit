<?php

/**
 * Created by PhpStorm.
 * User: funmi
 * Date: 3/10/17
 * Time: 12:32 PM
 */
class AuthFactory
{
    // return social login object with their names
    public static function getSocialLoginObj($network,$options=array(),$logger=null){
        $network  = preg_replace('![^a-z0-9]!i','',$network);
        if ($network == ''){
            throw new Exception("Social Network detail cannot be null");
        }
        $class = '\\Lib\\'.ucfirst($network);
        if (!class_exists($class)){
            throw new Exception("Integration with a class name %s not found",$class);
        }

        $obj  =new $class($options);
        return $obj;
    }

}