<?php if (!defined('PmWiki')) exit();
/*
+----------------------------------------------------------------------+
| Copyright 2023 Hans Bracker. http://www.softflow.uk
| This program is free software; you can redistribute it and/or modify
| it under the terms of the GNU General Public License, Version 2, as
| published by the Free Software Foundation.
| http://www.gnu.org/copyleft/gpl.html
| This program is distributed in the hope that it will be useful,
| but WITHOUT ANY WARRANTY; without even the implied warranty of
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
| GNU General Public License for more details.
+----------------------------------------------------------------------+
*/
$RecipeInfo['FoxCSV']['Version'] = '2024-07-12'; //needs PHP 7.2 min, Fox & FoxEdit versions 2023-11-14 min

// configuration
SDVA($FoxCSVConfig, [
    'sep' => '', //separator/delimter character. Leave empty so FoxCSV can determine separator from csv data without need to specify sep=
    'case' => 0,  //case-insensitive queries, set to 1 for case-sensitive queries
    'regex' => 0, //simplified pagelist-like wildcards in queries. Set to 1 to use regular expression query patterns 
    'header' => 1,//no column/field headers included in data rows. Set to 1 to include a row of header names
    'hideidx'=> 0, //default is to show index as column in auto templates
    'idxname'=> 'Row', //default name for IDX
    'textname' => 'Text', //default name for Text header field
    'popups' => 0, //set to 1 to enable popup confirmation dialogues
    'order' => 'natcase', //default sort order keyword
    'sortable' => 1, //default class=sortable (sorting via js) for auto tables
    'editlinks' => 1, //by default show edit and delete links/buttons
    'new' => 'bottom', //by default new entry to bottom, set to 'top' to put new entry just below header row
    'saveasnew' => 0, //set to 1 to show 'Save as New' button in edit form 
    'env' => 0, //set to 1 to enclose each item in double quotes
    'copycsv' => 0, //set to 1 to show form for copying/importing csv/txt files
    'fileedit' => 1, // set to 0 to inhibit file editing (no writing to files, only to wiki pages)
    'decimals' => 2,      // decimal places for 'num' fields number format
    'decisep' => '.', // decimal separator for 'num' fields number format
    'thousep' => ',', // thousands separator for 'num' fields number format
    'editbuttonurl' =>   "$FarmPubDirUrl/fox/edit-button.png",
    'deletebuttonurl' => "$FarmPubDirUrl/fox/delete-button.png",
]);

// markup expression {(newidx <source> [sep=..])} . Can be used as template var {$$(newidx .... )} in fox query form template
$MarkupExpr['newidx'] = 'fxc_Next_Idx_Num_ME($pagename, $argp)'; 
function fxc_Next_Idx_Num_ME( $pagename, $argp ) {
    $src = $argp['source'] ?? $argp[''][0] ?? '';
    if ($src=='') {
        $GLOBALS['FoxMsgFmt'][] = "%red%'''Error''' in form: '''newidx''' needs specific source.  Check form, and correct wrong idxs%%";
        return '000'; //error
    }
    $s = $argp['sep'] ?? $argp['seperator'] ?? $GLOBALS['FoxCSVConfig']['sep'];
    $rows = fxc_Get_Text_Rows( $pagename, $src );
    if (strstr($rows[0], "IDX$s"))
         $n = fxc_Get_Row_Key($rows, "new", $s);
    else $n = count($rows);
    return $n;
} //}}}

// markup expression {(csv ......)} . Can be used as template var {$$(csv .... )} in fox query form template
$MarkupExpr['csv'] = 'fxc_Display_ME($pagename, $argp)'; 
function fxc_Display_ME( $pagename, $argp ) {
    unset($argp['#']);
    $out = fxc_Display($pagename, 'mxcsv', $argp);
    return str_replace("\n\n","\n", $out);
} //}}}

// markup directive (:csv source=<pagename> template=<pagename> [sort=<field> ] [query="<field=...>..."] [seperator=".."] :)
Markup('foxcsvdisplay', '>if', '/\\(:csv\\s+(\\S.*?):\\)/i', "fxc_Display");
// main function for csv display
function fxc_Display ($m) {    
    global  $FoxCSVConfig, $FoxMsgFmt, $RegexQueryExclude, $InputValues;
    extract($GLOBALS['MarkupToHTML']);
    $args = ParseArgs($m[1]);
    unset($args['#']);
    // initialise option variables
    if ((isset($args['template']) OR isset($args['fmt'])) AND !isset($args['header'])) 
        $args['header'] = 0; // by default no header row if we use template, not auto template
    $opt = array_merge($FoxCSVConfig, $args);
 #show($opt,'opt');
    // Pierre Racine Preview
    // Make (:csv:) markup parameters available for the template
    // so we can use them to take decisions and use extra parameters in the template
    $simpleArgs = [];
    foreach ($args as $key => $value) {
        if (is_array($value) === FALSE) {
            $simpleArgs['FOXCSV_'.$key] = $value;
        }
    }

    $source = $opt['source'] ?? $opt['src'] ?? array_shift($opt['']);
    if (empty($source)) return;
    $template = $opt['template'] ?? $opt['fmt'] ?? '';

    //suppress display of edit and delete links/buttons, because they dont work when displaying file contents
    if (preg_match('/\.(csv|txt)$/i',$source,$m) && $FoxCSVConfig['fileedit']==0) 
          $opt['editlinks'] = 0;

    // get text rows from page, page section or file
    $text = fxc_Get_Text ($pagename, $source);
    if ($text=='') return "'''Error:''' cannot get source text from '''$source'''";
    $text = trim($text,"\r\n");

    // get csv separator/delimiter direct from text, or via sep= parameter 
    list($sep, $opt) = fxc_Set_Sep($text, $opt);
    
    // parse text into data 2-dimensional array, items per row
    // Pierre Racine Multiline
    // If set in the config, use a different CSV parser (able to parse double quoted values containing \n properly)
    // $FoxCSVConfig['csvparser'] = 'CSVTemplateParseCVSContentAsSimpleArrayWithHeader';
    if (isset($FoxCSVConfig['csvparser']) && function_exists($FoxCSVConfig['csvparser'])){
        $data = $FoxCSVConfig['csvparser']($text, $sep);
    }
    else
        $data = fxc_Parse_CSV($text, $sep);
    
    // Pierre Racine Empty CSV
    // Never fail when parsing CSV. Get an empty array instead.
    //if (empty($data)) return "'''Error:''' Parsing csv data failed!";
    if (empty($data)) return "";
 #show ($data,'data');

    // checking for errors in header names. Display errors by using markup (:foxmessages:)
    foreach ($data[0] as $k=>$v)  { 
        if (preg_match('/^[\d]|[^\-a-zA-Z0-9_]/', $v, $m)) 
            $FoxMsgFmt[] = "'''Error:''' %black%invalid header name: %blue%'''$v''' %black%in $source%%";
    }

    // display specific data item only
    if (isset($opt['cell'])) $cell = $opt['cell'];
    elseif (isset($opt[''][0]) && strpos($opt[''][0],'/')>0) 
        $cell = array_shift($opt['']);
    if (isset($cell)) 
        return  fxc_Table_Item($data, $cell, $sep);

    // create data array with field names as keys for each item
    array_walk($data, function(&$a) use ($data) {
        $a = fxc_Array_Combine_Special($data[0], $a);
    });
    // create header array, remove header row from data array
    $header = array_shift($data);

    // add SOURCE and IDX fields to data array, (:csv-delete ...:) and (:csv-edit ...:) rely on their presence 
    foreach ($data as $k=>$item) {
        $add1 = array('SOURCE'=>$source);
        if (array_key_exists('IDX',$item)) {
            // Pierre Racine Preview
            // Add markup parameters to the data to be used by the template
            $data[$k] = $add1 + $item + $simpleArgs;
        } else { //no IDX, add it!
            $add2 = array('IDX'=>$k+1);
            $data[$k] = $add2 + $add1 + $item + $simpleArgs;
        }
   }

    // filter data. Query-filter is constructed from query string and/or strings from header field names submitted
    $opt['query'] = $opt['query'] ?? $opt['filter'] ?? $opt['q'] ?? "";
    fxc_Query($data, $header, $opt); 

    // sort data
    if (isset($opt['sort']))
        fxc_Multi_Column_Sort($data, $header, $opt['sort'], $opt['order']);

    // add CNT field, can be used with template var {$$CNT}, 
    // giving descending result row count (Not the IDX of the item)
    foreach ($data as $k=>$item) {
         $new = array('CNT'=>$k+1);
         $data[$k] = $new + $item;
         // Pierre Racine Preview
         // Add the total count of records so it can be used by the template
         $new = array('FOXCSV_TOTALCNT'=>count($data));
         $data[$k] = $data[$k] + $new;
    }

    // extract subset of data rows according to 'count=' parameter, using function on scripts/pagelist.php
     if (isset($opt['count']))
        FPLTemplateSliceList($pagename, $data, $opt);
    $data = array_values($data); //re-index
    // Pierre Racine Preview
    // Add TOTALCNT and markup parameters in the header record also
    $header = array('SOURCE'=>$source) + array('FOXCSV_TOTALCNT'=>count($data)) + $simpleArgs + $header; //source field needed for possible var replacement, does not display as column

 #show($header,'header'); show($data,'data final');

 // set a function call number, used to feed correct text to Iput Values for copycsv
    static $num = 0;
    $num++;
    // auto build simple table template for all fields, or fetch template string from a page
    $head = $body = $foot = $csvbody = ''; 
    if ($template=='') { 
        $auto = 1;
        $out = fxc_Auto_Table($pagename, $data, $header, $opt, $num);
    } 
    else { // use an external template. The header row may be supplied via markup above (:foxcsv ...:) markup, or not at all
        $auto = 0;
        $template = RetrieveAuthSection ($pagename, $template, $list=NULL, $auth='read');
        // Pierre Racine Preview
        // Made a function with the template logic so it can be used by Fox when generating a preview with CSV data
        $out = fxc_Fill_Template($pagename, $data, $header, $template, $opt);
    }
    // return output as wiki markup, Markup to HTML will be done later by PmWiki
    // Pierre Racine
    // This fix all conditional markup (:if:)(:else:) interpretation problems
    $out = MarkupToHTML($pagename, $out);
    return Keep($out);
} //}}}

