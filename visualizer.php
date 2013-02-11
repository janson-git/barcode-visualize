<?php
// CREATE ARRAY OF TEST DATA
$data = array();
srand(time());

for ($i = 0; $i < 1000; $i++) {
	$num   = rand(2000, 2500);
	$count = rand(0, 20);
	if ($count > 15) {
		$count = rand(0, 20);
	}
	array_push($data, array('val' => $num, 'count' => $count));
}


// AND NOW - VISUALIZE THIS DATA
require_once 'VisualizerClass.php';

$visualizer = new VisualizerClass();

$visualizer->setBackgroundColor('#FFFFFF');
$visualizer->setColorGradientImage('gradient_scale.jpg');
$visualizer->setResultImageWidth(400);
$visualizer->setResultImageHeight(20);

$visualizer->setDataArray($data);

$visualizer->setMinValueToDisplay(2300);
$visualizer->setMaxValueToDisplay(2500);

// AND NOW - CREATE IMAGE
$visualizer->createImage();

// OK. IN THIS POINT IMAGE MAY BE SAVED (and you can give $path to folder on it)
$visualizer->saveImage();

// OR - JUST SHOW RESULT
header("Content-Type: image/png");
$visualizer->drawImage();
