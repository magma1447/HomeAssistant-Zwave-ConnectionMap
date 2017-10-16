# HomeAssistant-Zwave-ConnectionMap
Draws a map of the Z-wave mesh network using Graphviz

## Installation
`apt-get install php5-cli libgv-php5 graphviz`
Package graphviz is only needed to convert the dot file into an image.
We will need to enable the extension as well. This is done by:
`echo extension=gv.so > /etc/php5/mods-available/gv.so && php5enmod gv`
The alternative is to reconfigure php.ini to allow dynamically loading of modules.

## Usage
`php -f zwave-map.php <OZW.log> <zwcfg.xml>`  
Stores output as zwave-map.dot

Generate image with for example
`cat zwave-map.dot |dot -Tsvg -ozwave-map.svg`
or
`cat zwave-map.dot |dot -Tpng -ozwave-map.png`