function fxc_Fill_Template($pagename, $data, $header, $template, $opt) {
    $tp = preg_split('/\(:template\s(\w+):\)/si', $template,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE); 
    if (count($tp)==1) $tpeach = $tp[0];
    else {
        for($i=0; $i<count($tp); $i++) {
            if ($tp[$i]=='first') { $tpfirst = $tp[$i+1]; continue; }
            if ($tp[$i]=='each') { $tpeach = $tp[$i+1]; continue; }
            if ($tp[$i]=='last') { $tplast = $tp[$i+1]; continue; }
        }
    }  
    if (array_key_exists('header', $opt) && $opt['header'] == 1)  $tpfirst .= $tpeach; //to include header row as a first data row
    $head = $body = $foot = ''; 
    if (isset($tpfirst)) { 
        $str = FoxVarReplace($pagename, $header, '', $tpfirst);
        $head = preg_replace('/\{\$\$\(?(.*?)\)?\}/'," ",$str);
    }
    if (isset($tpeach)) {
        $rowcnt = count($data);
        $body = '';
        for($k=0; $k<$rowcnt; $k++) {
            // Pierre Racine Multiline
            // Add a (:csv:) parameter to replace \n with something else (e.g. \\ for simple tables)
            if (isset($opt['eol'])) {
                // replace \n with a true \n in the provided EOL
                $eol = preg_replace("/\\\\n/", PHP_EOL, $opt['eol']);
                // double any \\
                $eol = preg_replace("/\\\\/", "\\\\\\\\", $eol);
                array_walk($data[$k], function (&$val, $key, $eol){
                    if ($key !== "FOXCSV_eol")
                      $val = preg_replace("/(?<!\\\\)\r\n|(?<!\\\\)\n/m", $eol, $val);
                }, $eol);
            }
            $str = FoxVarReplace($pagename, $data[$k], '', $tpeach);
            $body .= preg_replace('/\{\$\$\(?(.*?)\)?\}/'," ",$str); //non-rendered template vars get removed
        }        
    }  
    // foot: replacing any vars from (:template last:): for column sums use [{$$SUM_<fieldname>}]         
    if (isset($tplast)) {
        $sums = array();
        foreach($header as $k=>$v) {
            // Pierre Racine
            // Bug for chk_ variable
            if (! is_array($v))
               $sums['SUM_'.$k] = array_sum(array_column($data,$v));
        }
        $str = FoxVarReplace($pagename, $sums, '', $tplast);
        $foot = preg_replace('/\{\$\$\(?(.*?)\)?\}/'," ",$str); 
    }

    return $head.$body.$foot;
}

// determine and set separator/delimiter 
function fxc_Set_Sep( $text, $opt ) {
    $sep = $opt['sep'] ?? $opt['csvsep'] ?? $GLOBALS['FoxCSVConfig']['sep'];
    if ($sep=='')
        if (preg_match('/^(")?IDX\1?(;)/', $text, $m)) {
            $sep = $opt['sep'] = $m[2];
            if ($m[1]=='"') $opt['quotes'] = 1;  //assuming data elements are enclosed in dbl-quotes
        }
        elseif (preg_match('/^(")?(?:{\$\$)?[a-zA-Z][-\w]*}?\1?(.)/', $text, $m)) { //guessing sep is first non-alpha-numeric char after alphanum start
            $sep = $opt['sep'] = $m[2]; 
            if ($m[1]=='"') $opt['quotes'] = 1;  //assuming data elements are enclosed in dbl-quotes
        }
    else $sep = ' ';
    $opt['sep'] =  $sep;
    if ($sep=='\t') $sep = "\t"; //catching tab character
    return array($sep, $opt);
} //}}}

// get text from file or page (section)
function fxc_Get_Text( $pagename, $src ) {
    // get rows array from a file in uploads 
    if (preg_match('/\.(csv|txt)$/i',$src,$ext)) {
            $txt = @fxc_Get_File_Content($pagename, $src);
        if ($txt===FALSE) $GLOBALS['FoxMsgFmt'][] = "'''Error:''' could not find file '''$src'''";
    } else {
        $anc = strstr($src,'#');
        if ($src[0]=='#') $src = $pagename.$src; 
        $pn = MakePageName($pagename, $src);
        $src = $pn.$anc;
        $txt = RetrieveAuthSection($pn, $src);
    }
    if (empty($txt)) return '';
    if (array_key_exists(1,$ext) && strtolower($ext[1])=="txt") { 
        $txt = "Text\n".$txt; // setting header field 'Text' for text import
    }
    return $txt;
} //}}}

// get file contents from uploaded (attached) file
function fxc_Get_File_Content( $pagename, $filename ) {
    global $UploadDir, $UploadPrefixFmt;
    $fpn = explode('/',$filename);
    if (isset($fpn[1])) 
         { $fname = $fpn[1]; $fpre = "/".$fpn[0];  }
    else { $fname = $fpn[0]; $fpre = $UploadPrefixFmt; }
    $uppath = FmtPageName("$UploadDir$fpre", $pagename);
    $csvfile = FmtPageName("$uppath/$fname", $pagename);
    return file_get_contents($csvfile);
} //}}}

// parse text and create two-dimensional data array
function fxc_Parse_CSV( $text, $sep ) {
    $data = explode("\n", $text);
    $data = fxc_Remove_Empty_Rows( $data, $sep); 
 #show($data,'rows');
    // text import with single 'Text' header field
    if ($data[0]=='Text') {
        foreach ($data as &$row) 
            $row = array($row);
        return $data;
    }
    $pat = array( '%25','%2c', '%0a', '%0d', '\\', '%22', '`"'); //tokens
    $rep = array( '%',   $sep,  "\n",  "\r", "\n",   '"', '"'); //restored to characters     
    foreach ($data as $i => $row) {
    # show($row,'row before');
        $row = str_getcsv($row, $sep, "\"", "`"); //parse the items in rows [no enclosure escape character, use double ""]
    # show($row,'row parsed');
        foreach ($row as &$it) {
            $it = str_replace($pat, $rep, $it); // restore characters from tokens
        }
    # show($row,'row adjusted');
        $data[$i] = $row;
    }
    if (isset($data[0]))
      $data[0] = fxc_Normalise_Header($data[0]);
 #show($data,'data');
    return $data;
} //}}}

// making header names with pattern (adjusting input to get valid names)
function fxc_Normalise_Header ( $header ) {
    if (empty($header)) return null;
    
    $hdpat = [
        "/'/" => '',               # strip single-quotes
        "/^\d+/" => 'N$0',         #prepend leading digits with 'N'
        "/[^_[:alnum:]]+/" => ' ', # convert everything else to space
        '/ /' => '-',              # convert space to hyphen
    ];
    $hdr = array_map('trim', $header); //assuming first row is header
    $header = PPRA($hdpat, $hdr); //sanitising header names
    //check for duplicates in header
    $duplis = array_diff_assoc($header, array_unique($header));
    if (!empty($duplis)) {
        $GLOBALS['FoxMsgFmt'][] = "%red%'''Error:''' header has duplicate field names. Edit header!";
    foreach ($duplis as $k=>$v)
        $header[$k] = $v."-duplic";
    }
    foreach ($header as $name)
    if (count($header)==1 ) $GLOBALS['FoxMsgFmt'][] = "%green%'''Warning:''' just one header field! Check if 'sep=..' is correct. ";
    return $header;
} //}}}

