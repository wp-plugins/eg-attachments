<?php
header('Content-type: '.$_GET['mime']);
$url = pathinfo($_GET['url']);
$filename = $url['basename'];
header('Content-Disposition: attachment; filename="'.$filename.'"');
readfile($_GET['url']);
?>