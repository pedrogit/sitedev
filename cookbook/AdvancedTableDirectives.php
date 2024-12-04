<?php if (!defined('PmWiki')) exit();
/*
This file is AdvancedTableDirectives.php; you
can redistribute it and/or modify it under the
terms of the GNU General Public License as
published by the Free Software Foundation
http://www.fsf.org either version 2 of the 
License, or (at your option) any later version.

Copyright 2007 GNUZoo (guru@gnuzoo.org)

	http://www.pmwiki.org/wiki/Profiles/GNUZoo

Please donate to the author:

	http://gnuzoo.org/GNUZooPayPal/

----

To add actions set variable $RecipeRunAction before 
including this file:

$RecipeRunAction['AdvancedTableDirectives'][] = 'pdf';
$RecipeRunAction['AdvancedTableDirectives'][] = 'whatever';

include_once("$FarmD/cookbook/AdvancedTableDirectives.php");
*/

$RecipeName = 'AdvancedTableDirectives';
$RecipeVersion = '3.2';
$RecipeInfo[$RecipeName]['Version'] = $RecipeVersion;
#----------------------------------------
# Add actions to array to run this recipe
$RecipeRunAction[$RecipeName][]='browse';
$RecipeRunAction[$RecipeName][]='preview';
$RecipeRunAction[$RecipeName][]='print';
#----------------------------------------
$UserAction = ($action === 'edit' && isset($_POST['preview']) && (boolean)$_POST["preview"]) ? 'preview' : $action;
if (! in_array($UserAction, $RecipeRunAction[$RecipeName])) return;
#------------------------------------------------------------
#recipe runs below this line
#------------------------------------------------------------
Markup('AdvancedTableDirectives', 
	'<table',
	'/^\\(:(table(?:end)?|caption|row#|row(?:end)?|cell(?:[cri])?#|celli#(?:[\d])?|celli[cr]#|cell(?:nr)?|head(?:nr)?)(\\s.*?)?:\\)/i',
	"AdvancedTableDirectives_e");

function AdvancedTableDirectives_e($m) {
 	return AdvancedTableDirectives(strtolower($m[1] ?? null),PQA($m[2] ?? null));
}