// Get item by header and idx: name/idx or col/idx {(csv <source> <name>/<idx>)}
function fxc_Table_Item( $data, $ref, $sep ) { 
    $ref = explode('/', $ref);
    if (!isset($ref[1])) return "'''Error:''' wrong or no name or idx!";
    $col = $ref[0];
    $row = $ref[1];
    //get row by idx number
    if ($data[0][0]=='IDX') {
        foreach ($data as $k => $rw)
            if ($rw[0]==$row) $row = $k; 
    }
    if (!array_key_exists($row,$data)) return "'''Error:''' row '''$row''' does not exist!";
    $el = $data[$row];
    if (!preg_match('/^\d+$/',$col))
        $el = array_combine($data[0], $el); 
    if (!array_key_exists($col,$el)) return "'''Error:''' header column '''$col''' does not exist, or wrong separator!"; 
    return $el[$col];
} //}}}

// filter data array according to query (regular expression or PmWiki pagelist query syntax)
function fxc_Query( &$data, $header, $opt ) {
    $query = ParseArgs($opt['query']);
    $quin = (isset($query[''])) ? implode(',',$query['']) : '';
    $quex = (isset($query['-'])) ? implode(',',$query['-']) : '';
    unset($query['#'], $query[''], $query['-']);
    foreach ($header as $k=>$v) {
        if (array_key_exists($k,$opt) && !empty($opt[$k])) {
            if (array_key_exists($k,$query))
                $query[$k] = ",".$query[$k].",".$opt[$k]; //combine strings if we got double keys
            else $query[$k] = ",".$opt[$k]; 
            $query[$k] = trim($query[$k], ",");
            continue;
        }
    } 
    foreach ($header as $k=>$v) {
        if (!isset($query[$k])) $query[$k] = '';
        if ($quin!='') $query[$k] .= ",".$quin;
        if ($quex!='') $query[$k] .= ",-".$quex;
    }
    if (!array_filter($query)) return;
    // create regex patterns for including and excluding 
    $exclude = $include = array();
    foreach ($query as $field => $pat) {
        if (!in_array($field, $header)) continue;
        if ($opt['regex']==0) {
            list($incl, $excl) = GlobToPCRE($pat);
        }
        $include[$field] = ($opt['regex']==0)? $incl : $pat;
        $exclude[$field] = ($opt['regex']==0)? $excl : "";
    }
    // check each data item in turn
    if (!empty($query))
    {
        $include = array_filter($include, function($v) {
            return $v !== null && $v !== "" && $v !== [];
        });
        if (count($include) > 0)
        {
            $matches = [];
            foreach($data as $i=>$item) {
                foreach($include as $k => $pat) {
                    if ($pat=="") continue;
                    if (isset($opt['case']) && $opt['case']==0)
                        $pat = "(?i)".$pat;
                    if (preg_match("($pat)", $item[$k])) {
                        $matches[$i] = $data[$i];
                        continue 2; 
                    }
                }
            } 
        }
        else {
            $matches = $data;
        }
        if ($opt['regex']==0) //for non-regex queries we may have exclude patterns
        {
            foreach($matches as $i=>$item) {
                foreach($exclude as $k => $pat) {
                    if ($pat=="") continue;
                    if (isset($opt['case']) && $opt['case']==0)
                        { $pat = "(?i)".$pat; }
                    if (preg_match("($pat)",$item[$k])) {
                        unset($matches[$i]);
                        continue 2;
                    }
                } 
            }
        }
        $data = $matches;
    }
} //}}}

// sort a multi-column data array by up to four header fields
function fxc_Multi_Column_Sort(&$data, $header, $sort, $order ) {
    global $FoxCSVConfig;
    global $FoxMsgFmt; 
    $sort = explode(',',$sort);
    if (empty($sort[0])) return;
    $cnt = count($sort); //we want max 4 sort names
    if ($cnt > 4) { $FoxMsgFmt[] = "Warning: no more than 4 sort names allowed!"; 
        for ($i=$cnt-1; $i>3 ; $i--) 
        unset($sort[$i]); //reduce list to 3 names
        $cnt = 4;
    }
    $order = ParseArgs($order); unset($order['#']);
    $dir = $col = $flags = array();
    // set sort direction, general or by sort field
    foreach ($sort as $k=>$n) {
        if (substr($n, 0,1)=='-') {
            $sort[$k] = ltrim($n, '-'); $dir[$k] = SORT_DESC;
        } else { $sort[$k] = $n; $dir[$k] = SORT_ASC; }
        if (isset($order['-'])) $dir[$k] = SORT_DESC;
        if (isset($order[$n])) { 
            if (substr($order[$n], 0,1)=='-') {
                $order[$n] = ltrim($order[$n], '-'); 
                $dir[$k] = SORT_DESC; //the specific overrides the general
            } else $dir[$k] = SORT_ASC;
        }
    }
    // get columns for array_multisort(), set flags, pass to array_multisort() according to number of sort columns
    $so = array();
    foreach ($sort as $k=>$n) {
        if (!in_array($n,$header)) { 
            $FoxMsgFmt[] = "Error: Could not sort Data. Invalid ''sort'' parameter!"; return; 
        }
        $col[$k]  = array_column($data, $n);
        // set sort order flags for each sort field
        if (in_array($n,array_keys($order))) $so[$k] = $order[$n];
        else if (isset($order['']))          $so[$k] = $order[''][0];
        else if (isset($order['-']))         $so[$k] = $order['-'][0];
        else                                 $so[$k] = $FoxCSVConfig['order']; //default
        // setting sort flags array $fl for array_multisort()
        switch($so[$k]) {
            case 'reg':   
            case 'regular': $fl[$k] = SORT_REGULAR; break;// default; compare items normally (don't change types)
            case 'nat':  
            case 'natural': $fl[$k] = SORT_NATURAL; break; // compare items as strings using "natural ordering" like natsort()
            case 'natcase': $fl[$k] = SORT_NATURAL|SORT_FLAG_CASE; break; // sort as strings natural and case-insensitively 
            case 'str': 
            case 'string': $fl[$k] = SORT_STRING; break; // compare items as strings
            case 'strcase': $fl[$k] = SORT_STRING|SORT_FLAG_CASE; break;// sort as strings and case-insensitively
            case 'num':   
            case 'numeric': $fl[$k] = SORT_NUMERIC; break; // compare items numerically
            case 'loc': 
            case 'locale': $fl[$k] = SORT_LOCALE_STRING; break; // compare items as strings, based on the current locale. It uses the locale, which can be changed using setlocale()
            default: $fl[$k] = SORT_REGULAR; break;// default; compare items normally (don't change types)
        }
    }
    switch ($cnt) { //do the ordering according to sort col number
        case '0': $FoxMsgFmt[] = "Error: Missing or invalid sort parameters!"; break;
        case '1': array_multisort($col[0], $dir[0] , $fl[0], $data); break;         
        case '2': array_multisort($col[0], $dir[0] , $fl[0], $col[1], $dir[1], $fl[1], $data); break; 
        case '3': array_multisort($col[0], $dir[0] , $fl[0], $col[1], $dir[1], $fl[1], $col[2], $dir[2], $fl[2], $data); break;  
        case '4': array_multisort($col[0], $dir[0] , $fl[0], $col[1], $dir[1], $fl[1], $col[2], $dir[2], $fl[2], $col[3], $dir[3], $fl[3],$data); break;
    }
} //}}}

