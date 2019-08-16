<?php
require_once './utils.php';

if (!isset($_POST['dep_code'])
	|| !mb_detect_encoding($_POST['dep_code'], 'ASCII', true)
) {
	header("HTTP/1.0 400 Bad Request");
	exit;
}

$dependency_code = $_POST['dep_code'];
$min_required = isset($_POST['min_req']) ? $_POST['min_req'] : False;

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
	if (in_array($package['status'], array('rolling', 'legacy')))
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

ksort($versionsByDistro);

$versionState = array();
foreach ($versionsByDistro as $distro => $version)
{
	switch(version_compare2($version, $latestVersion))
	{
	case -1: // Old
		if ($min_required && version_compare2($version, $min_required) == -1)
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
	$versionState[$distro] = $class;
}

echo json_encode(array(
	"code" => $dependency_code,
	"status" => True,
	"latestVersion" => $latestVersion,
	"versions" => $versionsByDistro,
	"states" => $versionState,
));

?>
