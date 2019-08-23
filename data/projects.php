<?php

$c_ProjectsPath = './data/projects/';
$c_Projects = array();
foreach (scandir($c_ProjectsPath, SCANDIR_SORT_ASCENDING) as $filename)
{
	if (is_dir($c_ProjectsPath . $filename) || pathinfo($filename, PATHINFO_EXTENSION) != 'json')
		continue;

	$fh = fopen($c_ProjectsPath . $filename, "r");
	if (!$fh)
		continue;

	$project = json_decode(fread($fh, filesize($c_ProjectsPath . $filename)), true);
	fclose($fh);
	if (!$project)
	{
		error_log($filename . " is unparseable.");
		continue;
	}

	$dep_sections = array();
	foreach ($project['dependencies'] as $name => $deps)
		$dep_sections[] = $name;

	$c_Projects[$project['code']] = array(
		'name' => $project['name'],
		'url' => $project['url'],
		'sections' => $dep_sections
	);

}

?>
