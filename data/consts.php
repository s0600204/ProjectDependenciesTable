<?php

$c_Dependencies = load_json(__DIR__ . "/dependencies.json");
$c_Distros = load_json(__DIR__ . "/distros.json");

function load_json ($file) {
	$fcontents = json_decode(file_get_contents($file), true);

	if ($fcontents !== NULL)
		return $fcontents;
	else
	{
		print($file . " is not a valid JSON file!");
		die();
	}
};


?>