function fxc_Auto_Table($pagename, $data, $header, $opt, $num) {
    // show specific columns only, or to exclude certain columns
    $source = $header['SOURCE'];
    if (isset($opt['show'])) 
        $header = fxc_Fields($opt['show'], $header);
    //remove fields not for display
    unset($header['SOURCE']);
    // Pierre Racine Preview
    // Unset all parameter passed to the template so they are not added to the auto table
    foreach ($header as $key => $value) {
        if (strpos($key, "FOXCSV_") !== false) {
            unset($header[$key]);
        }
    }
    if ($opt['hideidx']==1) unset($header['IDX']);
    // set cell alignment
    $ralf = $calf = array();
    if (isset($opt['ralign'])) $ralf = fxc_Fields($opt['ralign'], $header); //right aligned columns
    if (isset($opt['calign'])) $calf = fxc_Fields($opt['calign'], $header); //centred columns
    // build template string
    $tmpl= "(:table:)\n";
    $tmpl = '';
    $header = array_values($header); //index
  #show($header);
    // format numbers in 'num=' specified fields: displays rounded to n decimal places, sets decimal and thousands separtors 
    if (isset($opt['num'])) {
        $numf = fxc_Fields($opt['num'], $header);
        $ralf = array_merge($ralf, $numf); // num=... number fields get right-aligned
        foreach ($data as &$row)
            foreach ($row as $k => $it)
                if (in_array($k, $numf) && is_numeric($it))
                    $row[$k] = number_format($it, $opt['decimals'], $opt['decisep'], $opt['thousep']);

    }
    foreach ($header as $k=>$v) {
        $nr = ($k==0) ? "nr" : '';
        if (in_array($v, $calf)) $al='center'; 
        elseif (in_array($v, $ralf)) $al='right';
        else $al='left';
        $tmpl .= "(:cell$nr align=$al:){\$\$".$v."} \n";
    }
    if ($opt['editlinks']==1 && CondAuth($pagename,'edit')) { // insert delete and edit links for each row
        
        if ($GLOBALS['FoxCSVConfig']['saveasnew']==1 && $opt['saveasnew']==1) {
            if ($opt['new']=='top') $opt['saveasnew'] = 'top';
            else $opt['saveasnew'] = 'bottom';
        }
    
        // Pierre Racine Multiline
        // Pass-on the multiline parameter when it is set in the (:csv markup
        // So that double quotes are automatically added only to the multiline fields
        $template = $tmpl."(:cell:)".
                    "(:csv-delete target={\$\$SOURCE} idx={\$\$IDX} sep=\"".$opt['sep']."\"".
                    (isset($opt['multiline']) ? " multiline={$opt['multiline']} " : '').":)".
                    "(:csv-edit {\$\$SOURCE} idx={\$\$IDX} sep={$opt['sep']} ". 
                    (isset($opt['env']) ? " env={$opt['env']} " : '').
                    (isset($opt['multiline']) ? " multiline={$opt['multiline']} " : '').
                    (isset($opt['saveasnew']) ? " saveasnew={$opt['saveasnew']} " : '').
                    ":)\n";
    }
    else $template = $tmpl."\n";
    $head = $foot = $body = '';
    // make table rows (body)
    for ($k=0; $k<count($data); $k++) {
        $str = FoxVarReplace($pagename, $data[$k], '', $template);
        $body .= preg_replace('/\{\$\$\(?(.*?)\)?\}/'," ",$str); //non-rendered template vars get removed
    }  
   # show($data);
    $class = "class='csvtable'"; 
    // build foot row with sum fields
    if (isset($opt['sum'])) {
        if ($opt['sortable']==1) $class = "class='sortable-footer csvtable'"; //sortable-footer prevents last row from being included in sort
        $sumf = fxc_Fields($opt['sum'], $header);
        foreach ($header as $k=>$hd) {
            $nr = ($k==0) ? "nr" : '';
            if ($hd=='IDX') 
                 $foot .= "(:cell$nr:) \n"; 
            else if (in_array($hd, $sumf)) {
                $col = array_column($data, $hd);
                $sum = 0;
                foreach ($col as $i => $c) {
                    if (isset($numf) && in_array($hd, $numf)) {
                        $c = str_replace($opt['thousep'],'', $c);
                        if ($opt['decisep']!='.')
                            $c = str_replace($opt['decisep'],'.', $c);
                    }
                    if (is_numeric($c)) $sum += (float)$c;
                }
                if (isset($numf) && in_array($hd, $numf))
                     $sum = number_format($sum, $opt['decimals'], $opt['decisep'], $opt['thousep']);
                $sum = "%tsum%".$sum;

                if (in_array($hd, $calf)) $al='center';
                elseif (in_array($hd, $ralf)) $al='right';
                else $al='left';
                $foot .= "(:cell$nr align=$al:)$sum \n";
            }
            else $foot .= "(:cell$nr:) \n"; //no sum for this column
        }
    }
    //make csv table; insert copy form first in head
    if (CondAuth($pagename,'admin') && $opt['editlinks']==1) {
        $copyform = '';
        if ($opt['copycsv']==1) {
            fxc_Copy_To_CSV ($pagename, $data, $header, $opt, $num); // generate new csv text and put into $InputValues
            $copyform = "\n(:fox copycsv class='csvform':)".
            "(:input submit post '$[Copy to Page]':)&nbsp;(:input text target size=9:)". 
            "(:foxtemplate \"{\$\$text$num}\":)\n(:input hidden text$num :)".
            "(:foxend copycsv:)\n";
        } 

        // add toolbar with csv-action forms
        if (!empty($opt['toolbar']) )
            $head .= "(:div class='csvtoolbar':)(:csv-index $source:)(:csv-reindex $source:)(:csv-trim $source:)(:csv-quote $source:)".
                    "(:csv-reformat $source:)(:csv-newcol $source:)(:csv-coldel $source:)(:csv-export $source:)$copyform\n(:divend:)\n";
        elseif ( $opt['copycsv']==1 )
            $head .= $copyform;
    }
    // build header row as first row for output, the others are added after var substitutions by FoxVarReplace
    if ($opt['header']==0)
        $head .= "(:table:)\n";
    else {
        $htpl = '';
        if ($opt['sortable']==1 && empty($opt['sum']))  
            $class = "class='sortable csvtable'"; // last row is included for sorting
        $htpl .= "(:table $class :)\n"; //sortable tables need to be enabled in config via: $EnableSortable = 1;
        foreach ($header as $k=>$v) {
            $nr = ($k==0) ? "nr" : '';
            if ($v=='IDX') $v = $opt['idxname']; // give IDX a friendlier name, but IDX stays internally as special name
            if ($v=='Text') $v = $opt['textname'];
            if (in_array($v, $calf)) $al='center';
            elseif (in_array($v, $ralf)) $al='right';
            else $al='left';
            $htpl .= "(:head$nr align=$al:)$v \n";
        }
        $header['SOURCE'] = $source; // add SOURCE back in
        if ($opt['editlinks']==1 && CondAuth($pagename,'edit')) {
            $nw = ($opt['new']=='top') ? 'newtop' : 'new';
            $htpl .=  "(:head:)".
                    (CondAuth($pagename,'admin')? "(:csv-edit {\$\$SOURCE} idx=header sep=\"".$opt['sep']."\" :) | " : ""). 
                    "(:csv-edit {\$\$SOURCE} idx='$nw' sep=\"".$opt['sep']."\" label='Add +'".
                    (isset($opt['multiline']) ? " multiline={$opt['multiline']} " : '').
                    ":)";
        }
        $head .= FoxVarReplace($pagename, $header, '', $htpl)."\n";
    } 
    $foot .= "(:tableend:)";
    return $head.$body.$foot;
} //}}}

// create csv text and set to $InputValues for copy form
function fxc_Copy_To_CSV( $pagename, $data, $fields, $opt, $num ) {
    $sep = $opt['newsep'] ?? $opt['sep'];
    $head = ($GLOBALS['EnablePostDirectives']==1)? "(:csv #data sep=$sep:)\n(:if false:)\n[[#data]]\n" : "";
    $tmpl = '';
    foreach ($fields as $n) {
        $tmpl .= "{\$\$".$n."}".$sep;
        $head .= $n.$sep;
    }
    $tmpl = rtrim($tmpl,$sep)."\n"; //trim trailing separators, add line breaks
    $head = rtrim($head,$sep)."\n";
    $body = '';
    if (array_key_exists('header',$data)) unset($data['header']);
    // enclose items in quotes if they contain separator or newline characters 
    foreach ($data as &$row)
        foreach ($row as &$it)
           if (preg_match('/\n/',$it) OR strstr($it, $sep)) 
            $it = '"'.$it.'"'; // "enclose item" if needed

 #show($data,'data makecsv');
    foreach ($data as $k=>$v) {
        $str = FoxVarReplace($pagename, $v, '', $tmpl);
        $str = fxc_Make_CSV_Row ($str, $sep, $opt['env'])."\n";
        $body .= preg_replace('/\{\$\$\(?(.*?)\)?\}/'," ",$str);
    }  
    $foot = ($GLOBALS['EnablePostDirectives']==1)? "[[#dataend]](:ifend:)\n\n" : "";
    // set $InputValues for hidden input field when copoying/importing
    $text = $head.$body.$foot; 
 #show($text,'copytext:');
    $GLOBALS['InputValues']['text'.$num] = $text;
} //}}}

function fxc_Write_To_File ( $pagename, $filename, $text ) {
    global $UploadDir, $UploadPrefixFmt, $FoxMsgFmt;
    $fpn = explode('/',$filename);
    if (isset($fpn[1])) 
         { $fname = $fpn[1]; $fpre = "/".$fpn[0];  }
    else { $fname = $fpn[0]; $fpre = $UploadPrefixFmt; }
    $uppath = FmtPageName("$UploadDir$fpre", $pagename);
    $file = FmtPageName("$uppath/$fname", $pagename);
    $backup = preg_replace("/.csv$/","-backup.csv",$file);
    if (file_exists($file)) copy($file, $backup);
    $fp = fopen($file, 'w');
    fwrite($fp, $text);
    fclose($fp);
} //}}}

// make single text line into a valid csv record (row)
function fxc_Make_CSV_Row($row, $sep, $env='') {
    #show($row,'row');
    $items = $row;
    if (!is_array($items)) {
        $items = str_getcsv($items, $sep, "\"", "`");
    }
    foreach ($items as &$str) {
       $str = fxc_Make_CSV_Item($str, $sep, $env);
    }
    $row = implode($sep, $items); //return row as string
    return $row;
} //}}}

