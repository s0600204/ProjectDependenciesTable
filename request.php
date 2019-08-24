<?php
require_once './utils.php';

if (!isset($_POST['dep_code'])
	|| !mb_detect_encoding($_POST['dep_code'], 'ASCII', true)
) {
	header("HTTP/1.0 400 Bad Request");
	exit;
}

function determineVersionStates($versions, $latest, $minimum)
{
	$states = array();
	foreach ($versions as $distro => $version)
	{
		switch(version_compare2($version, $latest))
		{
		case -1: // Old
			if ($minimum && version_compare2($version, $minimum) == -1)
				$class = "lessthanminimum";
			else
				$class = "oldversion";
			break;

		case 0: // Latest
			$class = "latestversion";
			break;

		case 1: // Newer
		default:
			$class = "nonstandardversion";
			break;
		}
		$states[$distro] = $class;
	}
	return $states;
}

$dependency_code = $_POST['dep_code'];
$min_required = isset($_POST['min_req']) ? $_POST['min_req'] : False;

// Load from cache, if it exists and isn't too old.
$cache_filename = './data/cache/' . $dependency_code . '.json';
$cache_ttl = 60 * 60; // seconds in one hour.
if (file_exists($cache_filename) && time() - $cache_ttl < filemtime($cache_filename))
{
	$content = load_json($cache_filename);
	if ($content)
	{
		$content['states'] = determineVersionStates($content['versions'], $content['latestVersion'], $min_required);
		echo json_encode($content);
		exit;
	}
}

// Else, request it from Repology
$ch = curl_init();
curl_setopt_array($ch, array(
	CURLOPT_HEADER => False,
	CURLOPT_RETURNTRANSFER => True,
	CURLOPT_URL => "https://repology.org/api/v1/project/" . $dependency_code
));

$response = curl_exec($ch);

if (curl_errno($ch))
{
	header("HTTP/1.0 500 Internal Server Error");
	error_log("Error in cURL whilst processing request:\n\t" . curl_error($ch));
	exit;
}

$response_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

if ($response_code != 200)
{
	header("HTTP/1.0 500 Internal Server Error");
	error_log("Recieved following response code from Repology: " . $response_code);
	exit;
}

$response = json_decode($response, true);

if (empty($response))
{
	echo json_encode(array(
		"code" => $dependency_code,
		"status" => False
	));
	exit;
}

$versionsByDistro = array();
$latestVersion = NULL;

foreach ($response as $package)
{
	if (in_array($package['status'], array('rolling', 'legacy', 'ignored')))
		continue;

	if ($package['status'] == 'newest')
		$latestVersion = $package['version'];

	$distro = $package['repo'];
	if (!isset($versionsByDistro[$distro])
		|| version_compare2($package['version'], $versionsByDistro[$distro]) == 1)
	{
		$versionsByDistro[$distro] = $package['version'];
	}
}

$distros = load_json(__DIR__ . "/data/distros.json");
foreach ($distros as $distro)
{
	if (!isset($distro['releases']))
	{
		if (isset($distro['hard'])
			&& isset($distro['hard'][$dependency_code])
		)
			$versionsByDistro[md5($distro['name'])] = $distro['hard'][$dependency_code];
		continue;
	}

	foreach ($distro['releases'] as $release)
		if (!isset($release['code'])
			&& isset($release['hard'])
			&& isset($release['hard'][$dependency_code])
		)
			$versionsByDistro[md5($release['name'])] = $release['hard'][$dependency_code];
}

ksort($versionsByDistro);

$output = array(
	"code" => $dependency_code,
	"status" => True,
	"latestVersion" => $latestVersion,
	"versions" => $versionsByDistro,
);

// Save in cache, overwriting previous cachefile (if one exists).
file_put_contents($cache_filename, json_encode($output));

$output["states"] = determineVersionStates($versionsByDistro, $latestVersion, $min_required);
echo json_encode($output);

?>
