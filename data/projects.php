<?php
require_once './utils.php';

$g_Dependencies = array();
$g_Distros = load_json(__DIR__ . "/distros.json");
$g_ProjectsPath = './data/projects/';
$g_Projects = array();

$g_ProjectCode = NULL;
if (isset($_GET['project']) && mb_detect_encoding($_GET['project'], 'ASCII', true))
	$g_ProjectCode = $_GET['project'];

$g_SelectedSection = NULL;
if (isset($_GET['section']) && mb_detect_encoding($_GET['section'], 'ASCII', true))
	$g_SelectedSection = $_GET['section'];

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

if (!$g_ProjectCode || !in_array($g_ProjectCode, array_keys($g_Projects)))
	$g_ProjectCode = array_keys($g_Projects)[rand(0, count($g_Projects) - 1)];

$project = load_json($g_ProjectsPath . $g_ProjectCode . '.json');
if (in_array($g_SelectedSection, $g_Projects[$g_ProjectCode]['sections']))
	$g_Dependencies = $project['dependencies'][$g_SelectedSection];
else
	$g_Dependencies = $project['dependencies'][$g_Projects[$g_ProjectCode]['sections'][0]];

unset($project);

?>
