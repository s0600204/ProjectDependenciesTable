<?php
require_once './utils.php';

$g_Dependencies = array();
$g_Distros = load_json(__DIR__ . "/distros.json");
$g_ProjectsPath = './data/projects/';
$g_Projects = array();

$selected_project_code = NULL;
if (isset($_GET['project']) && mb_detect_encoding($_GET['project'], 'ASCII', true))
	$selected_project_code = $_GET['project'];

$selected_project_section = NULL;
if (isset($_GET['section']) && mb_detect_encoding($_GET['section'], 'ASCII', true))
	$selected_project_section = $_GET['section'];

foreach (scandir($g_ProjectsPath, SCANDIR_SORT_ASCENDING) as $filename)
{
	if (is_dir($g_ProjectsPath . $filename) || pathinfo($filename, PATHINFO_EXTENSION) != 'json')
		continue;

	$project = load_json($g_ProjectsPath . $filename);
	if (!$project)
		continue;

	$dep_sections = array();
	foreach ($project['dependencies'] as $name => $deps)
		$dep_sections[] = $name;

	$g_Projects[$project['code']] = array(
		'name' => $project['name'],
		'url' => $project['url'],
		'sections' => $dep_sections
	);
}

if (!$selected_project_code || !in_array($selected_project_code, array_keys($g_Projects)))
	$selected_project_code = array_keys($g_Projects)[rand(0, count($g_Projects) - 1)];

$project = load_json($g_ProjectsPath . $selected_project_code . '.json');
if (in_array($selected_project_section, $g_Projects[$selected_project_code]['sections']))
	$g_Dependencies = $project['dependencies'][$selected_project_section];
else
	$g_Dependencies = $project['dependencies'][$g_Projects[$selected_project_code]['sections'][0]];

unset($project);
unset($selected_project_code);
unset($selected_project_section);

?>
