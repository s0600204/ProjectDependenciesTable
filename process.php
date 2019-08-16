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

function process_response($ch, $dependency_code, &$latestVersion = NULL)
{
	$response = curl_multi_getcontent($ch);
	$response = json_decode($response, true);
	$versionsByDistro = array();

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

	return $versionsByDistro;
}

$g_mh = curl_multi_init();

$g_DistroList = array();
foreach ($c_Distros as $distro)
	if (isset($distro['releases']))
	{
		foreach ($distro['releases'] as $release)
			if (isset($release['code']))
				$g_DistroList[] = $release['code'];
	}
	else if (isset($distro['code']))
		$g_DistroList[] = $distro['code'];

$g_DependencyList = array();
foreach ($c_Dependencies as $dependency)
{
	$g_DependencyList[] = $dependency['code'];
	if (isset($dependency['alt-code']))
		foreach ($dependency['alt-code'] as $code)
			$g_DependencyList[] = $code;
}

// Set up the individual cURL handlers
$g_DependencyCurls = array();
foreach ($g_DependencyList as $dependency_code)
{
	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_HEADER => False,
		CURLOPT_RETURNTRANSFER => True,
		CURLOPT_URL => "https://repology.org/api/v1/project/" . $dependency_code
	));
	curl_multi_add_handle($g_mh, $ch);
	$g_DependencyCurls[$dependency_code] = $ch;
}

$g_VersionsByDistro = array();
$g_LatestVersions = array();

do {
	// Run the connection
	$status = curl_multi_exec($g_mh, $num_handles_active);


	// Block until activity
	curl_multi_select($g_mh);

	// Check for and deal with a completed response
	do {
		$info = curl_multi_info_read($g_mh, $msgs_in_queue);
		if ($info)
		{
			echo ". ";
			$ch = $info['handle'];

			$http_response_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
			if ($http_response_code == 200)
			{
				$dependency_code = basename(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
				$versions = process_response($ch, $dependency_code, $latestVersion);
				foreach ($versions as $distro => $version)
					if (in_array($distro, $g_DistroList))
					{
						if (!isset($g_VersionsByDistro[$distro]))
							$g_VersionsByDistro[$distro] = array();
						$g_VersionsByDistro[$distro][$dependency_code] = $version;
					}
				$g_LatestVersions[$dependency_code] = $latestVersion;
			}
			else
				echo "Unexpected response code: " . $http_response_code . "<br/>\n";

			// Remove this handle
			curl_multi_remove_handle($g_mh, $ch);
			$g_DependencyCurls[$dependency_code] = NULL;
		}
	} while ($msgs_in_queue > 0);

} while ($num_handles_active && $status == CURLM_OK);

// Remove any remaining handlers
foreach ($g_DependencyCurls as $code => $ch)
	if ($ch)
		curl_multi_remove_handle($g_mh, $ch);
unset($g_DependencyCurls);

curl_multi_close($g_mh);

?>
