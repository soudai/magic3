<?php
/**
 * colorプラグイン
 *
 * 機能：テキストに色を付ける。
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: color.inc.php 1098 2008-10-22 11:43:09Z fishbone $
 * @link       http://www.magic3.org
 */
// Allow CSS instead of <font> tag
// NOTE: <font> tag become invalid from XHTML 1.1
define('PLUGIN_COLOR_ALLOW_CSS', TRUE); // TRUE, FALSE

// ----
define('PLUGIN_COLOR_USAGE', '&color(foreground[,background]){text};');
define('PLUGIN_COLOR_REGEX', '/^(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z-]+)$/i');

function plugin_color_inline()
{
	global $pkwk_dtd;

	$args = func_get_args();
	$text = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)

	list($color, $bgcolor) = array_pad($args, 2, '');
	if ($color != '' && $bgcolor != '' && $text == '') {
		// Maybe the old style: '&color(foreground,text);'
		$text    = htmlspecialchars($bgcolor);
		$bgcolor = '';
	}
	if (($color == '' && $bgcolor == '') || $text == '' || func_num_args() > 3)
		return PLUGIN_COLOR_USAGE;

	// Invalid color
	foreach(array($color, $bgcolor) as $col){
		if ($col != '' && ! preg_match(PLUGIN_COLOR_REGEX, $col))
			return '&color():Invalid color: ' . htmlspecialchars($col) . ';';
	}

	if (PLUGIN_COLOR_ALLOW_CSS === TRUE || ! isset($pkwk_dtd) || $pkwk_dtd == PKWK_DTD_XHTML_1_1) {
		$delimiter = '';
		if ($color != '' && $bgcolor != '') $delimiter = '; ';
		if ($color   != '') $color   = 'color:' . $color;
		if ($bgcolor != '') $bgcolor = 'background-color:' . $bgcolor;
		return '<span style="' . $color . $delimiter . $bgcolor . '">' .
			$text . '</span>';
	} else {
		if ($bgcolor != '') return '&color(): bgcolor (with CSS) not allowed;';
		return '<font color="' . $color . '">' . $text . '</font>';
	}
}
?>
