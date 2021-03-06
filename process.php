<?php
require_once './utils.php';

$distroList = array();
foreach ($g_Distros as $distro)
	if (isset($distro['releases']))
	{
		foreach ($distro['releases'] as $release)
			if (isset($release['code']))
				$distroList[] = $release['code'];
			else
				$distroList[] = md5($release['name']);
	}
	else if (isset($distro['code']))
		$distroList[] = $distro['code'];
	else
		$distroList[] = md5($distro['name']);

$dependencyList = array();
$dependencyAlts = array();
$dependencyReqs = array();
foreach ($g_Dependencies as $dependency)
{
	$dependencyList[] = $dependency['code'];
	if (isset($dependency['minRequired']))
		$dependencyReqs[$dependency['code']] = $dependency['minRequired'];
	if (isset($dependency['alt-code']))
		foreach ($dependency['alt-code'] as $code)
		{
			$dependencyList[] = $code;
			$dependencyAlts[$code] = $dependency['code'];
		}
}

?>
	<script>
	var g_DistroList = <?=json_encode($distroList)?>;
	var g_DependencyList = <?=json_encode($dependencyList)?>;
	var g_DependencyMinReqs = <?=json_encode($dependencyReqs)?>;
	var g_DependencyAlts = <?=json_encode($dependencyAlts)?>;
	</script>
<?php
unset($distroList);
unset($dependencyList);
unset($dependencyAlts);
unset($dependencyReqs);
?>
