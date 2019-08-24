<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
	<link href="style.css" rel="stylesheet"/>
	<?php include "./data/projects.php"; ?>
	<?php include "./process.php"; ?>
	<script src="script.js"></script>
	<title>Project Dependencies :: <?=$g_Projects[$g_ProjectCode]['name']?></title>
</head>

<body onload="init()">

<div id="toolbar">
	<span onclick="toggleBoxes('projectsbox')">&#128736;</span>
	<span onclick="toggleBoxes('infobox')">&#128712;</span>
</div>

<div id="projectsbox" class="toolbox">
	<fieldset>
		<legend><b>Projects</b></legend>
		<dl>
<?php
		foreach ($g_Projects as $code => $project)
		{
			echo "\t\t<dt><a href='".$project['url']."' target='_new'>" . $project['name'] . "</a></dt>\n";
			echo "\t\t<dd>";
			$sections = array();
			foreach ($project['sections'] as $section)
				if ($code == $g_ProjectCode && $section == $g_SelectedSection)
					$sections[] = $section;
				else
					$sections[] = "<a href='?project=".$code."&section=".$section."'>" . $section . "</a>";
			echo implode(" &#9702; ", $sections); // &#8226;
			echo "</dd>\n\n";
		}
?>
		</dl>
	</fieldset>
</div>

<div id="infobox" class="toolbox">
	<fieldset>
		<legend><b>Key</b></legend>
		<dl>
		<dt><span class="version lessthanmin">
			<span>Bright Red/Scarlet</span>
		</span></dt>
		<dd>Package provided does not meet the project's minimum required version.</dd>

		<dt><span class="version old">
			<span>Faded Red</span>
		</span></dt>
		<dd>Package provided is an old stable release.</dd>

		<dt><span class="version latest">
			<span>Green</span>
		</span></dt>
		<dd>Package provided is the latest stable release.</dd>

		<dt><span class="version newer">
			<span>Turquoise</span>
		</span></dt>
		<dd>Package provided is a development release.</dd>

		<dt><span class="version nonstandard">
			<span>Light Grey</span>
		</span></dt>
		<dd>Package provided has a non-standard version.</dd>

		<dt><span class="version notavailable">
			<span>Dark Grey</span>
		</span></dt>
		<dd>Package is not available from this repository.</dd>
		</dl>
	</fieldset>
	<fieldset>
		<legend><b>Attribution</b></legend>
		<p>The data shown in this table primarily originates from the <a href="https://repology.org/" target="_new">Repology (https://repology.org/)</a> version tracking service.</p>
		<p>Version data from the <a href="https://pypi.org/" target="_new">Python Package Index</a> is obtained <a href="https://pypi.org/help/#APIs" target="_new">via API</a> directly.</p>
		<p>This page was inspired by the <a href="https://d.pidgin.im/wiki/Dependencies/3.0.0" target="_new">dependencies page</a> for <i>Pidgeon 3.0.0</i>.</p>
	</fieldset>
	<fieldset>
		<legend><b>Source</b></legend>
		<p>The source code for this table can be found at <a href="https://github.com/s0600204/ProjectDependenciesTable" target="_new">GitHub</a>, and is licensed under the MIT license.</p>
		<p>To request a distribution release be added, or to point out corrections to end-of-life or project dependency minimum versions, please file an issue (or a Pull Request) at the above location.</p>
	</fieldset>
	<fieldset>
		<legend><b>Disclaimer</b></legend>
		<p>I am not responsible for the content or security of external links. (And all links on this page are external.)</p>
	</fieldset>
</div>

<hgroup>
<h1><?=$g_Projects[$g_ProjectCode]['name']?></h1>
<h2><?php
	$sections = array();
	foreach ($g_Projects[$g_ProjectCode]['sections'] as $section)
		if ($section == $g_SelectedSection)
			$sections[] = $section;
		else
			$sections[] = "<a href='?project=".$g_ProjectCode."&section=".$section."'>" . $section . "</a>";
	echo implode(" &#9702; ", $sections); // &#8226;
?></h2>
</hgroup>

<table class="wiki">
<?php include "./draw.php"; ?>
</table>

<template id="version_template">
	<span class="version"><span></span></span>
</template>

</body>
</html>
