<?php
/*********************************************************************************
This is where you should put path, config and so on

Released under the WTFPL http://www.wtfpl.net/

sly sylvain@letuffe.org
**********************************************************************************/
// pick one xapi url endpoint :
$config['xapi_url']="http://api.openstreetmap.fr/xapi-without-meta?";
//$config['xapi_url']="http://overpass.osm.rambler.ru/xapi?";

// pick one Overpass API endpoint :
//$config['oapi_url']="http://api.openstreetmap.fr/oapi/interpreter?";
//$config['oapi_url']="http://overpass.osm.rambler.ru/cgi/interpreter?";                                                                               
$config['oapi_url']="http://www.overpass-api.de/api/interpreter?";


// Either absolute, or relative to the directory this file is in
// just set "osmconvert" if osmconvert is in your path
$config['osmconvert_path']="osmconvert";


?>