<?php
#dl from https://gist.github.com/oliveratgithub/0bf11a9aff0d6da7b46f1490f86a71eb/
$data = file_get_contents(__DIR__."/emojiAll.json");

$all = json_decode($data);

$newList = new stdClass();
foreach($all->emojis AS $emoji){
	if(strpos($emoji->shortname, "skin_tone") !== false)
		continue;
					
	$name = trim($emoji->shortname, ":");
	$newList->$name = $emoji->emoji;
}

file_put_contents(__DIR__."/emojis.json", json_encode($newList, JSON_UNESCAPED_UNICODE));