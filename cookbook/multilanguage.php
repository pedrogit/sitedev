<?php if (!defined('PmWiki')) exit();
/*
	MultiLanguage 1.1
	multilanguage for PmWiki
	copyright (c) 2006-2007 Yuri Giuntoli (www.giuntoli.com)

	This PHP script is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation.

	This PHP script is not part of the standard PmWiki distribution.

	0.0 - 07.11.2004
		First multilanguage concept by KAL.
	0.1 - 22.01.2006
		First implementation.
	0.2 - 23.01.2006
		Added parsing of the page to automatically find available languages.
		Added (:selectlang:) directive to display selection links with current available languages.
	0.3 - 25.01.2006
		Added possibility to configure (:selectlang:) output.
		Available languages are now parsed on edit and saved as attributes, so page load is faster (uses PCache).
		Added parameter to (:selectlang:) directive to display links to a specific page.
	1.0 - 10.02.2006
		Added cascade of default languages in order of importance.
		Added parameter to (:selectlang:) to display links to a default page when a language is not available.
		Added support to multilanguage titles for [[Page|+]] titled links (saved as page attributes).
		Public release.
	1.0b - 03.04.2006
		Fixed a problem when $pagename is not set.
	1.1 - 05.07.2007
		Added new langinpage conditional.
		Added {$userlang} page variable (suggested by noskule).
		Fixed a problem with setting userlang cookie (fixed by SteP).
		Added version information (see Cookbook.RecipeCheck).
	1.2 - 14.10.2015
		changed Markup creation so it works with PHP >5.5
		(see also: http://www.pmwiki.org/wiki/PmWiki/CustomMarkup)

*/

	define(MULTILANGUAGE, '1.1');
	$RecipeInfo['MultiLanguage']['Version'] = '2015-10-14';

	SDV($DefaultLanguages,array('en'));
	SDV($LanguageSelectionFmt,'[[{$FullName}?userlang=$1|$1]] ');

	//------------------------------------------------------------------------------------

	if (isset($_GET['userlang'])) {
		$userlang = $_GET['userlang'];
		setcookie('userlang',$userlang,0,'/');
	} else if (isset($_COOKIE['userlang'])) {
		$userlang = $_COOKIE['userlang'];
	} else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $userlang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2);
		setcookie('userlang',$userlang,0,'/');
	} else {
		$userlang = $DefaultLanguages[0];
		setcookie('userlang',$userlang,0,'/');
	}
	$_COOKIE['userlang']=$userlang;

	if ($pagename=='') {
		if (function_exists('ResolvePageName')) {
			$pagename = ResolvePageName($pagename);
		} else {
			$pagename="$DefaultGroup.$DefaultName";
		}
	}

	if (!isset($PCache[$pagename])) {
		PCache($pagename, ReadPage($pagename, READPAGE_CURRENT));
	}

	//------------------------------------------------------------------------------------

	$PageLanguages = explode(',',$PCache[$pagename]['languages']);
	if (!in_array($userlang,$PageLanguages)) {
		foreach ($DefaultLanguages as $lang) {
			if (in_array($lang,$PageLanguages)) {
				$userlang = $lang;
				break;
			}
		}
		if (!$PageLanguages[0]=='') {
			$userlang = $PageLanguages[0];
		} else {
			$userlang = $DefaultLanguages[0];
		}
	}
	$Conditions['userlang'] = '$GLOBALS[\'userlang\']==$condparm';

	//------------------------------------------------------------------------------------

	function LangInPage($args) {
		global $PCache, $DefaultLanguages;

		$args = ParseArgs($args);
		$args = $args[''];

		if (count($args)<2) return false;

		if (in_array($args[0],$DefaultLanguages)) {
			$lang = $args[0];
			$pn = $args[1];
		} else {
			$lang = $args[1];
			$pn = $args[0];
		}

		if (!isset($PCache[$pn])) {
			PCache($pn, ReadPage($pn, READPAGE_CURRENT));
		}
		$PageLanguages = explode(',',$PCache[$pn]['languages']);

		return in_array($lang,$PageLanguages);
	}

	$Conditions['langinpage'] = 'LangInPage($condparm)';

	//------------------------------------------------------------------------------------

	$FmtPV['$Title'] = 'MultiLanguageTitle($pagename,$name)';
	$FmtPV['$Titlespaced'] = '$AsSpacedFunction(MultiLanguageTitle($pagename,$name))';
	$FmtPV['$userlang'] = "'$userlang'";

	function MultiLanguageTitle($pn,$name) {
		global $PCache, $userlang, $DefaultLanguages, $pagename, $AsSpacedFunction;

		$pn = MakePageName($pagename,$pn);
		if (!PageExists($pn)) return '';
		if (!isset($PCache[$pn])) {
			PCache($pn, ReadPage($pn, READPAGE_CURRENT));
		}
		if (isset($PCache[$pn]["mltitle-$userlang"])) return $PCache[$pn]["mltitle-$userlang"];
		foreach ($DefaultLanguages as $lang) {
			if (isset($PCache[$pn]["mltitle-$lang"])) return $PCache[$pn]["mltitle-$lang"];
		}
		if (isset($PCache[$pn]['title'])) return $PCache[$pn]['title'];
		if ($GLOBALS['SpaceWikiWords']) {
			return $AsSpacedFunction($name);
		} else {
			return $name;
		}
	}


	//------------------------------------------------------------------------------------

	Markup_e('selectlang', 'directives', "/\\(:selectlang\s*(.*?):\\)/i", "LanguageSelection(ParseArgs(\$m[1]))");

	function LanguageSelection($args) {
		global $LanguageSelectionFmt, $pagename, $PCache, $DefaultLanguages;

		$pn = $args['page'];
		if ($pn=='') $pn = $pagename;

		$pn = MakePageName($pagename,$pn);
		if (!PageExists($pn)) return '';
		if (!isset($PCache[$pn])) {
			PCache($pn, ReadPage($pn, READPAGE_CURRENT));
		}

		if (!$PCache[$pn]['languages']) {
			$PageLanguages = array();
		} else {
			$PageLanguages = explode(',',@$PCache[$pn]['languages']);
		}

		$mid = '';
		foreach ($DefaultLanguages as $lang) {
			if (in_array($lang,$PageLanguages)) {
				$mid .= str_replace('$1',$lang,$LanguageSelectionFmt);
			} else if (isset($args['default'])) {
				$mid .= FmtPageName(str_replace('$1',$lang,$LanguageSelectionFmt),$args['default']);
			}
		}
		foreach ($PageLanguages as $lang) {
			if (!in_array($lang,$DefaultLanguages)) {
				$mid .= str_replace('$1',$lang,$LanguageSelectionFmt);
			}
		}
		return FmtPageName($mid,$pn);
	}

	//------------------------------------------------------------------------------------

	array_unshift($EditFunctions,'SaveLanguages');

	function SaveLanguages($pagename, &$page, &$new) {
		$text = $new['text'] ?? "";
		$text = preg_replace('/(\[=.+=\])/msiU', '', $text);
		$text = preg_replace('/(\[@.+@\])/msiU', '', $text);
		preg_match_all('/\(:if\s*userlang\s*(.+?)\s*:\)/msi', $text, $matches);

		$PageLanguages = array();
		foreach ($matches[1] as $lang) {
			if (!in_array($lang,$PageLanguages)) $PageLanguages[] = $lang;
		}

		if (count($PageLanguages)>0) {
			$new['languages'] = implode(',', $PageLanguages);
		} else {
			unset($new['languages']);
		}

		preg_match_all('/\(:if\s*userlang\s*(.+?)\s*:\).*?\(:title\s*(.+?)\s*:\)/msi', $text, $matches);
		if (count($matches[1])==count($matches[2])) {
			for ($i=0; $i<count($matches[2]); ++$i) { 
				$new['mltitle-'.$matches[1][$i]] = $matches[2][$i];
			}
		}
	}

?>