// make single csv item. Replace line breaks with tokens, add quotes to quoted parts, enclose items in quotes if needed
function fxc_Make_CSV_Item( $str, $sep, $env='' ) {
    #show($str,'str 1');    
        $str = preg_replace('/\r\n?/', "\n", $str); // replace any CR or CRNR with NL
        $str = preg_replace('/(?<!`)"/','`"', $str); // escape quotes with ` accent char
   # show($str,'str 2');
        // Pierre Racine Multiline
        // We don't need this anymore. This is producing CSV data incompatible with other softwares (Excell, Google Sheet)
        //$str = str_replace("\n", "\\", $str); // replace line breaks with \
        $str = trim($str); // remove any white spaces at start and end
        if (preg_match('/\n/',$str) OR strstr($str, $sep) OR $env==1) { 
            if (!is_numeric($str))
            $str = '"'.$str.'"'; //enclose item if needed
        }
   #show($str,'str 3');     
        return $str;
} //}}}

// takes rows array and unsets empty rows, and reindexes array
function fxc_Remove_Empty_Rows( $rows, $sep ) {
    if (!is_array($rows))
        $rows = array(0=>$rows);
    foreach ($rows as $k=>&$v) {
        $rows[$k]  = trim($v,"\r\n"); //not trimming seps at right!
        if (empty($rows[$k])) unset($rows[$k]);
    } 
    $rows = array_values($rows); //reindex
    return $rows; 
} //}}}

// returns a field array as subset of header according to arg parameter [called from fxc_Auto_Tables] 
function fxc_Fields( $arg, $header ) {
    if ($arg==1) return $header;
    $args = explode(',', $arg);
    $flg = 1;
    foreach ($args as $k=>$v)
        if ($v[0]=='-') { $args[$k] = substr($v,1); $flg = 0; continue;  }
    if ($flg==1) return $args;
    else return array_diff($header, $args);
} //}}}

// combines arrays of different sizes by special paddings. Code from comment on PHP Manual:array_combine
function fxc_Array_Combine_Special( $a, $b, $pad = TRUE ) {
    $acount = count($a);
    $bcount = count($b);
    // more elements in $a than $b but we don't want to pad either
    if (!$pad) {
        $size = ($acount > $bcount) ? $bcount : $acount;
        $a = array_slice($a, 0, $size);
        $b = array_slice($b, 0, $size);
    } else {
        // more headers than row fields
        if ($acount > $bcount) {
            $more = $acount - $bcount;
            // Add empty strings to ensure arrays $a and $b have same number of elements
            $more = $acount - $bcount;
            for($i = 0; $i < $more; $i++) {
                $b[] = " "; //HB: add space, not empty string, so replacement variable can match something!
            }
        // more fields than headers
        } else if ($acount < $bcount) {
            $more = $bcount - $acount;
            // fewer elements in the first array, add extra keys        
            for($i = 0; $i < $more; $i++) {
                $key = 'extra_0' . $i;
                $a[] = $key;
            }
        }
    }
    return array_combine($a, $b);
} //}}}

function fxc_Get_Row_Key($rows, $idx, $sep, $modeCSV=false ) {
    $idxk = 0;
    if ($modeCSV){
       $idxk = array_search('IDX', $rows[0]);
    }
    $idnr = array();
    $cnt = count($rows);
    foreach ($rows as $k => $row) {
        if (!$modeCSV){
            $row = explode($sep, $row);
        }

        if (is_numeric($row[$idxk])) {
            if ($row[$idxk]==$idx) return $k; //return row key
            $idnr[] = $row[$idxk];
        }
    }
    $max = (!empty($idnr)) ? (int)max($idnr) : 0;
    if (preg_match('/^new(top|\d+)?/', $idx)) return $max + 1; //return next from highest for new entries
    return $idx; //return idx if no row key is found
} //}}}

//===================================//
// csv mod button markup {[foxcsv... target={$$SOURCE} idx={$$IDX} label='&nbsp;X&nbsp;']} 
Markup('foxcsvact', 'directives', '/\(:csv-(del|delete|index|reindex|reformat|trim|quote|table|coldel|newcol|import|export)\\s+(.*?)\\s*:\)/',
       "fxc_Action_Form_Fmt");
# Creates the HTML output for csv action button
function fxc_Action_Form_Fmt( $m ) { 
    global $FoxCSVConfig, $ScriptUrl, $EnablePathInfo;
    extract($GLOBALS['MarkupToHTML']);

    $act = $m[1];
    $opt = ParseArgs($m[2]);
    unset($opt['#']);
    $opt[''] = (array)@$opt[''];
    if ($act=='import') {
        $filename = array_shift($opt['']);
    }
    $target = $opt['target'] ?? array_shift($opt['']) ?? $pagename;
  #show($opt,'opt');
    if ($target=='') return;
    // Pierre Racine Preview
    // When IDX is empty, display a message instead of doing nothing
    //if ((empty($opt['idx']) || $opt['idx']=='IDX') && ($act=='del' || $act=='delete')) return;
    $idx =  $opt['idx'] ?? '';
    $csum = '$[Table updated]';
    $imageurl = '';
    // set defaults for labels and titles (mouse-over tooltips)
    if (isset($opt['label'])) {
        $label = $opt['label'];
        $title = $opt['title'] ?? $label;
    }
    // set labels, titles, messages
    else switch ($act) {
            case 'del':
            case 'delete':  $imageurl = $FoxCSVConfig['deletebuttonurl']; 
                            $label = 'X'; $title = "$[Delete row] $idx";
                            $onclickmessage = '$[Please confirm: Do you want to delete this csv row?]';
                            $csum = '$[Table row deleted]'; break;
            case 'index':   $label = '$[Index]'; $title = "$[Index] $target";
                            $onclickmessage = '$[Please confirm: Do you want to index this csv table?]';
                            $csum = '$[CSV Table Index added]'; break;
            case 'reindex': $label = '$[Re-Index]'; $title = "$[Re-index] $target";
                            $onclickmessage = '$[Please confirm: Do you want to reindex this csv table?]';
                            $csum = '$[CSV Table re-indexed]'; break;
            case 'reformat':$label = '$[Reformat]'; $title = "$[Reformat with new separator] $target";
                            $onclickmessage = '$[Please confirm: Do you want to reformat this csv table?]';
                            $csum = '$[CSV Table reformatted]'; break;
            case 'trim':    $label = '$[Trim Quotes]'; $title = "$[Trim surrounding quotes and white spaces from] $target"; 
                            $onclickmessage = '$[Please confirm: Do you want to trim items of this csv table?]';
                            $csum = '$[CSV Items trimmed]'; break;
            case 'quote':   $label = '$[Add Quotes]'; $title = "$[Enclose with double quote marks on] $target"; 
                            $onclickmessage = '$[Please confirm: Do you want to add quote marks to items of this csv table?]';
                            $csum = '$[CSV Items enclosed in quote marks]'; break;
            case 'table':   $label = '$[Make Table]'; $title = "$[Make Table] $target"; 
                            $onclickmessage = '$[Please confirm: Do you want to convert csv table into simple table format?]';  break; 
            case 'coldel':  $label = '$[Delete Column]'; $title = "$[Delete Column on] $target"; 
                            $onclickmessage = '$[Please confirm: Do you want to delete this column from the table?]';
                            $csum = '$[Column deleted]'; break;
            case 'newcol':  $label = '$[Add New Column]'; $title = "$[Add New empty Column to] $target";
                            $onclickmessage = '$[Please confirm: Do you want to add this new column to the table?]';
                            $csum = '$[New column added]'; break; 
            case 'import':  $label = '$[Import]'; $title = "$[Import] $target";
            case 'export':  $label = '$[Export]'; $title = "$[Export] $target";
        }
    $sep = $opt['sep'] ?? $FoxCSVConfig['sep'] ?? '';
    $env = $opt['env'] ?? $FoxCSVConfig['env'] ?? 0;
    $col = $opt['col'] ?? '';
    // sanitising target parameter, so it has group, name and possible anchor
    $csvfile = 0;
    if (preg_match('/\.(csv|txt)$/i', $target, $m)) { //file update
        $csvfile = 1; //file edit flag
        $TargetPageUrl = PUE(($EnablePathInfo) ? "$ScriptUrl/$pagename" : "$ScriptUrl?n=$pagename");
        $tpn = $pagename;
    } else { //page /section update
        $target = FoxMakeFullTarget($pagename, $target);
        $tpn = explode("#",$target)[0];
        $TargetPageUrl = PUE(($EnablePathInfo) ? "$ScriptUrl/$target" : "$ScriptUrl?n=$tpn");
    }
    // javascript delete message dialogue
    $onclick = ($FoxCSVConfig['popups']==true)? "onclick='return confirm(\"{$onclickmessage}\")'" : "";
    // construct HTML delete button as output, additional input given via foxfilter function
    $out = "\n<form  class='csvform' name='FoxCSV-$act' action='{$TargetPageUrl}' method='post' >".
                "<input type='hidden' name='foxpage' value='{$pagename}' />".
                "<input type='hidden' name='foxname' value='CSVUpdate' />".
                "<input type='hidden' name='action' value='foxpost' />".
                "<input type='hidden' name='foxaction' value='csv' />".
                "<input type='hidden' name='foxtemplate' value='csv' />".
                (isset($opt['multiline'])? "<input type='hidden' name='multiline' value='{$opt['multiline']}' />" : "").
            "<!--input type='hidden' name='foxfilter' value='csvaction' /-->".
                "<input type='hidden' name='csvact' value='$act' />".
                "<input type='hidden' name='csum' value='$csum' />".
                "<input type='hidden' name='csvsep' value='$sep' />".
                "<input type='hidden' name='csvfile' value='$csvfile' />".
                (isset($opt['newsep'])? "<input type='hidden' name='newsep' value='{$opt['newsep']}' />" : "").
                ($env==1 ? "<input type='hidden' name='csvenv' value='1' />" : "").
                (isset($opt['new']) && $opt['new']=='top' ? "<input type='hidden' name='reverseindex' value='1' />" : "").
                ($act=='reformat' ? "NewSep:<input type='text' size=1 name='newsep' value='' />" : "").
                ($act=='coldel' ? "Col:<input type='text' size=1 name='coldel' value='$col' />" : "").
                ($act=='newcol' ? "New:<input type='text' size=9 name='colname' value='' />" : "").
                ($act=='newcol' ? " After:<input type='text' size=1 name='addafter' value='' />" : "").
                "<input type='hidden' name='target' value='$target' />".
                "<input type='hidden' name='csvidx' value='$idx' />". 
                ($act!='import' ? "<input type='hidden' name='redir' value='$pagename' />" : "").
                (!empty($imageurl) ? "<input type='image' name='post' alt='$label' class='inputbutton' src=$imageurl title='$title' {$onclick} />"
                : "<input type='submit' name='post' value='$label' title='$title' class='inputbutton' {$onclick} />").
                ($act=='import' ? " to:<input type='text' size=10 name='importto' value='' />" : "").
                ($act=='export' ? " to:<input type='text' size=10 name='exportto' value='' />" : "").
                "</form>";
    return Keep(FmtPagename($out,$tpn));
} //}}}

