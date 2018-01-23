<?php
use RedBeanPHP\R;

define("SECRET","aG91c2dpcmxfdG9rZW5fZm9yX2F1dGhlbnRpY2F0aW9u");
define("COUCHDB_URL","http://root:root@127.0.0.1:5984/");

// R::setup('mysql:host=localhost;dbname=wearegr3_greenpower', 'wearegr3_manager', 'Manager2017!');
R::setup('mysql:host=localhost;dbname=twelvegiftsdevice', 'root', '');
R::setAutoResolve( TRUE );