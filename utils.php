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

?>
