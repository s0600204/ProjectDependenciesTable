<?php

/**
 * `version_compare` considers `1`, `1.0`, and `1.0.0` to be unequal in
 * ascending order of greatness. This, then, removes all trailing '.0' from
 * the end of a version string.
 *
 * We cannot use `rtrim()`, as that removes characters, not strings.
 */
function version_compare2($a, $b)
{
	$a = explode('.', $a);
	$b = explode('.', $b);
	while (count($a))
		if (end($a) == '0') array_pop($a); else break;
	while (count($b))
		if (end($b) == '0') array_pop($b); else break;
	return version_compare(implode('.', $a), implode('.', $b));
}


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

$g_DependencyList = array();
foreach ($c_Dependencies as $dependency)
{
	$g_DependencyList[] = $dependency['code'];
	if (isset($dependency['alt-code']))
		foreach ($dependency['alt-code'] as $code)
			$g_DependencyList[] = $code;
}

foreach ($g_DependencyList as $dependency_code)
{
	curl_setopt($g_ch, CURLOPT_URL, "https://repology.org/api/v1/project/" . $dependency_code);
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

		if ($package['status'] == 'newest' && !isset($g_LatestVersions[$dependency_code]))
			$g_LatestVersions[$dependency_code] = $package['version'];

		if (in_array($package['repo'], $g_DistroList))
		{
			$distro = $package['repo'];
			if (!isset($g_VersionsByDistro[$distro]))
				$g_VersionsByDistro[$distro] = array($dependency_code => $package['version']);
			else if (!isset($g_VersionsByDistro[$distro][$dependency_code])
				|| (version_compare2($g_VersionsByDistro[$distro][$dependency_code], $package['version']) == -1 && $package['status'] != 'ignored')
				|| $package['status'] == 'newest')
			{
				$g_VersionsByDistro[$distro][$dependency_code] = $package['version'];
			}
		}
	}
}

curl_close($g_ch);

?>
