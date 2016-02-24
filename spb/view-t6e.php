<?php
include('php/autoload.php');
$validItem = isset($_GET['id']);
$template = null;
if($validItem){
	$id = $_GET['id'];
	$templates = $db->getTemplates("SELECT * FROM Templates WHERE templateId = ? AND reviewState <> 'd'", array($_GET['id']), array(SQLITE3_TEXT));
	if(count($templates) > 0){
		$template = $templates[0];
	} else{
		$validItem = false;
	}
}

echo $twig->render('view-t6e.html', array('title' => 'Template', 'isValidItem' => $validItem, 't' => $template, 
	'lastPage' => isset($_SESSION['lastT6eGalleryPage']) ? $_SESSION['lastT6eGalleryPage'] : 't6e-gallery.php'));
$db->close();
?>