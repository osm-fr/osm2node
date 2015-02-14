osm2node
========
A wrapper from a XAPI of Overpass API to provide converted output (with osmconvert) to nodes only, the output can either be osm or gpx 
(this is an hugly but working converter that osmconvert cannot provide)

Pass de special tweaked parameter in the url : &magouillefixme
like :
http://api.openstreetmap.fr/osm2nodegpx?*[bbox=5.71,45.37626,6.2752533,45.7301497][fixme=*]&magouillefixme
and all th fixme=* will replace the poi's name

I can be usefull when you want restaurant or hôtels drawn as polygons in the osm database to be converted to a virtual node in the centroid.
(easier to process when the client tool does not provide this simplification)

Read the code for more ;-)

Note : You need osmconvert to make it work : http://wiki.openstreetmap.org/wiki/Osmconvert

``
wget -O - http://m.m.i24.cc/osmconvert.c | cc -x c - -lz -O3 -o osmconvert
``

should download it and compile it

Install
=======
* Just put those files (including the .htaccess for apache) into a directory accessible by an url.
* copy the config-sample.php to config.php and adapt (or symlink if the defaut config file suits you)

