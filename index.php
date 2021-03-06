<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
error_reporting(0);
//REDEPLOY TEST

//GENERAL RULES FOR THE VIEW AND ERRORS
$error = false;
$view = $_GET['view'];
if ($_GET['pid']) $pid = $_GET['pid'];

if ($view == 'property') {
    $filename = $view.'_'.$pid.'.json';
}

else {
    $filename = $view.'.json';
}

//IF WE HAVE SPECIFIED A VIEW
if ($view) :
    
function process($view) {

    $odta = "bVQ%3DGMJE%26PVQ%3D-E";
    $urlBase = "http://hbofeeds.booking-system.net/";
    if ($view == 'availability') :
        $url = $urlBase . 'HBO_Availability_XML.asp?odta='.$odta;
        $xmlA = simplexml_load_file($url,null, LIBXML_NOCDATA);
        $output = $xmlA;

    elseif ($view == 'wakeup') :
            $output = '{"Message":"I am awake!"}';
          
       
    elseif ($view == 'prices') :
        $xmlB = simplexml_load_file($urlBase . 'HBO_Prices_XML.asp?odta='.$odta,null, LIBXML_NOCDATA);
        $output = $xmlB;
    elseif ($view == 'combined') :
        $xmlA = simplexml_load_file($urlBase . 'HBO_Availability_XML.asp?odta='.$odta,null, LIBXML_NOCDATA);
        $xmlB =  simplexml_load_file($urlBase . 'HBO_Prices_XML.asp?odta='.$odta, null,LIBXML_NOCDATA);   
        $jsona = json_encode($xmlA);  
        $ja = (array) json_decode($jsona);
        $jsonb = json_encode($xmlB); 
        //COMMIT
        $jb = (array) json_decode($jsonb);
        $output = array();
        $i = 0;
        foreach ($ja['property'] as $k => $v) {
            $keyA = $ja['property'][$k]->propertyid;
            $keyB = $jb['property'][$k]->propertyid;
            if ($keyA == $keyB) {
                $output[$i] = $ja['property'][$k];
                $output[$i]->propertyPrices = $jb['property'][$k]->propertyPrices;
                if ($jb['property'][$k]->propertyOneOffBreaks) 
                $output[$i]->propertyOneOffBreaks = $jb['property'][$k]->propertyOneOffBreaks;
                $i++;
            }
        }

        elseif ($view == 'ids') :
            $url = $urlBase . 'HBO_Availability_XML.asp?odta='.$odta;
            $xmlA = simplexml_load_file($url,null, LIBXML_NOCDATA);
            $jsona = json_encode($xmlA); 
            $ja = (array) json_decode($jsona);
            $output = array();
            $i = 0;
            foreach ($ja['property'] as $k => $v) {
                $keyA = $ja['property'][$k]->propertyid;
                $output[$i]->propertyid = $ja['property'][$k]->propertyid;
                $output[$i]->propertyname = $ja['property'][$k]->propertyname;
                $i++;
            }
        

    elseif ($view == 'offers') :
        $xmlA = simplexml_load_file($urlBase . 'HBO_Availability_XML.asp?odta='.$odta,null, LIBXML_NOCDATA);
        $xmlB = simplexml_load_file($urlBase . 'HBO_Prices_XML.asp?odta='.$odta,null, LIBXML_NOCDATA);   
        $jsona = json_encode($xmlA);  
        $ja = (array) json_decode($jsona);
        $jsonb = json_encode($xmlB);
        $jb = (array) json_decode($jsonb);
        $output = array();
        $i = 0;
        foreach ($ja['property'] as $k => $v) {
            $keyA = $ja['property'][$k]->propertyid;
            $keyB = $jb['property'][$k]->propertyid;
            if ($keyA == $keyB && $jb['property'][$k]->propertyOneOffBreaks) {
                $output[$i] = $ja['property'][$k];
                $output[$i]->propertyOneOffBreaks = $jb['property'][$k]->propertyOneOffBreaks;
                $i++;
            }
        }
        elseif ($view == 'property') :
            $pid = $_GET['pid'];
            $xml = simplexml_load_file($urlBase . 'HBO_Prices_XML.asp?odta='.$odta,null, LIBXML_NOCDATA);
            $jsona = json_encode($xml);  
            $ja = (array) json_decode($jsona);
            $output = array();
            $i = 0;      
            if ($pid) {
                $error = false;
                foreach ($ja['property'] as $k => $v) {
                    $key = $ja['property'][$k]->propertyid;
                    if ($key == $pid) {
                        $output[$i] = $ja['property'][$k];
                        $i++;
                    }
                }
                if ($i == 0) {
                    $error = true;
                }
            }
            else {
                $error = true;
            }
            
    else :
        $error = true;
    endif;

    if ($error !== true) :
        if ($view == 'property') {
            $f = $view.'_'.$pid.'.json';
        }
        
        else {
            $f = $view.'.json';
        }
        $j = json_encode($output,JSON_PRETTY_PRINT);
        $fp = fopen($f, 'w');
        fwrite($fp, $j);
        fclose($fp);
       print_r(file_get_contents($f));
    endif;
}

//IF WE HAVE A JSON FILE FOR THIS VIEW
if (file_exists($filename)) {
    //CURRENT TIME
    $n = date("F d Y H:i:s",time());
    $now = date_create($n);

    //FILE WRITE TIME
    $m = date("F d Y H:i:s",filemtime($filename));
    $mod = date_create($m);
    

    //DIFFERENCE BETWEEN THE TWO
    $diff = date_diff($now, $mod);

    //IN MINUTES    
    $minutes = $diff->format('%i');

    //IF THE FILE IS OLDER THAN TWO MINUTES GET IT AGAIN
    if ($minutes > 2 )
    process($view);

    
    //OTHERWISE JUST RETURN THE FILE 
    else {
        if ($view == 'property') {
            $f = $view.'_'.$pid.'.json';
        }
        
        else {
            $f = $view.'.json';
        }
        print_r(file_get_contents($f));
    }
    
}

//OTHERWISE MAKE ONE AND PRINT IT
else {
    process($view);
}

//WITHOUT A VIEW, SET ERRORS TO BE TRUE
else :
    $error = true;
endif;

//AN ERROR IN VALID JSON FOR WHEN A VIEW ISN'T SPECIFIED
if ($error == true) {
    echo "{\"Error\":\"Define a correct view\"}";
}

?>
