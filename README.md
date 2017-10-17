# HomeAssistant-Zwave-ConnectionMap
Draws a map of the Z-wave mesh network using Graphviz.

This should be compatible with anything based on OpenZWave that has a OZW_Log.txt and a zwcfg_0xfac5e970.xml file I guess, but I haven't tried it on anything else but [Home Assistant](https://home-assistant.io/).

## Installation
Start by installing required packages. The below command is based on Debian Jessie.  
`apt-get install php5-cli php-pear graphviz`

Either get GraphViz.php from [GitHub](https://github.com/pear/Image_GraphViz/blob/trunk/Image/GraphViz.php) or install it via pear, `pear install Image_GraphViz`.

In current stable (stretch) the first package would be *php7-cli*.

Note that it doesn't have to be installed on the same server as your Home Assistant. You can install it somewhere else and just copy the two required files that are needed to generate the connection graph.

## Usage
The controller is hard coded as Node 001. If this isn't correct, it can be changed in the source code around line 12.  
`php -f zwave-map.php <OZW.log> <zwcfg.xml> <image.svg>`  

