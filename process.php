<?php

$g_VersionsByDistro = array();
$g_LatestVersions = array();

$g_ch = curl_init();
curl_setopt($g_ch, CURLOPT_HEADER, False);
curl_setopt($g_ch, CURLOPT_RETURNTRANSFER, True);

$g_DistroList = array();
foreach ($c_Distros as $distro)
	if (isset($distro['releases']))
		foreach ($distro['releases'] as $release)
			$g_DistroList[] = $release['code'];
	else
		$g_DistroList[] = $distro['code'];

$g_LibraryList = array();
foreach ($c_Libraries as $library)
{
	$g_LibraryList[] = $library['code'];
	if (isset($library['alt-code']))
		foreach ($library['alt-code'] as $code)
			$g_LibraryList[] = $code;
}

foreach ($g_LibraryList as $library)
{
	curl_setopt($g_ch, CURLOPT_URL, "https://repology.org/api/v1/project/" . $library);
	$response = curl_exec($g_ch);

	if (curl_errno($g_ch))
	{
		echo 'Curl error: ' . curl_error($g_ch) . "<br/>\n";
		continue;
	}

	$http_response_code = curl_getinfo($g_ch, CURLINFO_RESPONSE_CODE);
	if ($http_response_code != 200)
	{
		echo "Unexpected response code: " . $http_response_code . "<br/>\n";
		continue;
	}

	$response = json_decode($response, true);
	foreach ($response as $package)
	{
		if (in_array($package['status'], array('rolling', 'legacy')))
			continue;

		if ($package['status'] == 'newest' && !isset($g_LatestVersions[$library]))
			$g_LatestVersions[$library] = $package['version'];

		if (in_array($package['repo'], $g_DistroList))
		{
			$distro = $package['repo'];
			if (!isset($g_VersionsByDistro[$distro]))
				$g_VersionsByDistro[$distro] = array($library => $package['version']);
			else if (!isset($g_VersionsByDistro[$distro][$library])
				|| (version_compare($g_VersionsByDistro[$distro][$library], $package['version']) == -1 && $package['status'] != 'ignored')
				|| $package['status'] == 'newest')
			{
				$g_VersionsByDistro[$distro][$library] = $package['version'];
			}
		}
	}
}

curl_close($g_ch);

?>
