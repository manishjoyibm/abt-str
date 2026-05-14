<?php
// enable, adjust and copy this code for each store you run
// Store #0, default one
//if (isHttpHost("example.com")) {
//    $_SERVER["MAGE_RUN_CODE"] = "default";
//    $_SERVER["MAGE_RUN_TYPE"] = "store";
//}


function isHttpHost($host)
{
    if (!isset($_SERVER['HTTP_HOST'])) {
    return false;
    }
    if(preg_match("/^(.*)$host/", $_SERVER['HTTP_HOST'])) {
        return true;
    }
    return false;
}

if (isHttpHost("secure.abbottstore.com")){
    $_SERVER["MAGE_RUN_CODE"] = "abbott";
    $_SERVER["MAGE_RUN_TYPE"] = "store";
} elseif (isHttpHost("secure.similacstore.com")){
    $_SERVER["MAGE_RUN_CODE"] = "similac";
    $_SERVER["MAGE_RUN_TYPE"] = "store";
} elseif (isHttpHost("secure.glucernastore.com")){
    $_SERVER["MAGE_RUN_CODE"] = "glucerna";
    $_SERVER["MAGE_RUN_TYPE"] = "store";
} elseif (isHttpHost("secure.similac.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "new_similac";
    $_SERVER["MAGE_RUN_TYPE"] = "store";
} elseif (isHttpHost("secure.pedialyte.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "pedialyte";
    $_SERVER["MAGE_RUN_TYPE"] = "store";
}

