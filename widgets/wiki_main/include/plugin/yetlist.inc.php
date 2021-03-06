<?php
/**
 * yetlistプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: yetlist.inc.php 1143 2008-10-27 09:38:35Z fishbone $
 * @link       http://www.magic3.org
 */
// Copyright (C) 2001-2006 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Yet list plugin - Show a list of dangling links (not yet created)

function plugin_yetlist_action()
{
	global $_title_yetlist, $_err_notexist, $_symbol_noexists, $non_list;
	global $whatsdeleted;

	$retval = array('msg' => $_title_yetlist, 'body' => '');

	// Diff
	//$pages = array_diff(get_existpages(CACHE_DIR, '.ref'), get_existpages());
	$pages = array_diff(WikiPage::getPageCacheRefPages(), get_existpages());
	if (empty($pages)) {
		$retval['body'] = $_err_notexist;
		return $retval;
	}
	$empty = TRUE;

	// Load .ref files and Output
	$script      = get_script_uri();
	$refer_regex = '/' . $non_list . '|^' . preg_quote($whatsdeleted, '/') . '$/S';
	asort($pages, SORT_STRING);
	//foreach ($pages as $file=>$page) {
	foreach ($pages as $page) {
		$refer = array();
		/*foreach (file(CACHE_DIR . $file) as $line) {
			list($_page) = explode("\t", rtrim($line));
			$refer[] = $_page;
		}*/
		$lines = WikiPage::getPageCacheRef($page);
		foreach ($lines as $line) {
			list($_page) = explode("\t", rtrim($line));
			if (empty($_page)) continue;
			$refer[] = $_page;
		}
		
		// Diff
		$refer = array_diff($refer, preg_grep($refer_regex, $refer));
		if (! empty($refer)) {
			$empty = FALSE;
			$refer = array_unique($refer);
			sort($refer, SORT_STRING);

			$r_refer = '';
			$link_refs = array();
			foreach ($refer as $_refer) {
				$r_refer = rawurlencode($_refer);
				//$link_refs[] = '<a href="' . $script . '?' . $r_refer . '">' . htmlspecialchars($_refer) . '</a>';
				$link_refs[] = '<a href="' . $script . WikiParam::convQuery('?' . $r_refer) . '">' . htmlspecialchars($_refer) . '</a>';
			}
			$link_ref = join(' ', $link_refs);
			unset($link_refs);

			$s_page = htmlspecialchars($page);
			if (PKWK_READONLY) {
				$href = $s_page;
			} else {
				// Dangling link
		/*		$href = '<span class="noexists">' . $s_page . '<a href="' . 
				$script . '?cmd=edit&amp;page=' . rawurlencode($page) . '&amp;refer=' . $r_refer . 
				'">' . $_symbol_noexists . '</a></span>';*/
				$href = '<span class="noexists">' . $s_page . '<a href="' . 
				$script . WikiParam::convQuery('?cmd=edit&amp;page=' . rawurlencode($page) . '&amp;refer=' . $r_refer) . 
				'">' . $_symbol_noexists . '</a></span>';
			}
			$retval['body'] .= '<li>' . $href . ' <em>(' . $link_ref . ')</em></li>' . "\n";
		}
	}

	if ($empty) {
		$retval['body'] = $_err_notexist;
		return $retval;
	}

	if ($retval['body'] != '')
		$retval['body'] = '<ul>' . "\n" . $retval['body'] . '</ul>' . "\n";

	return $retval;
}
?>
