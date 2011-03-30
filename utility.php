<?php
/**
 * Replaces double spaces by commas
 * 
 * @param string $val
 * @return string
 */
function commatize($val) {
	return preg_replace('/  /', ',', $val);
}

/**
 * Removes spaces
 * 
 * @param string $val
 * @return string
 */
function spaceLess($val) {
	return preg_replace('/ /', '', $val);
}
?>