#------------------------------------------------------------
SDV($TableCellIndexMax, 1);
#------------------------------------------------------------
$ATDTableNumber = 0;
$ATDLastElementOpen      = array(); # boolean
$ATDLastElementCloseText = array(); # string to close element
#------------------------------------------------------------
function MergeClassAndAppendAttributes($AttributeString1, $AttributeString2){
	# if either is empty
	if ($AttributeString1 === '' | $AttributeString1 === NULL) return $AttributeString2;
	if ($AttributeString2 === '' | $AttributeString2 === NULL) return $AttributeString1;

	# if either has no class attribute
	if (strpos($AttributeString1, 'class=') === false || 
            strpos($AttributeString2, 'class=') === false)
		return $AttributeString1 . ' ' . $AttributeString2;

	# below here we know that both have the class attribute
	# quote unquoted 
	$AttributeString1 = preg_replace('/\\bclass=([^\'\\"].*?)\\b/', 'class=\'$1\'', $AttributeString1);
	$AttributeString2 = preg_replace('/\\bclass=([^\'\\"].*?)\\b/', 'class=\'$1\'', $AttributeString2);

	# get the stuff inside the quotes
	preg_match('/(?:class=)(?:\'|\\")(.*?)(?:\'|\\")/', $AttributeString1, $matches);
	$AttributeStringValue1 = $matches[1];

	preg_match('/(?:class=)(?:\'|\\")(.*?)(?:\'|\\")/', $AttributeString2, $matches);
	$AttributeStringValue2 = $matches[1];

	# get string of all classes
        $ClassValues = array_unique(array_merge(explode(' ', $AttributeStringValue1), explode(' ', $AttributeStringValue2)));
	$ClassValues = implode(' ', $ClassValues);

	# replace stuff in quotes in 1st attribute string with all classes
	$AttributeString1 = preg_replace('/\\bclass=(\'|\\")(.*?)(\1)/', 'class=$1' . $ClassValues . '$1', $AttributeString1);

	# remove class information from the 2nd attribute string
	$AttributeString2 = preg_replace('/\\bclass=(\'|\\")(.*?)(\1)/', '', $AttributeString2);

	return $AttributeString1 . ' ' . $AttributeString2;
}
#------------------------------------------------------------
# ElementType 0=cell,head,caption 1=row, 2=table
function SetupCloseLastElement($sometext, $ElementType = 0) {
	global $ATDTableNumber, $ATDLastElementOpen, $ATDLastElementCloseText;
	$ATDLastElementCloseText[$ATDTableNumber][$ElementType] = $sometext;
	$ATDLastElementOpen[$ATDTableNumber][$ElementType] = true;
}
#------------------------------------------------------------
# ElementType 0=cell,head,caption 1=row, 2=table
function CloseLastElement($ElementType = 0) {
	global $ATDTableNumber, $ATDLastElementOpen, $ATDLastElementCloseText;
	$ATDLastElementOpen[$ATDTableNumber][$ElementType] = false;
	return $ATDLastElementCloseText[$ATDTableNumber][$ElementType] ?? '';
}
#------------------------------------------------------------
function AdvancedTableDirectives($name,$attr) {
	global $TableCellAttrFmt, $TableRowAttrFmt, $TableCellIndexMax, $TableRowIndexMax, $FmtV;

	global $ATDTableNumber, $ATDLastElementOpen;

	static $TableRowNumber           = array(); # actual table row number
	static $TableColumnNumber        = array(); # actual table column number

	static $IncrementingColumn       = array(); # incermenting column number
	static $IncrementingRow          = array(); # incrementing row number
	static $LastIncrementingTableRow = array(); # last actual table row number when an incrementing row number is used

	static $Incrementor              = array(); # incrementing number
	$IncSub                          =       0; # designates which incrementor to use

	$out                             =      ''; # output buffer

	static $RowCounter               = array(); # incrementing row number - used only by case 'row#'

	$n = substr($name, 0, 4);
	if ($n === 'cell' || $n === 'head') {
		if ($name === 'cellnr' || $name == 'headnr') $out .= AdvancedTableDirectives('row', '');

		# If not already set, PMWiki will automatically include the 
		# attribute valign='top' with all (:cell:) and (:cellnr:). 
		# PM said "Table Directives were created for layout purposes 
		# and in that case it makes the most sense for each cell (column) 
		# to have its content at the top of the row. The attribute is 
		# placed in each cell and not in the row because certain browsers 
		# didn't recognize valign='top' in the row tag.
		$attr .= (strpos(strtolower($attr), 'valign=') !== false) ? '' : ' valign=\'top\'';

		if ($ATDLastElementOpen[$ATDTableNumber][0] ?? false) $out .= CloseLastElement();
		if (!($ATDLastElementOpen[$ATDTableNumber][1] ?? false)) $out .= AdvancedTableDirectives('newrow', '');

	 	$FmtV['$TableCellIndex'] = ($TableColumnNumber[$ATDTableNumber] % ($TableCellIndexMax ? $TableCellIndexMax : 1)) + 1;
	 	$FmtV['$TableCellCount'] = ++$TableColumnNumber[$ATDTableNumber];

		$attr = MergeClassAndAppendAttributes($attr, FmtPageName(@$TableCellAttrFmt, ''));

		if ($n === 'head') {
			SetupCloseLastElement('</th>');
			return $out . '<th ' . $attr . '>';
		}
		$out .= '<td ' . $attr . '>';
		switch($name) {
			case 'cell':
			case 'cellnr':
				SetupCloseLastElement('</td>');
				return $out;
			case 'celli#9': if ($IncSub === 0) $IncSub = 9;
			case 'celli#8': if ($IncSub === 0) $IncSub = 8;
			case 'celli#7': if ($IncSub === 0) $IncSub = 7;
			case 'celli#6': if ($IncSub === 0) $IncSub = 6;
			case 'celli#5': if ($IncSub === 0) $IncSub = 5;
			case 'celli#4': if ($IncSub === 0) $IncSub = 4;
			case 'celli#3': if ($IncSub === 0) $IncSub = 3;
			case 'celli#2': if ($IncSub === 0) $IncSub = 2;
			case 'celli#1': if ($IncSub === 0) $IncSub = 1;
			case 'celli#' : return $out . ++$Incrementor[$ATDTableNumber][$IncSub] . '</td>';

			case 'cellr#' : return $out . $TableRowNumber[$ATDTableNumber] . '</td>';

			case 'cellc#' :
			case 'cell#'  : return $out . $TableColumnNumber[$ATDTableNumber] . '</td>';

			case 'cellic#': return $out . ++$IncrementingColumn[$ATDTableNumber] . '</td>';

			case 'cellir#':
				# only increment if in a new row
				if ($LastIncrementingTableRow[$ATDTableNumber] !== $TableRowNumber[$ATDTableNumber]) {
					$LastIncrementingTableRow[$ATDTableNumber] = $TableRowNumber[$ATDTableNumber];
					$IncrementingRow[$ATDTableNumber] ++;
				}
			return $out . $IncrementingRow[$ATDTableNumber] . '</td>';
		}
	}

	switch ($name) {
		case 'row': # THIS DIRECTIVE CAN ALSO BE CALLED THROUGH RECURSION
			if ($ATDLastElementOpen[$ATDTableNumber][0] ?? false) $out .= CloseLastElement();
			# FALL THROUGH TO NEXT CASE
		case 'newrow': # ONLY CALLED THROUGH RECURSION AND FALL THROUGH
			if ($ATDLastElementOpen[$ATDTableNumber][1] ?? false) $out .= CloseLastElement(1);

			$TableColumnNumber[$ATDTableNumber] = 0;
			$IncrementingColumn[$ATDTableNumber] = 0;
			$FmtV['$TableCellCount'] = 0;
			if (array_key_exists($ATDTableNumber, $TableRowNumber)){
				$FmtV['$TableRowIndex' ] = ($TableRowNumber[$ATDTableNumber] % $TableRowIndexMax) + 1;
				$FmtV['$TableRowCount' ] = ++$TableRowNumber[$ATDTableNumber];
			}

			$attr = MergeClassAndAppendAttributes($attr, FmtPageName(@$TableRowAttrFmt, ''));

			SetupCloseLastElement('</tr>', 1);
			return $out . '<tr ' . $attr . '>';
		case 'caption':
			# valign does not belong on caption attribute
			SetupCloseLastElement('</caption>');
			# pmwiki does not recognize caption as a block markup
			return '<:block><caption ' . $attr . '>';
		case 'table':
			$ATDTableNumber ++;
			$TableRowNumber    [$ATDTableNumber] = 0;
			$RowCounter        [$ATDTableNumber] = 0;
			$IncrementingColumn[$ATDTableNumber] = 0;
			$IncrementingRow   [$ATDTableNumber] = 0;

			$Incrementor[$ATDTableNumber][0] = 0;
			$Incrementor[$ATDTableNumber][1] = 0;
			$Incrementor[$ATDTableNumber][2] = 0;
			$Incrementor[$ATDTableNumber][3] = 0;
			$Incrementor[$ATDTableNumber][4] = 0;
			$Incrementor[$ATDTableNumber][5] = 0;
			$Incrementor[$ATDTableNumber][6] = 0;
			$Incrementor[$ATDTableNumber][7] = 0;
			$Incrementor[$ATDTableNumber][8] = 0;
			$Incrementor[$ATDTableNumber][9] = 0;

			CloseLastElement($ATDTableNumber);
			CloseLastElement($ATDTableNumber, 1);
			SetupCloseLastElement('</table>', 2);
			# PM said to put <:block> here.
			return '<:block><table ' . $attr . '>';
		case 'tableend':
			if ($ATDLastElementOpen[$ATDTableNumber][0] ?? false) $out .= CloseLastElement();
			if ($ATDLastElementOpen[$ATDTableNumber][1] ?? false) $out .= CloseLastElement(1);
			if ($ATDLastElementOpen[$ATDTableNumber][2] ?? false) $out .= CloseLastElement(2);
			$ATDTableNumber --;
			return $out;
		case 'row#': # deprecated - kept for backward compatibility
			$out .= AdvancedTableDirectives('row', $attr);
			$RowCounter[$ATDTableNumber]++;

			$FmtV['$TableCellCount'] = $RowCounter[$ATDTableNumber];

			$attr = MergeClassAndAppendAttributes($attr, FmtPageName(@$TableCellAttrFmt, ''));

			return $out . '<td ' . $attr . '>' . $RowCounter[$ATDTableNumber] . '</td>';
	}
}
