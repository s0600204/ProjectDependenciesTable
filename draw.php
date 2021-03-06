<?php
require_once './utils.php';

function generateGenericLink($addr, $text)
{
	return "<a href='$addr' class='ext-link'><span class='icon'></span>" . $text . "</a>";
}

function generateDependencyLink ($code, $name)
{
	return generateGenericLink("https://repology.org/metapackage/$code", $name);
}

function generateDistroReleaseLink($distro)
{
	if (isset($distro['link']))
		return generateGenericLink($distro['link'], $distro['name']);
	return $distro['name'];
}

function generateVersionText($dependency_code, $distro_code, $minRequired = False)
{
	global $g_LatestVersions;
	global $g_VersionsByDistro;
	if (!isset($g_VersionsByDistro[$distro_code]) || !isset($g_VersionsByDistro[$distro_code][$dependency_code]))
		return '<span class="version notavailable"><span>-</span></span>';

	switch (version_compare2($g_VersionsByDistro[$distro_code][$dependency_code], $g_LatestVersions[$dependency_code]))
	{
	case -1: // Old
		if ($minRequired && version_compare2($g_VersionsByDistro[$distro_code][$dependency_code], $minRequired) == -1)
			$class = "lessthanminimum";
		else
			$class = "oldversion";
		break;

	case 0: // Latest
		$class = "latestversion";
		break;

	case 1: // Newer
		$class = "nonstandardversion";
		break;
	}
	return '<span class="version ' . $class . '"><span>' . $g_VersionsByDistro[$distro_code][$dependency_code] . '</span></span>';
}

function getEOL($distro)
{
	if (!isset($distro['eol']))
		return "-";

	if ($distro['eol'] == "inf")
		return "&#8734;";

	$outFormat = "M Y";
	switch (substr_count($distro['eol'], '-'))
	{
	case 0:
		return $distro['eol'];
	case 1:
		return date_create_from_format("!Y-m", $distro['eol'])->format($outFormat);
	case 2:
		return date_create_from_format("!Y-m-d", $distro['eol'])->format($outFormat);
	default:
		return "-";
	}
}


// Horizontal Header: Dependency Name
echo "<tr>\n";
echo "\t<th colspan='3'><b>Dependencies</b></th>\n";
foreach ($g_Dependencies as $dependency)
{
	echo "\t<th id='dep__" . $dependency['code'] . "'>";
	echo generateDependencyLink($dependency['code'], $dependency['name']);
	if (isset($dependency['alt-code']))
	{
		$names = [];
		for ($i = 0; $i < count($dependency['alt-code']); ++$i)
			$names[] = generateDependencyLink($dependency['alt-code'][$i], $dependency['alt-name'][$i]);
		echo "<br/>(" . implode(" / ", $names) . ")";
	}
	echo "</th>\n";
}
echo "</tr>\n";

// Horizontal Header: Dependency Version
echo "<tr>\n";
echo "\t<th colspan='3'><b>Min. Required Version</b></th>\n";
foreach ($g_Dependencies as $dependency)
{
	echo "\t<th>";
	if (isset($dependency['minRequired']))
		echo $dependency['minRequired'];
	echo "</th>\n";
}
echo "</tr>\n";

echo "<tr>\n";
echo "\t<th colspan='2'><b>Distributions</b></th>\n";
echo "\t<th><b>EOL</b></th>\n";
echo "</tr>\n";

// Repo Entries
foreach ($g_Distros as $distro)
{
	echo "\n<!-- " . strtoupper($distro['name']) . "-->\n";

	// No separate releases
	if (!isset($distro['releases']))
	{
		// This supports distros that don't have a repology code, but have
		// hard-coded versions stored in the distros.json file instead.
		if (!isset($distro['code']))
			$distro['code'] = md5($distro['name']);

		echo "<tr id='distro__" . $distro['code'] . "'>\n";
		echo "\t<th colspan='2'>" . generateDistroReleaseLink($distro) . "</th>\n";

		echo "\t<td>" . getEOL($distro) . "</td>\n";

		foreach ($g_Dependencies as $dependency)
		{
			$minRequired = isset($dependency['minRequired']) ? $dependency['minRequired'] : False;
			echo "\t<td>";

			if (isset($dependency['alt-code']) and
				(isset($dependency['always-show-alt']) and $dependency['always-show-alt'] or
				isset($distro['alt']) and in_array($dependency['code'], $distro['alt'])))
			{
				echo "<span class='altdeps'></span>";
			}

			echo "</td>\n";
		}
		echo "</tr>\n";
		continue;
	}

	// Separate releases
	echo "<tr id='distro__" . $distro['releases'][0]['code'] . "'>\n";
	echo "\t<th rowspan='" . count($distro['releases']) . "'>" . generateDistroReleaseLink($distro) . "</th>\n";

	$first = True;
	foreach ($distro["releases"] as $release)
	{
		// This supports releases that don't have a repology code, but have
		// hard-coded versions stored in the distros.json file instead.
		if (!isset($release['code']))
			$release['code'] = md5($release['name']);

		if (!$first)
			echo "<tr id='distro__" . $release['code'] . "'>\n";

		$first = False;

		echo "\t<th><i>" . generateDistroReleaseLink($release) . "</i></th>\n";
		echo "\t<td>" . getEOL($release) . "</td>\n";

		foreach ($g_Dependencies as $dependency)
		{
			$minRequired = isset($dependency['minRequired']) ? $dependency['minRequired'] : False;
			echo "\t<td>";

			if (isset($dependency['alt-code']) and
				(isset($dependency['always-show-alt']) and $dependency['always-show-alt'] or
				isset($release['alt']) and in_array($dependency['code'], $release['alt'])))
			{
				echo "<span class='altdeps'></span>";
			}

			echo "</td>\n";
		}
		echo "</tr>\n";
	}
}

?>
