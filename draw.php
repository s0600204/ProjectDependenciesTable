<?php

function generateImageLink ($repoCode, $libraryCode, $minRequired = False)
{
	if ($minRequired)
		return "<img src='https://repology.org/badge/version-only-for-repo/" . $repoCode . "/" . $libraryCode . ".svg?minversion=" . $minRequired . "' style='vertical-align: middle'/>";
	return "<img src='https://repology.org/badge/version-only-for-repo/" . $repoCode . "/" . $libraryCode . ".svg' style='vertical-align: middle'/>";
}

function generateGenericLink($addr, $text)
{
	return "<a href='$addr' class='ext-link'><span class='icon'></span>" . $text . "</a>";
}

function generateLibraryLink ($code, $name)
{
	return generateGenericLink("https://repology.org/metapackage/$code", $name);
}

function generateDistroReleaseLink($distro)
{
	if (isset($distro['link']))
		return generateGenericLink($distro['link'], $distro['name']);
	return $distro['name'];
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


// Horizontal Header: Library Name
echo "<tr>\n";
echo "\t<th colspan='3'><b>Libraries</b></th>\n";
foreach ($c_Libraries as $library)
{
	echo "\t<th>";
	echo generateLibraryLink($library['code'], $library['name']);
	if (isset($library['alt-code']))
	{
		$names = [];
		for ($i = 0; $i < count($library['alt-code']); ++$i)
			$names[] = generateLibraryLink($library['alt-code'][$i], $library['alt-name'][$i]);
		echo "<br/>(" . implode(" / ", $names) . ")";
	}
	echo "</th>\n";
}
echo "</tr>\n";

// Horizontal Header: Library Version
echo "<tr>\n";
echo "\t<th colspan='3'><b>Min. Required Version</b></th>\n";
foreach ($c_Libraries as $library)
{
	echo "\t<th>";
	if (isset($library['minRequired']))
		echo $library['minRequired'];
	echo "</th>\n";
}
echo "</tr>\n";

echo "<tr>\n";
echo "\t<th colspan='2'><b>Distributions</b></th>\n";
echo "\t<th><b>EOL</b></th>\n";
echo "</tr>\n";

// Repo Entries
foreach ($c_Distros as $distro)
{
	echo "\n<!-- " . strtoupper($distro['name']) . "-->\n";

	// No separate releases
	if (!isset($distro['releases']))
	{
		echo "<tr>\n";
		echo "\t<th colspan='2'>" . generateDistroReleaseLink($distro) . "</th>\n";

		echo "\t<td><center>" . getEOL($distro) . "</center></td>\n";

		foreach ($c_Libraries as $library)
		{
			$minRequired = isset($library['minRequired']) ? $library['minRequired'] : False;
			echo "\t<td><center>";
			echo generateImageLink($distro['code'], $library['code'], $minRequired);
			
			if (isset($library['alt-code']) and
				(isset($library['always-show-alt']) and $library['always-show-alt'] or
				isset($distro['alt']) and in_array($library['code'], $distro['alt'])))
			{
				$versions = array_map(function ($c) {
					global $distro;
					return generateImageLink($distro['code'], $c);
				}, $library['alt-code']);
				echo " (" . implode(" / ", $versions) . ")";
			}
			echo "</center></td>\n";
		}
		echo "</tr>\n";
		continue;
	}

	// Separate releases
	echo "<tr>\n";
	echo "\t<th rowspan='" . count($distro['releases']) . "'>" . generateDistroReleaseLink($distro) . "</th>\n";

	$first = True;
	foreach ($distro["releases"] as $release)
	{
		if (!$first)
			echo "<tr>\n";
		$first = False;

		echo "\t<th><i>" . generateDistroReleaseLink($release) . "</i></th>\n";
		echo "\t<td><center>" . getEOL($release) . "</center></td>\n";

		foreach ($c_Libraries as $library)
		{
			$minRequired = isset($library['minRequired']) ? $library['minRequired'] : False;
			echo "\t<td><center>";
			if (isset($release['code']))
			{
				echo generateImageLink($release['code'], $library['code'], $minRequired);
				if (isset($library['alt-code']) and
					(isset($library['always-show-alt']) and $library['always-show-alt'] or
					isset($release['alt']) and in_array($library['code'], $release['alt'])))
				{
					$versions = array_map(function ($c) {
						global $release;
						return generateImageLink($release['code'], $c);
					}, $library['alt-code']);
					echo " (" . implode(" / ", $versions) . ")";
				}
			}
			else
			{
				if (isset($release['hard'][$library['code']]))
					echo $release['hard'][$library['code']];
				else
					echo "-";

				if (isset($release['alt']) and isset($library['alt-code']) and in_array($library['code'], $release['alt']))
				{
					$versions = [];
					for ($i = 0; $i < count($library['alt-code']); ++$i)
						if (isset($release['hard'][$library['alt-code'][$i]]))
							$versions[] = $release['hard'][$library['alt-code'][$i]];
						else
							$versions[] = "-";
					echo " (" . implode(" / ", $versions) . ")";
				}
			}
			echo "</center></td>\n";
		}
		echo "</tr>\n";
	}
}

?>
