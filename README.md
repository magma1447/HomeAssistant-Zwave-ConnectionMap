# HomeAssistant-Zwave-ConnectionMap
Draws a map of the Z-wave mesh network using Graphviz

## Installation
`apt-get install php5-cli libgv-php5 graphviz`
`echo extension=gv.so > /etc/php5/mods-available/gv.so && php5enmod gv`

## Usage
`php -f zwave-map.php <OZW.log> <zwcfg.xml>`
Stores output as zwave-map.dot
Generate image with for example
`cat zwave-map.dot |dot -Tsvg -ozwave-map.svg`
or
`cat zwave-map.dot |dot -Tpng -ozwave-map.png`