// gets text content from csv file and fills template
$FoxFilterFunctions['csvimport'] = 'fxc_Import_Filter';
function fxc_Import_Filter ( $pagename, $fx) {
    if (!preg_match('/\.(csv|txt)$/i',$fx['filename'],$m)) 
        FoxAbort($pagename, "$[Error: Wrong file type!]");
    $text = fxc_Get_File_Content($pagename, $fx['filename']);
    if (strtolower($m[1])=='csv')
         $tmpl = "(:csv #data:)\n(:if false:)\n[[#data]]\n".$text."\n[[#dataend]]"; //text into data section and added csv markup
    else $tmpl = $text; //plain text import
    $fx['foxtemplate'] = $tmpl; 
    $fx['csum'] = 'CSV file import';
    return $fx;
} //}}}

// pre-process input
// if  submit post2 button is used, 'saveasnew' option is processed for correct placement
// with calls to fxc_Make_CSV_Item() quoted text gets double quoted, newline replaced with tokem, items with separator characters enclosed with quotes
#$FoxFilterFunctions['csvedit'] = 'fxc_Edit_Filter';
function fxc_Preprocess_Input($pagename, &$fx) {
    #DEBUG  show($fx,'fx Edit Filter begin');
    if ($fx['csvact']=='export') {
        $GLOBALS['EnableFoxDefaultMsg'] = 0;
        return;
    }
    unset($fx['put']);
    //submit post2 'Save as New': change csvact to 'addnew'
    if (isset($fx['post2'])) $fx['csvact'] = 'addnew';
    if ($fx['csvact']!='replace' OR $fx['csvact']!='addnew') return;
    // get header field names and sep from template (reverse engineer)
    // correct any field input from these names
    $temp = $fx['foxtemplate']; #show($temp,'template');
    $env = (!empty($fx['csvenv'])) ? 1 : 0;
    if (preg_match_all('~\{\$\$(.*?)\}(.)?~', $temp, $m)) {
        $sep = $m[2][0]; 
        foreach ($m[1] as $k=>$name) {
            if (!empty($fx[$name])) {
                $fx[$name] = fxc_Make_CSV_Item($fx[$name], $sep, $env);
            }
        } 
    }
    #DEBUG show($fx,'fx Edit Filter end'); #exit;
    return;
} //}}}

