<?php
/*********************************************************************************
Un petit wrapper à osmconvert http://wiki.openstreetmap.org/wiki/Osmconvert.

On l'appel par http://x/osm2node?[Syntaxe XAPI]
On l'appel avec la syntaxe de XAPI, il fait lui même un appel XAPI
et renvoi tout objet osm simplifié en noeud (un noeud reste un noeud, mais way et relation 
sont converti en un noeud positionné au centre approximatif).

On peut aussi appelé le script avec "http://x/osm2nodegps?[Syntaxe XAPI]" et il fourni une conversion 
de osm (noeud) en waypoint gpx

Released under the WTFPL http://www.wtfpl.net/

sly sylvain@letuffe.org 
**********************************************************************************/
require_once("config.php");

function osmnodes2gpx($osm_xml)
{
  function c($txt)
  {
    return htmlspecialchars($txt,ENT_COMPAT,'UTF-8');
  }
  $osm = simplexml_load_string($osm_xml);
  if (isset($osm->node))
  {
    foreach ( $osm->node as $node )
    {
      $wpts="\t<wpt lat=\"$node[lat]\" lon=\"$node[lon]\">\n";
      if (isset($node[timestamp]))
	$gpx_tags="\t\t<time>$node[timestamp]</time>\n";
      else
        $gpx_tags="";
      $gpx_tags_extension="";
      if (isset($node->tag))
      {
	$gpx_tags_extension="\t\t\t<extensions>\n";
	$at_least_one=false;
	foreach ( $node->tag as $tag )
	{
	  $tag['k']=c($tag['k']);
	  $tag['v']=c($tag['v']);
	  
	  // those tags are of low interest
	  if ($tag['k']=="created_by" or $tag['k']=="source")
	    break;
	  
	  if ($tag['k']=="name")
	    $gpx_tags.="\t\t<name>$tag[v]</name>\n";
	  else if ($tag['k']=="ele")
	    $gpx_tags.="\t\t<ele>$tag[v]</ele>\n";
	  else if ($_GET['magouillefixme'] and $tag['k']=="fixme")
	    $gpx_tags.="\t\t<name>$tag[v]</name>\n";
	  else
	    $gpx_tags_extension.="\t\t\t\t<tag k=\"$tag[k]\" v=\"$tag[v]\"/>\n";
	  $at_least_one=true;
	}
	$gpx_tags_extension.="\t\t\t</extensions>\n";
	if ($at_least_one)
	  $wpts.="$gpx_tags.$gpx_tags_extension\t</wpt>\n";
        else
          $wpts="";
      }
      $gpx_wpts.=$wpts;
    }
  } 
    
    $gpx_en_tete='<?xml version="1.0" encoding="utf-8"?>'."\n".'<gpx creator="osm2nodesgpx" version="1.0" xmlns="http://www.topografix.com/GPX/1/0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">'."\n";
    $gpx_end="</gpx>";
  
  return $gpx_en_tete.$gpx_wpts.$gpx_end;
}

// On prépare l'appel à osmconvert
$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
);

// Let's check what type of call we have, we suppose that if the form is ?data=* we face a overpass API syntax query, else a xapi syntax query
if (isset($_GET['data']))
{
  $url_to_open=$config['oapi_url'];
  $detected_syntax="Overpass API";
}
else
{
  $url_to_open=$config['xapi_url'];
  $detected_syntax="Xapi";
}

$process = proc_open($config['osmconvert_path']." - --drop-relations --all-to-nodes --out-osm", $descriptorspec, $pipes);


// We just forward the call to the other endpoint just like we where asked
if (!($xapi_p=fopen($url_to_open.$_SERVER['QUERY_STRING'],"r")))
  die("Your request (detected as $detected_syntax syntax) returned no answer. The call was : \n".$url_to_open.$_SERVER['QUERY_STRING']."\n"); 
$osm="";
while (!feof($xapi_p)) 
{
  // Au fûr et à mesure on nourri, en flux, osmconvert
  $osm = fread($xapi_p, 8192);
  fwrite($pipes[0], $osm);
}

fclose($pipes[0]);

// on récupère le fichier osm résultant en retirant les noeuds (pas super proprement) qui n'ont pas de tags
// FIXME c'est pas parfait car osmconvert aurait pu avoir une option pour ça
// Donc on se retrouve avec des noeuds qui pourrait avoir un created_by / source ou autre tag sans rapport avec
// la requête xapi initiale car ils étaient le composant d'un way qui lui a été converti en noeud
$osm_node_only="";
while (!feof($pipes[1])) 
{
  $line=fgets($pipes[1]);
  if (!preg_match("/<node.*\/>$/",$line))
    $osm_node_only.=$line;
}
  fclose($pipes[1]);

// Choix du format renvoyer osm ou gpx
if ( preg_match("/^\/osm2nodegpx/",$_SERVER['REQUEST_URI'])) // On veut des noeuds au format gpx
{
  $nom_fichier="osm2nodegpx.gpx";
  $content_type="application/gpx";
  $xml=osmnodes2gpx($osm_node_only);
}
else // et par défaut, osm
{
  $xml=$osm_node_only;
  $nom_fichier="osm2node.osm";
  $content_type="text/xml";
}
//header("Content-disposition: attachment; filename=$nom_fichier");
//header("Content-Type: $content_type; charset=utf-8"); // rajout du charset
//header("Content-Transfer-Encoding: binary");
print($xml);

?>