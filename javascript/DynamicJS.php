<?php
require "../system/connect.php";

header('Content-type: text/javascript; charset="utf-8"');

$DJS = new DynamicJSGUI();
$DJS->output();
?>