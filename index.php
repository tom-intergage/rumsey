<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: http://rumseyofsandbankscouk.14-1.a01.co.uk");

$params = $_GET;
if ($params) {
    $odta = "bVQ%3DGMJE%26PVQ%3D-E";
    $selectedMonth = $params['selectedMonth'];

    $pstring = "?odta=".$odta;

    $url = 'http://hbofeeds.booking-system.net/HBO_Availability_XML.asp?'.$pstring;
    $xml = simplexml_load_file($url);
    $json = json_encode($xml,JSON_PRETTY_PRINT);
    print_r($json);
}
else {
    echo "{\"Error\":\"No Parameters Supplied\"}";
}

?>