// processing various csv-modifying actions via parameter 'csvact=...' from a fox form (i.e. from fxc_Action_Form_Fmt)
// called via 'foxaction=csv' from FoxProcessTargets() in fox.php 
function FoxCSV_Update($pagename, $text, $newline, $fx) {
    global $FoxDebug, $FoxCSVConfig, $FoxMsgFmt; 
    if ($FoxDebug) { echo "<br /><i>FoxCSV_Update></i> "; show($fx['csvidx'],'idx'); show($newline,'newline'); }
    if (empty($fx['csvact'])) { 
        $FoxMsgFmt[] = "Error: no csv update action provided"; 
        return $text; 
    }
    $idx = $fx['csvidx'] ?? null;
    $env = $fx['csvenv'] ?? '';
    if (isset($fx['csvfile']) && $fx['csvfile']==1) 
        $text = fxc_Get_Text($pagename, $fx['target']);
    // Pierre Racine Typo
    $text = trim($text,"\r\n");
    list($sep, $fx) = fxc_Set_Sep($text, $fx);
    if ($sep=='\t') $sep = "\t";
    $fx['csvsep'] = $sep;
    
    // Pierre Racine Empty CSV
    // Get the template from parameters in case the CSV is empty and we need to build a default header
    if (isset($fx['foxtemplate']) && $fx['foxtemplate'] !== 'csv'){
        $template = trim($fx['foxtemplate']);
    }
    elseif (isset($fx['template'])){
        $template = RetrieveAuthSection ($pagename, $fx['template'], $list=NULL, $auth='read');
        $template = trim($template);
    }
    // Pierre Racine Multiline
    // If set in the config, use a different CSV parser (able to parse double quoted values containing \n properly)
    // and generate the rows to be written in the page using the template (so multiline values stay double quoted)
    $rows = array();
    if (isset($FoxCSVConfig['csvparser']) && function_exists($FoxCSVConfig['csvparser'])){
        $data = $FoxCSVConfig['csvparser']($text, $sep);
        if (!empty($data)){
            array_walk($data, function(&$a) use ($data) {
                $a = fxc_Array_Combine_Special($data[0], $a);
            });
            $header = array_shift($data);
            if (empty($template)) {
                // ##doublequote
                $template = fxc_Auto_Template($header, $sep);
            }
            $rows[] = implode($sep, $header);
            foreach ($data as $row){
                $rows[] = FoxVarReplace($pagename, array_merge($fx, $row), null, $template);
            }
        }
    }
    else
        $rows = explode("\n", $text);
    $rows = fxc_Remove_Empty_Rows($rows, $sep);
    $cnt = count($rows);
    if ($FoxDebug >1) show($rows,'rows old'); #show($idx,'idx');
    if (empty($rows) && ! empty($template)) {
        // Pierre Racine
        // Try to build a header from the template
        $rows[] = preg_replace('/\{\$\$([^{}]+?)\}/', "$1", $template);
    }
    // make idx for new item for custom form submissions
    if ($fx['csvact']=='addnew') {
        if ($idx=='new') $idx = $cnt; //add to bottom
        if ($idx=='newtop') $idx = 0; //add to top
    }
    if (empty($rows)) return $text;
    // 'Save as New' post (submit post2 button)
    if (isset($fx['post2'])) { 
        if (!isset($fx['saveasnew'])) $fx['saveasnew'] = $FoxCSVConfig['saveasnew'];
        if ($fx['saveasnew']==1) $fx['saveasnew'] = 'bottom';
        if (isset($fx['IDX'])) 
            $fx['IDX'] = $cnt; //put new IDX
        switch ($fx['saveasnew']) { //saveasnew==1 is changed to 'bottom' in AutoTable function!
            case 'bottom': $idx =  $cnt; break;
            case 'top':    $idx = 0; break;
            case 'above':  if ($fx['csvidx']>0) $idx = $fx['csvidx'] - 1; break;
            case 'below': break;
            case '0': FoxAbort($fx['foxpage'], "Error: 'Save as New' is not enabled!"); break;
        }
    }
    switch ($fx['csvact']) {
        // add a new record after row $idx
        case 'addnew':
            $pos = (int)$idx +1;
            array_splice($rows, $pos, 0, $newline);
            break;
        // update single row (replace original row)
        case 'replace':
            if ($idx=='0') {  // normalise header fields!
                $row = fxc_Normalise_Header(str_getcsv($newline, $sep));
                foreach ($row as &$it) $it = fxc_Make_CSV_Item($it, $sep, $fx);
                $rows[0] = implode($sep, $row);
            }
            else  $rows[$idx] = $newline;
            break;
        // remove single row according to idx given. row number can be different from idx number for indexed tables
        case 'del':
        case 'delete': 
                $key = fxc_Get_Row_Key($rows, $idx, $sep); 
                unset($rows[$key]);
                $FoxMsgFmt[] = "CSV row deleted";
                break;
        // add a first field with index numbers and 'IDX' in the header row    
        case 'index':  
                if (preg_match('/^IDX/', $rows[0])) FoxAbort($fx['redir'], "Error: IDX field already present, table may need re-indexing instead. No index added.");
                $rows[0] = "IDX".$sep.$rows[0];
                for ( $i=1; $i < $cnt; $i++ ) {
                 $n = (isset($fx['reverseindex'])) ? $cnt-$i : $i;
                    $rows[$i] = $n.$sep.$rows[$i];
                } 
                $FoxMsgFmt[] = "CSV table index added";
                break;
        // refresh index numbers if csv table has index (IDX column as first column)     
        case 'reindex': 
                if (preg_match("/^(?!IDX$sep).*/", $rows[0])) {
                    FoxAbort($fx['redir'], "Error: IDX field missing, table may need indexing first. Reindex failed");
                    return $text; 
                }
                for ( $i=1; $i<$cnt; $i++ ) {
                    $n = (isset($fx['reverseindex'])) ? $cnt-$i : $i;
                    if (preg_match("/^([\d]+$sep)/", $rows[$i], $m))
                        $rows[$i] = preg_replace('/^([\d]+)/', $n, $rows[$i]);
                    else {
                        FoxAbort($fx['redir'], "Error: check row $k for invalid IDX entry! Reindex failed");
                        return $text; 
                    }
                }
                $FoxMsgFmt[] = "CSV table reindexed";
                break;
        // replace separator with new separator    
        case 'reformat':
                if (empty($fx['newsep']) OR strlen($fx['newsep'])>1) {
                    FoxAbort($fx['redir'], "Error: new separator 'newsep' missing or bad. Reformat failed.");
                    return $text;
                } 
                if ($fx['newsep']=='\t') $fx['newsep'] = "\t";
                foreach ($rows as &$row) {
                    $row = str_getcsv($row, $sep, "\"", "`");  
                    foreach ($row as &$it) $it = fxc_Make_CSV_Item($it, $sep, $env);
                    $row = implode($fx['newsep'], $row);
                }
                $FoxMsgFmt[] = "CSV table reformatted";
                break;
        // rewrite csv data table with PmWiki simple table markup    
        case 'table': 
                $rows[0] = "||class='sortable csvtable'\n||!".preg_replace("~(\s?$sep\s*)~", " ||!", $rows[0])." ||";
                $rows[0] = str_replace("IDX", $FoxCSVConfig['idxname'], $rows[0]);
                for($k=1; $k<count($rows); $k++) 
                    $rows[$k] = "||".preg_replace("~(\s?$sep\s*)~", " ||", $rows[$k])." ||";
                $FoxMsgFmt[] = "CSV table converted to simple table";
                break;
        // trim white spaces around items    
        case 'trim': 
                foreach ($rows as &$row) {
                    $row = str_getcsv($row, $sep, "\"", "`"); 
                    foreach ($row as &$it) $it = fxc_Make_CSV_Item($it, $sep);
                    $row = implode($sep, $row);
                }
                $FoxMsgFmt[] = "CSV items trimmed";
                break;;
        //add double quotes to start and end of each item    
        case 'quote': 
                foreach ($rows as &$row) {
                    #if ($k==0) continue; //excludes header
                    $row = str_getcsv($row, $sep, "\"", "`"); 
                    foreach ($row as &$it) {
                        $it = fxc_Make_CSV_Item($it, $sep, 1);
                       # if (!preg_match('/^".*?"$/',$it))
                       #     $it = "\"$it\"";
                    }
                    $row = trim(implode($sep,$row));
                } 
                $FoxMsgFmt[] = "Double Quotes added to CSV items";
                break;
        // remove entire data column from csv table    
        case 'coldel':  
                if (empty($fx['coldel'])) FoxAbort($fx['redir'], "Error: column not specified");
                $col = (int)$fx['coldel'] - 1;
                foreach ($rows as &$row) {
                    $row = str_getcsv($row, $sep, "\"", "");
                    unset($row[$col]);
                    foreach ($row as &$it) $it = fxc_Make_CSV_Item($it, $sep);
                    $row = trim(implode($sep,$row));
                } 
                $FoxMsgFmt[] = "CSV table column deleted";
                break;
        // add new column with header name and empty column fields
        case 'newcol': 
                $name = (!empty($fx['colname'])) ? $fx['colname'] : 'New'; // 'New' as placeholder, because no name was given
                if (preg_match("/$sep|\s/",$name)) {
                    FoxAbort($fx['redir'], "'''Error: Invalid header name!''' Adding new column failed");
                    return $text;
                }
                $header = str_getcsv($rows[0], $sep);
                $hdcnt = count($header);
                $num = ($fx['addafter']=='') ? $hdcnt : $fx['addafter'];
                if ((int)$num > (int)$hdcnt) $num = $hdcnt; //column number should not be higher than count, i.e. just one higher than actual
                //for each row: make csv array, slice into two, add the new between and rebuild row
                foreach ($rows as $k=>$v) {
                    $row = str_getcsv($v, $sep);
                    foreach ($row as &$it) $it = fxc_Make_CSV_Item($it, $sep); 
                    $rwa = array_slice($row,0,(int)$num);
                    $rwb = array_slice($row,(int)$num);
                    $new = ($k==0) ? $name : "";
                    $a = (isset($rwa[0])) ? trim(implode($sep,$rwa)).$sep : '';
                    $b = (isset($rwb[0])) ? $sep.trim(implode($sep,$rwb)) : '';
                    $rows[$k] = $a.$new.$b;
                } 
                $FoxMsgFmt[] = "CSV table column added";
                break;
        // save all to csv file
        case 'export': 
                $fx['target'] = $fx['exportto'];
                $FoxMsgFmt[] = "Success: exported text to file {$fx['exportto']}";
                break;
    }
    if ($FoxDebug >1) show($rows,'rows new'); #exit;
    //rebuild text from rows array
    $text = implode("\n", $rows);
    $text = rtrim($text);
    if (isset($fx['csvfile']) && $fx['csvfile']==1) {
        if ($GLOBALS['FoxCSVConfig']['fileedit']==0) 
             FoxAbort($fx['redir'],"Error: file editing is not allowed");
        else fxc_Write_To_File($pagename, $fx['target'], $text);
        return '';
    }
    return $text;
} //}}}

// Pierre Racine Multiline
// automatically build template from header, sep and multi (to surround field with double quotes)
function fxc_Auto_Template($header, $sep = ",") {
    $t = "";
    foreach ($header as $n){
        $t .= "{\$\$".$n."}$sep";
    }
    // remove the last separator
    if (strlen($t) > 0)
        $t = substr($t, 0, -1);
    return $t;
}

////----- FoxCSVEdit 2024-04-06 -----//////
//FoxEdit Globals
$CSVEditForm = $EditSource = $EditTarget = $EditBase = $EditSection = $EditItem = ''; //init; will be set by form

// Pierre Racine Preview
// Make the name of the form available to users so it can be added to the (:fox :) markup to be used when editing a preview
$FmtPV['$CSVEditForm'] = '$GLOBALS["CSVEditForm"]';
$FmtPV['$EditSource'] = '$GLOBALS["EditSource"]';
$FmtPV['$EditTarget'] = '$GLOBALS["EditTarget"]';
$FmtPV['$EditSection'] ='$GLOBALS["EditSection"]';
$FmtPV['$EditItem'] ='$GLOBALS["EditItem"]';
$FmtPV['$EditBase'] ='$GLOBALS["EditBase"]';

