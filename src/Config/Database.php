<?php
use RedBeanPHP\R;

define("SECRET","aG91c2dpcmxfdG9rZW5fZm9yX2F1dGhlbnRpY2F0aW9u");
define("COUCHDB_URL","http://root:root@127.0.0.1:5984/");

R::setup('mysql:host=localhost;dbname=pushkanmit', 'root', '');
R::setAutoResolve( TRUE );