<?php

require_once('GraphViz.php');


if($argc !== 4) {
	die("Usage: {$argv[0]} OZW.log zwcfg.xml image.svg\n");
}
$ozwLog = $argv[1];
$zwcfg = $argv[2];
$imageFilename= $argv[3];
$controllerId = 1;



function GetNodeColor($hops) {
	if($hops === 1) {
		return 'forestgreen';
	}
	else if($hops === 2) {
		return 'darkturquoise';
	}
	else if($hops === 3) {
		return 'gold2';
	}
	else if($hops === 4) {
		return 'darkorange';
	}
	else if($hops === 5) {
		return 'red';
	}
	return 'black';
	
}

function GetEdgeColor($hops) {
	if($hops === 1) {
		return 'forestgreen';
	}
	else if($hops === 2) {
		return 'darkturquoise';
	}
	else if($hops === 3) {
		return 'gold2';
	}
	else if($hops === 4) {
		return 'darkorange';
	}
	else if($hops === 5) {
		return 'red';
	}
	return 'black';
}




$nodes = array();

echo "Reading XML\n";
$xml = file_get_contents($zwcfg);
$xml = new SimpleXMLElement($xml);
foreach($xml->Node as $v) {
	$id = (int) $v['id'];
	$name = $v['name'];
	$name = reset($name);	// No clue why I get an array back

	if(empty($name)) {
		$name = "{$v->Manufacturer['name']} {$v->Manufacturer->Product['name']}";
	}
	echo "{$id} => {$name}\n";

	$nodes[$id]['name'] = $name;
}
/*
$fixed = array(1,2,3,4,5,6,9,10,13,15,17,18,19,20,21);
foreach($fixed as $n) {
	$nodes[$n]['name'] = $n;
}
*/


/*
2017-10-14 12:02:31.336 Info, Node001,     Neighbors of this node are:
2017-10-14 12:02:31.336 Info, Node001,     Node 2
2017-10-14 12:02:31.336 Info, Node001,     Node 3
*/
echo "Reading OZW log\n";
$buf = file_get_contents($ozwLog);
$buf = explode(PHP_EOL, $buf);
foreach($buf as $line) {

	if(preg_match('/.*Info, Node([0-9]{3}), +Neighbors of this node are:$/', $line, $matches) === 1) {
		$node = (int) $matches[1];

		echo $line . PHP_EOL;

		if(!isset($nodes[$node])) {
			die("Node {$node} not found in xml\n");
		}

		$nodes[$node]['neighbors'] = array();
	}
	else if(preg_match('/.*Info, Node([0-9]{3}), +Node ([0-9]+)$/', $line, $matches) === 1) {
		$node = (int) $matches[1];
		$neighbor = (int) $matches[2];
	
		echo $line . PHP_EOL;

		if(!isset($nodes[$node])) {
			die("Node {$node} not initialized\n");
		}
		if(!isset($nodes[$node]['neighbors'])) {
			echo "WARNING: {$node} -> {$neighbor} listed before a list header, ignoring\n";
			continue;
		}

		if(!in_array($neighbor, $nodes[$node]['neighbors'])) {
			$nodes[$node]['neighbors'][] = $neighbor;
		}
	}
}


echo "Calculating hops\n";
$nodes[$controllerId]['hops'] = 0;	// The controller obviously has 0 hops
// Z-wave supports max 4 hops
for($maxHops = 1 ; $maxHops <= 4 ; $maxHops++) {
	foreach($nodes as $id => $n) {
		if(isset($n['hops'])) {
			continue;
		}

		if(!isset($n['neighbors'])) {	// Should not happen, this is a workaround
			echo "  WARNING: Node {$id} has no neighbors\n";
			$nodes[$id]['hops'] = 5;
			continue;
		}

		$hops = FALSE;
		foreach($n['neighbors'] as $neighbor) {
			if(!isset($nodes[$neighbor]['hops'])) {
				continue;
			}
			if($hops === FALSE || $nodes[$neighbor]['hops']+1 < $hops) {
				$hops = $nodes[$neighbor]['hops']+1;
			}
		}
		if($hops <= $maxHops) {
			$nodes[$id]['hops'] = $hops;
			echo "  {$id} has {$hops} hops to the controller\n";
		}
	}
}



echo "Rendering graph\n";
$gv = new Image_GraphViz();
foreach($nodes as $id => $n) {
	$attributes = array(
		'label' => $n['name'],
		'color' => GetNodeColor($n['hops']),
	);
	if($id === $controllerId) {
		$attributes = array_merge($attributes, array(
				'fontcolor' => 'white',
				'fillcolor', 'gray50',
				'color' => 'black',
				'style' => 'bold,filled',
			)
		);
	}
	$gv->addNode($id, $attributes);
}

$addedEdges = array();
foreach($nodes as $id => $n) {
	if(empty($n['neighbors'])) {
		echo "  WARNING: Node {$id} still doesn't have any neighbors (actually expected now)\n";
		continue;
	}


	foreach($n['neighbors'] as $neighbor) {
		// Sort nodes and check that the connection isn't already drawn
		$n1 = min(array($id, $neighbor));
		$n2 = max(array($id, $neighbor));
		if($n1 === $n2) {	// It seems weird that this happens
			continue;
		}
		$edge = "{$n1}:{$n2}";
		if(isset($addedEdges[$edge])) {
			continue;
		}
		$addedEdges[$edge] = TRUE;
		// --

		$attributes = array('dir' => 'none');
		// Set color depending on number of hops to the controller
		$hops = min(array($nodes[$n1]['hops'], $nodes[$n2]['hops']));
		$attributes['color'] = GetEdgeColor($hops+1);
		// Dash connections that aren't the shortest path
		// If the difference is 1, it's the shortest path for one of them
		$solid = (abs($n['hops'] - $nodes[$neighbor]['hops']) == 1);
		if(!$solid) {
			$attributes['style'] = 'dashed';
		}

		$gv->addEdge(array($id => $neighbor), $attributes);


	}
}
$ext = pathinfo($imageFilename, PATHINFO_EXTENSION);
$image = $gv->fetch($ext);
file_put_contents($imageFilename, $image);
echo "Image saved as {$imageFilename}\n";