// make csv edit form by setting page vars and loading edit form
$HandleActions['foxcsvedit'] = 'fxc_Edit_Form';
function fxc_Edit_Form($pagename) {
    global $CSVEditForm, $EditSource, $EditTarget, $EditBase, $EditSection, $EditItem, $InputValues, $FoxCSVConfig;
    $args = RequestArgs($_POST); // fetch POST arguments
    // Pierre Racine Preview
    // When editing from preview, only $args['target'] is set, not $args['source']
    if (empty($args['source']) && empty($args['target'])) FoxAbort($pagename,"%red%'''Error:''' no source given");
    //DEBUG    show($args,'edit args');
    // initialising variables
    $section = $args['section'] ?? ''; 
    // check for file edit or page (section) edit
    // Pierre Racine Preview
    // Set the source from the (:csv-edit:) markup source parameter or from the Fox target
    $source = $args['source'] ?? $args['target'];
    unset($args['target']);
    if (preg_match('/\.(csv|txt)$/i', $source, $m)) {
        $EditSource = $target = $source;
        $csvfile = 1; // file edit flag, will be passed on at form submit to Fox processing
    } else {
        $csvfile = 0;
        // Pierre Racine Preview
        // $source is now defined above
        $source = FoxMakeFullTarget($pagename, $source);
        $parts = explode('#', $source);
        $section = isset($parts[1]) ? '#'.$parts[1] : "";
    }
    $EditSource = $source;
    $EditSection = $section;
    // Pierre Racine Preview
    // Get idx from idx or csvidx
    $EditItem = $idx = $args['idx'] ?? $args['csvidx'] ?? '';
    if (empty($idx)) FoxAbort($pagename,isset($args['preview']) ? "" : "%red%'''Error:''' no idx provided");
    $csv = (isset($args['csv'])) ? explode(",", $args['csv']) : '';
    $target = $args['target'] ?? $source;
    $EditTarget = MakePagename($pagename, $target); 
    $EditBase = $base = $args['base'] ?? $EditTarget;
    $fulltarget = (isset($section) && strstr($section,'#') && ! strstr($target,'#')) ? $target.$section : $target;
    // open target page or file, get text section, set InputValues for 'text' and 'mark' controls
    $text = fxc_Get_Text($pagename, $fulltarget);
    if (empty($text)) FoxAbort($pagename,"$[Error: cannot find csv data] $fulltarget");
    // get rows array from text
    $text = trim($text,"\r\n");
    // get csv separator/delimiter 
    list($sep, $args) = fxc_Set_Sep($text, $args);
    if ($idx=='header') $idx = 0;
    // Pierre Racine Multiline
    // If set in the config, use a different CSV parser (able to parse double quoted values containing \n properly)
    if (isset($FoxCSVConfig['csvparser']) && function_exists($FoxCSVConfig['csvparser']))
       $data = $FoxCSVConfig['csvparser']($text, $sep);
    else
       $data = fxc_Parse_CSV( $text, $sep );

    $rowcnt = count($data);
    $header = $data[0];
    $multi = (isset($args['multiline'])) ? fxc_Fields($args['multiline'], $header) : array();
    foreach ($header as $k=>$v)  { 
        if (preg_match('/^[\d]|[^\-a-zA-Z0-9_]/', $v, $m)) 
            FoxAbort($pagename, "'''Error: invalid header name(s)!''' (No  digits at beginning, no spaces, no punctuations)");
    }
    $key = ($header[0]=='IDX') ? fxc_Get_Row_Key($data, $idx, $sep, true) : $idx;

    $idxtitle = " #$idx";
    $idx0 = $idx; 
    $act = 'replace';
    if (substr($idx,0,3)=='new') {
        $act = 'addnew';
        if ($idx=='newtop') { 
            $idx = $key; 
            $key = 0;     //top insert (below header)
            $idxtitle = " to top";
        }
        if ($idx=='new') { 
            $idx = $key; 
            $key = $rowcnt;  //bottom insert (below last)
            $idxtitle = " to bottom";
        }
        if (preg_match('/new(\d+)/', $idx, $m))
            $key = $m[1]; //insert new after row $key
        if ($header[0]=='IDX') 
             $data[$key] = array($key);
        else $data[$key] = array(); 
    } 
    if (!array_key_exists($key, $data)) 
         $item = array();
    else $item = $data[$key];
    // set InputValues for the item (csv data from row) to fill form's text fields [input defaults requests=1]
    foreach ($item as $k => $value) {
        $fn = $header[$k] ?? '';
        if (!isset($InputValues[$fn]))
        $InputValues[$fn] = trim(str_replace('$','&#036;',htmlspecialchars($value,ENT_NOQUOTES)));
    }
    //note: we use hardcoded form as default, if no formpage is set via form= parameter
    if (isset($args['form'])) {
        $formpage = $args['form'];
        // Pierre Racine Preview
        // Set the CSVEditForm page variable so it can be used in the (:fox markup to indicate which form to use when editing preview
        $CSVEditForm = $formpage;
    }
    //retrieve edit form from page or page section
    if (!empty($formpage)) {
        if (substr($formpage,0,1)=='#') $formpage = $pagename.$formpage;
        $formname = MakePagename($pagename, $formpage);
        if (PageExists($formname)) {
            $eform = RetrieveAuthSection($formname, $formpage);
            if (empty($eform)) FoxAbort($pagename,"$[Error: cannot find edit template] $formpage");
        }
    } 
## edit form //we got no form page, so we use hardcoded form, to edit csv row:
    if (empty($eform)) {
        $eform = "(:fox eform foxaction=csv csvact=$act csvidx=$key target=$fulltarget redirect=$base:)";
        // set template
        $tv= fxc_Auto_Template($header, $sep);
        $eform .= "(:foxtemplate \"".rtrim($tv,$sep)."\":)"; //trim trailing sep! trailing sep stays from text row!
        $eform .= "(:input defaults request=1:)(:input hidden csum '$[Updated CSV Table]':)(:input hidden rowcnt $rowcnt:)"
                  ."(:input hidden csvsep $sep:)(:input hidden csvfile $csvfile:)".
                  (isset($args['env']) ? "(:input hidden csvenv 1:)" : '').
                  (substr($idx0,0,3)=='new' ? "\n!!!Adding csv record " : "\n!!!Editing csv record ").
                  ($idx0==0 ? "Header": $idxtitle)." on {$fulltarget}";
        // set input fields
        foreach ($header as $name) {
            $area = (in_array($name, $multi)) ? 'area cols=40 rows=4' : ' size=40';
            if (isset($InputValues[$name]) && preg_match('/\n/',$InputValues[$name]))
                $area = 'area cols=40 rows=4';
            if (is_array($csv)) {
                if (in_array($name, $csv))
                    $eform .= "\n|| $name:||(:input text$area $name :) ||";
                else $eform .= "(:input hidden $name:)";
            } else {
                if ($name=='IDX')
                    $eform .= "(:input hidden IDX $idx:)"; //IDX field is not for editing
                else
                    $eform .= "\n|| $name:||(:input text$area $name :) ||";
            }
        }
        $eform .= "\n|| ||(:input submit post '$[Save]':)". 
            (isset($args['saveasnew']) ? "&nbsp; (:input submit post2 '$[Save as New]':)(:input hidden saveasnew ".$args['saveasnew'].":)": '').
            "&nbsp; (:input submit cancel '$[Cancel]':) ||(:foxend eform:)";
    }
    // make HTML editform page
    global $FmtV, $FoxEditForm, $PageStartFmt, $FoxPageEditFmt, $PageEndFmt;
    $FmtV['$FoxEditFrm'] = MarkupToHTML($pagename, $eform);
    $FoxPageEditFmt = '$FoxEditFrm';
    $HandleEditFmt = array(&$PageStartFmt, &$FoxPageEditFmt, &$PageEndFmt);
    PrintFmt($pagename, $HandleEditFmt);
    exit;
} //}}}

Markup('foxcsveditform','directives','/\\(:csv-edit\\s*(.*?)\\s*:\\)/', "fxc_Edit_Button");
// make (:csv-edit <source> <idx=..> ...:) form button HTML
function fxc_Edit_Button ($m) {
    extract($GLOBALS['MarkupToHTML']);
    $PageUrl = PageVar($pagename, '$PageUrl');
    $args = ParseArgs($m[1]);
    $args[''] = (array)@$args[''];
    $source =  $args['source'] ?? array_shift($args['']); 
    $idx = $args['idx'] ??  array_shift($args['']);
    $label =  $args['label'] ?? array_shift($args['']);
    $csv = $args['csv'] ?? '';
    $sep = $args['sep'] ?? '';
    $env = $args['env'] ?? '';
    $form = $args['form'] ?? '';
    $multiline = $args['multiline'] ?? '';
    $saveasnew = $args['saveasnew'] ?? '';
    //title (tooltip)
    if (!empty($idx)||$idx==='0') { 
        if (substr($idx,0,3)=='new') {
            if (isset($label)) $title = $label;
            else $title = "$[Add new item]";
        }
        elseif ($idx=='header') $title = "$[Edit header]";
        // Pierre Racine Preview
        // Display a proper error message when no IDX is provided (e.g. in the preview)
        else $title = "$[Edit row]".(is_numeric($idx) ? "" : " (no IDX provided)");
    }
    else if (!empty($source)) $title = "$[Edit] ".$source;
    $title = $args['title'] ?? $title ?? '';
    $target = $args['target'] ?? '';
    $base = $args['base'] ?? $pagename; //return to base
    if (empty($label)) $label = "$[Edit]";
    $imageurl = $GLOBALS['FoxCSVConfig']['editbuttonurl']; 
    $inputs = [
        'source' => $source, 
        'idx' => $idx, 
        'sep' => $sep, 
        'env' => $env, 
        'csv' => $csv, 
        'base' => $base,
        'form' => $form, 
        'target' => $target,
        'multiline' => $multiline,
        'saveasnew' => $saveasnew,
    ];
    $out = "\n<form class='csvform' name='FoxCSV-edit' action='{$PageUrl}' method='post' >".
            "<input type='hidden' name='action' value='foxcsvedit' />";
    foreach ($inputs as $n=>$v) if (!empty($v)) {
        $out .= "<input type='hidden' name=$n value=$v />";
    }
    $out .= (!empty($imageurl) ? "<input type='image' name='post' alt='$label' class='inputbutton' src=$imageurl title='$title'/>"
    : "<input type='submit' name='post' value='$label' title='$title' class='inputbutton'/>");
    $out .= "</form>";
    return Keep(FmtPagename($out,$pagename));
} //}}}

//EOF