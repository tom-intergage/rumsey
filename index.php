<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
$error = false;
$params = $_GET;

if ($params['view']) :
    $odta = "bVQ%3DGMJE%26PVQ%3D-E";
    $urlBase = "http://hbofeeds.booking-system.net/";
    if ($params['view'] == 'availability') :
        $url = $urlbase . 'HBO_Availability_XML.asp?odta='.$odta;
    
    elseif ($params['view'] == 'offers') :
        $url = $urlbase . 'HBO_Prices_XML.asp?odta='.$odta;

    else :
        $error == true;
    
    endif;

    if (!$error) :
        $xml = simplexml_load_file($url);
        $json = json_encode($xml,JSON_PRETTY_PRINT);
        print_r($json);
    endif;

else :
    $error = true;
endif;

if ($error) {
    echo "{\"Error\":\"No Views Specified\"}";
}

?>