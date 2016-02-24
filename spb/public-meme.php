<?php
header('Content-Type: image/jpg');
include('php/autoload.php');
$MINUTE = 60;
$interval = 30 * $MINUTE;

$time = time();
$seededTime = $time - ($time % $interval);

if(!file_exists("img/output/Temp-$seededTime.jpg")){
	$oldFiles = glob("img/output/Temp-*.jpg");
	foreach ($oldFiles as $file) {
		unlink($file);
	}
	$templateQuery = "SELECT * FROM Templates WHERE reviewState = 'a' ORDER BY random()";
	$sourceQuery = "SELECT * FROM SourceImages WHERE reviewState = 'a' ORDER BY random()";

	$templates = $db->getTemplates($templateQuery);
	$sources = $db->getSourceImages($sourceQuery);

	$sourcePaths = array();
	for($i = 0; $i < count($sources); $i++){
		array_push($sourcePaths, $sources[$i]->getImage());
	}
	$generator = new ImageGenerator('', '');
	$template = $templates[mt_rand(0, count($templates) -1)];
	$img = $generator->generate($template->getPositions(), $template->getImage(), $template->getOverlayImage(), $sourcePaths);
	imagejpeg($img, "img/output/Temp-$seededTime.jpg");
	imagejpeg($img);
} else{
	$img = imagecreatefromjpeg("img/output/Temp-$seededTime.jpg");
	imagejpeg($img);
}

$db->close();
?>