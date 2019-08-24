<?php

$selected_project_code = NULL;
$selected_project_section = NULL;

if (isset($_GET['project'])
	&& mb_detect_encoding($_GET['project'], 'ASCII', true)
) {
	$selected_project_code = $_GET['project'];
}

if (isset($_GET['section'])
	&& mb_detect_encoding($_GET['section'], 'ASCII', true)
) {
	$selected_project_section = $_GET['section'];
}

$c_Dependencies = array();
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

if (!$selected_project_code || !in_array($selected_project_code, array_keys($c_Projects)))
	$selected_project_code = array_keys($c_Projects)[rand(0, count($c_Projects) - 1)];

$project = load_json($c_ProjectsPath . $selected_project_code . '.json');
if (in_array($selected_project_section, $c_Projects[$selected_project_code]['sections']))
	$c_Dependencies = $project['dependencies'][$selected_project_section];
else
	$c_Dependencies = $project['dependencies'][$c_Projects[$selected_project_code]['sections'][0]];

unset($project);
unset($selected_project_code);
unset($selected_project_section);

?>
