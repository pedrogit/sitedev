<?php

/* Copyright (C) 2023 PierreRacine.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

Usage

(:csvtemplate OPTIONS :)

OPTIONS are:

source      The name of the page containing CSV data. In order to display 
            correctly CSV data in a PmWiki page, a last column filled 
            with "\\" can be added.
            
            e.g., source=Main.StudentData
            
            

template    Format the result using a pattern of templates stored in pages 
            between [[#templname]] and [[#templnameend]] anchors similar to 
            those used by (:pagelist:). Templates are separated by a comma 
            and can be located in different pages. If a number follow the
            template name, the template is repeated according to this number. 
            Default is one. This pattern scheme allows for building more 
            complex patterns like those involved when producing table rows 
            (the first or the last column is different than the middle 
            columns). Reference to values in the CSV source data are done 
            by surrounding the column name with cruly braquets ("{" and "}")
            as in {varname}.
            
            e.g., template=main.templatepage#firstcell, main.templatepage#secondcell, 3, group2.templatepage2#fifthcell


header      Does the source contain a header line of column names?
            1 = yes
            0 = no, default, in this case the default names become 'h1', 'h2', 'h3',...
            
            e.g., "header=0" or simply "header"

sep         The file separator. Some CSV file use ',', some use ';'
            Default to ';'
            
            e.g., sep='\t' or sep=','

filter      Filter rows from the source using an expression. The expression 
            must be enclosed in double quote. Reference to CSV columns (or 
            variable) are done using "{" and "}" as in {varname}.
            
            e.g., filter="strpos({Name}, 'Smith') !== FALSE && {Title} == 'Student'"

sort        Sort the result using a CSV column (or variable). Prefix the 
            variable name with "-" to sort in descending order (default 
            is to sort in ascending order). Prefix the variable with "#" 
            to sort as a number (default is to sort as a string). You can
            do "sort=random" in which case the rows are returned in random order.
            
            e.g., sort=varname, #-varname2
            Sort by varname as a string in ascending order and then by varname2 as a number in descending order

limit       Limit the number of rows to this number.

countonly   Only return the count of rows in the CSV.

            e.g., limit=100
*/

Markup('csvtemplate','<markup','/\(:csvtemplate\\s+(\\S.*?)\\s?:\)/',"CSVTemplate");
SDV($HandleActions['csvtemplatequery'],'CSVTemplateQueryURL');

$parsedCSV = array();

function array_contains(array $search_arr, array $in_arr)
{
  foreach($search_arr as $search) {
    foreach($in_arr as $in) {
      if (stripos($search, $in) !== false)
      {
        return true;
      }
    }
  }
  return false;
}

function contains_any($search_str, array $arr)
{
  foreach($arr as $a) {
     if (stripos($search_str,$a) !== false) return true;
  }
  return false;
}

$FoxFilterFunctions['csv'] = 'FoxCSVFilter';
function FoxCSVFilter($pagename, $fields)
{
    foreach ($fields as $key => $value)
    {
        $fields[$key] = trim($fields[$key] ?? "");
        $fields[$key] = str_replace("\"", "\"\"", $fields[$key]);
    }
    return $fields;
}

## format parameters for the (:csvtemplate:) directive
function CSVTemplate($m)
{
  extract($GLOBALS['MarkupToHTML']);
  $opt = ParseArgs($m[1]);
  if (isset($_GET['sort']) && isset($_GET['csvname']) && isset($opt['name']) && $_GET['csvname'] == $opt['name'])
  {
    $opt['sort'] = trim($_GET['sort'] ?? "");
  }

  if (isset($_GET['filter']) && isset($_GET['csvname']) && isset($opt['name']) && $_GET['csvname'] == $opt['name'])
  {
    $opt['filter'] = trim($_GET['filter'] ?? "").($opt['filter'] ? " && (".$opt['filter'].")" : "");
  }

  # Parse any standalone argument (without '=')
  foreach ((array)@$opt[''] as $a)
    if (!isset($opt[$a])) $opt[$a] = TRUE;

  return CSVTemplateGenerateContent($pagename, $opt);
}

## format parameters for the (:csvtemplate:) directive from an URL Query
function CSVTemplateQueryURL($pagename)
{
  $opt = array();

  // Extract only the parameters that we know are valid one
  $opt['source'] = trim($_GET['source'] ?? "");
  $opt['template'] = str_replace(",", "#", trim($_GET['template'] ?? ""));
  $opt['header'] = trim($_GET['header'] ?? "");
  $opt['sep'] = trim($_GET['sep'] ?? "");
  $opt['filter'] = trim($_GET['filter'] ?? "");
  $opt['sort'] = trim($_GET['sort'] ?? "");
  $opt['limit'] = trim($_GET['limit'] ?? "");
  $opt['countonly'] = trim($_GET['countonly'] ?? "");

  $result = CSVTemplateGenerateContent($pagename, $opt);

  PrintFmt($pagename,$result);
  return $result;
}


function CSVTemplateGenerateContent($pagename, $opt)
{
    global $FmtPV, $parsedCSV;
    $templatesArray = array();

    # Check if source file exist
    $sourcefile = MakePageName($pagename, $opt['source']);
    if (!PageExists($sourcefile))
        return "%red%csvtemplate: Invalid source File!";

    # Create a list of templates
    $templateslist = explode(',', $opt['template']);

    # Create a new list of templates with template filename as key on a list of (repeat, count, string)
    while (count($templateslist) > 0)
    {
        # Get the first template file
        $tempfilename = array_shift($templateslist);

        list($tempfilename, $tempanchor) = explode('#', $tempfilename, 2);

        # Check if template file exist
        $templatefile = MakePageName($pagename, $tempfilename);
        if (!PageExists($templatefile))
            return "%red%csvtemplate: Invalid template File: ".$tempfilename."!";

        $templatestring = ReadPage($templatefile, READPAGE_CURRENT);
        $templatestring = $templatestring['text'];
        if ($tempanchor)
            $templatestring = TextSection($templatestring, "#".$tempanchor);

        # Fill the other values depending if the repetition is specified or not (default to 1)
        $templrepeat = $templateslist[0] ?? null;
        if (is_numeric($templrepeat) && $templrepeat > 0)
            $templatesArray[$templatefile."#".$tempanchor] = array(array_shift($templateslist), 1, $templatestring);
        else
            $templatesArray[$templatefile."#".$tempanchor] = array(1, 1, $templatestring);
    }

    # Read the source file
    $sourceCSV = ReadPage($sourcefile, READPAGE_CURRENT);
    $sourceCSV = trim($sourceCSV['text'])."\n";

    # Parse the CSV content if it was not already
    if (!array_key_exists($sourcefile, $parsedCSV))
        $parsedCSV[$sourcefile] = CSVTemplateParseCVSContent($sourceCSV,  $opt['header'] ?? null, $opt['sep'] ?? null);

    $filteredCSV = array();
    $filteredCSV = $parsedCSV[$sourcefile];

    # If a filter is present, filter the entries
    if (array_key_exists('filter', $opt))
    {
        $filteredresult = array();
        $filteredresult = $parsedCSV[$sourcefile];
        $filteredCSV = array();

        $filterstring = $opt['filter'];

        # Replace {name}s with $parsedCSV[x]['name'] in the filter
        $filterstring = "return (".preg_replace_callback('/\{([^[$]*?)\}/', function($m) {
          return "\$entry['".$m[1]."']";
        }, $filterstring).");";

        foreach ($filteredresult as $entry)
            if (eval($filterstring)) $filteredCSV[] = $entry;
    }

    $limit = count($filteredCSV);
    # Determine the number of rows we will return
    if (array_key_exists('limit', $opt) && is_numeric($opt['limit']) && $opt['limit'] > 0)
       $limit = min($opt['limit'], $limit);

    if ($limit == 0)
        return "";

    if (array_key_exists('countonly', $opt))
    {
        return $limit;
    }

    # If a sort argument is present, sort the entries
    if (array_key_exists('sort', $opt) && count($filteredCSV) > 1)
    {
        if ($opt['sort'] == 'random')
        {
            $lastIndex = count($filteredCSV) - 1;
            $draws = $limit;

            $newArr = array();
            while($draws > 0)
            {
                   $rndIndex = rand(0, $lastIndex);
                   $test = array_splice($filteredCSV, $rndIndex, 1);
                   $newArr[] = $test[0];
                   $draws--;
                   $lastIndex--;
            }

            $filteredCSV = $newArr;
        }
        else
        {
            $sortarray = explode(',', $opt['sort']);
            $sortarray = array_map('trim',$sortarray);
            $sortcommand = "CSVTemplateArrayColumnSort(";
            foreach ($sortarray as $sortoption)
            {
                $type = "SORT_LOCALE_STRING";

                $order = "SORT_ASC";

                if ($sortoption[0] == "#")
                {
                    $type = "SORT_NUMERIC";
                    $sortoption = substr ($sortoption, 1);
                }
                if ($sortoption[0] == "-")
                {
                    $order = "SORT_DESC";
                    $sortoption = substr ($sortoption, 1);
                }
                $sortcommand = $sortcommand."'".$sortoption."', ".$order.", ".$type.",";
            }
            $sortcommand = "return ".$sortcommand." \$filteredCSV);";

            $filteredCSV = eval($sortcommand);
        }
    }

    # Create a string using one template per row
    $count = 1;
    foreach ($filteredCSV as $rowvalues)
    {
        # Grab the string that will be used as index
        $templ = key($templatesArray);

        # Append the filled template to the the resulting string
        $templ1 = str_replace("{CSVTemplateCount}", $count, $templatesArray[$templ][2]);

        $result .= CSVTemplateGetFilledTemplate($templ1, array_merge($rowvalues, $opt));

        # Increment the template counter
        $templatesArray[$templ][1]++;

        # If the counter exceed the repetition factor, reset the counter and switch to the next template
        if ($templatesArray[$templ][1] > $templatesArray[$templ][0])
        {
            $templatesArray[$templ][1] = 1;
            if (!next($templatesArray)) reset($templatesArray);
        }
        $count++;
        if ($count > $limit)
            break;
    }
    return $result;
}

// Fill the  template with the field values
function CSVTemplateGetFilledTemplate($template, $fields)
{
    $string = $template;
    $string = preg_replace("/\n\n\n*/", "\n<:vspace>\n", $string);
    
    $string = preg_replace_callback('/\{([^[\(\){}$]*?)\}/', function($m) use($fields) {
      if (array_key_exists($m[1], $fields))
      {
        // ensure no more then 2 consecutive new line chars
        $ret = preg_replace("/(\n|\r\n|\r){2,}/", "$1$1", $fields[$m[1]]);
        $ret = stripmagic(str_replace(array(":)", "(:", "\n", "\r", "\r\n"), array("&colon;)", "(&colon;", "<br>", "<br>", "<br>"), $ret));
        return $ret;
      }
      return "";
    }, $string);  # replace {name} fields

    return $string;
}

function CSVTemplateParseCVSContent($content, $header=TRUE, $separator=";")
{
    $headerA = array();
    $result = array();

    if ($separator == "")
      $separator=";";

    if (strlen($content) == 0)
        return $result;

    $csv = new CSVTemplateParseCSV();

    if ($header)
    {
        $tok = strtok($content,"\r\n");
        $headerA = explode($separator, $tok);
    }

    $csv->heading = $header;
    $csv->delimiter = $separator;

    $csv->parse($content);
    $result = $csv->data;
    return $result;
}

# CSVTemplateArrayColumnSort
function CSVTemplateArrayColumnSort()
{
    $n = func_num_args();

    // Get the array to sort
    $ar = func_get_arg($n-1);

    if(!is_array($ar))
        return false;

    //
    for($i = 0; $i < $n-1; $i++)
        $col[$i] = func_get_arg($i);

    foreach($ar as $key => $val)
        foreach($col as $kkey => $vval)
            if(is_string($vval))
                ${"subar$kkey"}[$key] = $val[$vval] ?? null;

    $arv = [];
    foreach($col as $key => $val)
        $arv[] = (is_string($val) && isset(${"subar$key"}) ? ${"subar$key"} : $val);
    $arv[] = &$ar;

    call_user_func_array("array_multisort", $arv);
    return $ar;
}

##############################3
class CSVTemplateParseCSV {
/*
    Class: CSVTemplateParseCSV v0.2.0 beta
    https://github.com/parsecsv/parsecsv-for-php

    Created by Jim Myhrberg (jim@zhuoqe.org).

    Fully conforms to the specifications lined out on wikipedia:
     - http://en.wikipedia.org/wiki/Comma-separated_values

    Based on the concept of this class:
     - http://minghong.blogspot.com/2006/07/csv-parser-for-php.html


    Code Examples
    ----------------
    # general usage
    $csv = new CSVTemplateParseCSV('data.csv');
    print_r($csv->data);
    ----------------
    # tab delimited, and encoding conversion
    $csv = new CSVTemplateParseCSV();
    $csv->encoding('UTF-16', 'UTF-8');
    $csv->delimiter = "\t";
    $csv->parse('data.tsv');
    print_r($csv->data);
    ----------------
    # auto-detect delimiter character
    $csv = new CSVTemplateParseCSV();
    $csv->auto('data.csv');
    print_r($csv->data);
    ----------------
    # modify data in a csv file
    $csv = new CSVTemplateParseCSV();
    $csv->sort_by = 'id';
    $csv->parse('data.csv');
    # "4" is the value of the "id" column of the CSV row
    $csv->data[4] = array('firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@doe.com');
    $csv->save();
    ----------------
    # add row/entry to end of CSV file
    #  - only recommended when you know the extact sctructure of the file
    $csv = new CSVTemplateParseCSV();
    $csv->save('data.csv', array('1986', 'Home', 'Nowhere', ''), true);
    ----------------
    # convert 2D array to csv data and send headers
    # to browser to treat output as a file and download it
    $csv = new CSVTemplateParseCSV();
    $csv->output (true, 'movies.csv', $array);
    ----------------
    ----------
    This program is free software; you can redistributeit and/or modify it
    under the terms of the GNU General Public License as published by the Free
    Software Foundation; either version 2 of the License, or (at your option)
    any later version. http://www.gnu.org/licenses/gpl.txt

    This program is distributed in the hope that it will be useful, but WITHOUT
    ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
    FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
    more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc., 59
    Temple Place, Suite 330, Boston, MA 02111-1307 USA
    ----------

*/


    /**
     * Configuration
     */

    # use first line/entry as field names
    var $heading = true;

    # override field names
    var $fields = array();

    # sort entries by this field
    var $sort_by = null;
    var $sort_reverse = false;

    # delimiter (comma) and enclosure (double quote)
    var $delimiter = ',';
    var $enclosure = '"';

    # number of rows to analyze when attempting to auto-detect delimiter
    var $auto_depth = 15;

    # characters to ignore when attempting to auto-detect delimiter
    var $auto_non_chars = "a-zA-Z0-9\n\r";

    # prefered delimiter characters, only used when all filtering method
    # returns multiple possible delimiters (happens very rarely)
    var $auto_prefered = ",;\t.:|";

    # character encoding options
    var $convert_encoding = false;
    var $input_encoding = 'ISO-8859-1';
    var $output_encoding = 'ISO-8859-1';

    # used by unparse(), save(), and output() functions
    var $linefeed = "\n";

    # only used by output() function
    var $output_delimiter = ',';
    var $output_filename = 'data.csv';


    /**
     * Internal variables
     */

    # current file
    var $file;

    # loaded file contents
    var $file_data;

    # array of field values in data parsed
    var $titles = array();

    # two dimentional array of CSV data
    var $data = array();


    /**
     * Constructor
     * @param   input   CSV file or string
     * @return  nothing
     */
    function __construct($input = null) {
        if ( !empty($input) ) $this->parse($input);
    }


    // ==============================================
    // ----- [ Main Functions ] ---------------------
    // ==============================================

    /**
     * Parse CSV file or string
     * @param   input   CSV file or string
     * @return  nothing
     */
    function parse ($input = null)
    {
        if ( !empty($input) )
        {
                $this->file_data = &$input;
                $this->data = $this->parse_string();


            if ( $this->data === false )
                return false;
        }
        return true;
    }

    /**
     * Save changes, or new file and/or data
     * @param   file     file to save to
     * @param   data     2D array with data
     * @param   append   append current data to end of target CSV if exists
     * @param   fields   field names
     * @return  true or false
     */
    function save ($file = null, $data = array(), $append = false, $fields = array()) {
        if ( empty($file) ) $file = &$this->file;
        $mode = ( $append ) ? 'at' : 'wt' ;
        $is_php = ( preg_match('/\.php$/i', $file) ) ? true : false ;
        return $this->wfile($file, $this->unparse($data, $fields, $append, $is_php), $mode);
    }

    /**
     * Generate CSV based string for output
     * @param   output      if true, prints headers and strings to browser
     * @param   filename    filename sent to browser in headers if output is true
     * @param   data        2D array with data
     * @param   fields      field names
     * @param   delimiter   delimiter used to seperate data
     * @return  CSV data using delimiter of choice, or default
     */
    function output ($output = true, $filename = null, $data = array(), $fields = array(), $delimiter = null) {
        if ( empty($filename) ) $filename = $this->output_filename;
        if ( $delimiter === null ) $delimiter = $this->output_delimiter;
        $data = $this->unparse($data, $fields, null, null, $delimiter);
        if ( $output ) {
            header('Content-type: application/csv');
            header('Content-Disposition: inline; filename="'.$filename.'"');
            echo $data;
        }
        return $data;
    }

    /**
     * Convert character encoding
     * @param   input    input character encoding, uses default if left blank
     * @param   output   output character encoding, uses default if left blank
     * @return  nothing
     */
    function encoding ($input = null, $output = null) {
        $this->convert_encoding = true;
        if ( $input !== null ) $this->input_encoding = $input;
        if ( $output !== null ) $this->output_encoding = $output;
    }

    /**
     * Auto-Detect Delimiter: Find delimiter by analyzing a specific number of
     * rows to determin most probable delimiter character
     * @param   file           local CSV file
     * @param   parse          true/false parse file directly
     * @param   search_depth   number of rows to analyze
     * @param   prefered       prefered delimiter characters
     * @param   enclosure      enclosure character, default is double quote (").
     * @return  delimiter character
     */
    function auto ($file = null, $parse = true, $search_depth = null, $prefered = null, $enclosure = null) {

        if ( $file === null ) $file = $this->file;
        if ( empty($search_depth) ) $search_depth = $this->auto_depth;
        if ( $enclosure === null ) $enclosure = $this->enclosure;

        if ( $prefered === null ) $prefered = $this->auto_prefered;

        if ( empty($data) ) {
            if ( $this->check_data() ) {
                $data = &$this->file_data;
            } else return false;
        }

        $chars = array();
        $strlen = strlen($data);
        $enclosed = false;
        $n = 1;
        $to_end = true;

        // walk specific depth finding posssible delimiter characters
        for ( $i=0; $i < $strlen; $i++ ) {
            $ch = $data[$i];
            $nch = ( isset($data[$i+1]) ) ? $data[$i+1] : false ;
            $pch = ( isset($data[$i-1]) ) ? $data[$i-1] : false ;

            // open and closing quotes
            if ( $ch == $enclosure && (!$enclosed || $nch != $enclosure) ) {
                $enclosed = ( $enclosed ) ? false : true ;

            // inline quotes
            } elseif ( $ch == $enclosure && $enclosed ) {
                $i++;

            // end of row
            } elseif ( ($ch == "\n" && $pch != "\r" || $ch == "\r") && !$enclosed ) {
                if ( $n >= $search_depth ) {
                    $strlen = 0;
                    $to_end = false;
                } else {
                    $n++;
                }

            // count character
            } elseif (!$enclosed) {
                if ( !preg_match('/['.preg_quote($this->auto_non_chars, '/').']/i', $ch) ) {
                    if ( !isset($chars[$ch][$n]) ) {
                        $chars[$ch][$n] = 1;
                    } else {
                        $chars[$ch][$n]++;
                    }
                }
            }
        }

        // filtering
        $depth = ( $to_end ) ? $n-1 : $n ;
        $filtered = array();
        foreach( $chars as $char => $value ) {
            if ( $match = $this->check_count($char, $value, $depth, $prefered) ) {
                $filtered[$match] = $char;
            }
        }

        // capture most probable delimiter
        ksort($filtered);
        $delimiter = reset($filtered);
        $this->delimiter = $delimiter;

        // parse data
        if ( $parse ) $this->data = $this->parse_string();

        return $delimiter;

    }


    // ==============================================
    // ----- [ Core Functions ] ---------------------
    // ==============================================

    /**
     * Load local file or string
     * @param   input   local CSV file
     * @return  true or false
     */
    function load_data ($input = null) {
        $data = null;
        $file = null;
        if ( $input === null ) {
            $file = $this->file;
        } elseif ( file_exists($input) ) {
            $file = $input;
        } else {
            $data = $input;
        }
        if ( !empty($data) || $data = $this->rfile($file) ) {
            if ( $this->file != $file ) $this->file = $file;
            if ( preg_match('/\.php$/i', $file) && preg_match('/<\?.*?\?>(.*)/ims', $data, $strip) ) {
                $data = ltrim($strip[1] ?? "");
            }
            if ( $this->convert_encoding ) $data = iconv($this->input_encoding, $this->output_encoding, $data);
            if ( $data[strlen($data)-1] != "\n" ) $data .= "\n";
            $this->file_data = &$data;
            return true;
        }
        return false;
    }

    /**
     * Read file to string and call parse_string()
     * @param   file   local CSV file
     * @return  2D array with CSV data, or false on failure
     */
    function parse_file ($file = null) {
        if ( $file === null ) $file = $this->file;
        if ( empty($this->file_data) ) $this->load_data($file);
        return ( !empty($this->file_data) ) ? $this->parse_string() : false ;
    }

    /**
     * Parse CSV strings to arrays
     * @param   data   CSV string
     * @return  2D array with CSV data, or false on failure
     */
    function parse_string ($data = null) {
        if ( empty($data) ) {
            if ( $this->check_data() ) {
                $data = &$this->file_data;
            } else return false;
        }

        $rows = array();
        $row = array();
        $row_count = 0;
        $current = '';
        $head = ( !empty($this->fields) ) ? $this->fields : array() ;
        $col = 0;
        $enclosed = false;
        $strlen = strlen($data);

        // walk through each character
        for ( $i=0; $i < $strlen; $i++ ) {
            $ch = $data[$i];
            $nch = ( isset($data[$i+1]) ) ? $data[$i+1] : false ;
            $pch = ( isset($data[$i-1]) ) ? $data[$i-1] : false ;

            // open and closing quotes
            if ( $ch == $this->enclosure && (!$enclosed || $nch != $this->enclosure) ) {
                $enclosed = ( $enclosed ) ? false : true ;

            // inline quotes
            } elseif ( $ch == $this->enclosure && $enclosed ) {
                $current .= $ch;
                $i++;

            // end of field/row
            } elseif ( ($ch == $this->delimiter || ($ch == "\n" && $pch != "\r") || $ch == "\r") && !$enclosed ) {
                $current = trim($current ?? "");
                $key = ( !empty($head[$col]) ) ? $head[$col] : $col;
                $row[$key] = $current;
                $current = '';
                $col++;

                // end of row
                if ( $ch == "\n" || $ch == "\r" ) {
                    if ( $this->heading && empty($head) ) {
                        $head = $row;
                    } elseif ( empty($this->fields) || (!empty($this->fields) && (($this->heading && $row_count > 0) || !$this->heading)) ) {
                        if ( !empty($this->sort_by) && !empty($row[$this->sort_by]) ) {
                            if ( isset($rows[$row[$this->sort_by]]) ) {
                                $rows[$row[$this->sort_by].'_0'] = &$rows[$row[$this->sort_by]];
                                unset($rows[$row[$this->sort_by]]);
                                for ( $sn=1; isset($rows[$row[$this->sort_by].'_'.$sn]); $sn++ ) {}
                                $rows[$row[$this->sort_by].'_'.$sn] = $row;
                            } else $rows[$row[$this->sort_by]] = $row;
                        } else $rows[] = $row;
                    }
                    $row = array();
                    $col = 0;
                    $row_count++;
                }

            // append character to current field
            } else {
                $current .= $ch;
            }
        }
        $this->titles = $head;
        if ( !empty($this->sort_by) ) {
            ( $this->sort_reverse ) ? krsort($rows) : ksort($rows) ;
        }
        return $rows;
    }

    /**
     * Create CSV data from array
     * @param   data        2D array with data
     * @param   fields      field names
     * @param   append      if true, field names will not be output
     * @param   is_php      if a php die() call should be put on the first
     *                      line of the file, this is later ignored when read.
     * @param   delimiter   field delimiter to use
     * @return  CSV data (text string)
     */
    function unparse ( $data = array(), $fields = array(), $append = false , $is_php = false, $delimiter = null) {
        if ( !is_array($data) || empty($data) ) $data = &$this->data;
        if ( !is_array($fields) || empty($fields) ) $fields = &$this->titles;
        if ( $delimiter === null ) $delimiter = $this->delimiter;

        $string = ( $is_php ) ? "<?php header('Status: 403'); die(' '); ?>".$this->linefeed : '' ;
        $entry = array();

        // create heading
        if ( $this->heading && !$append ) {
            foreach( $fields as $value ) {
                $entry[] = $this->enclose_value($value);
            }
            $string .= implode($delimiter, $entry).$this->linefeed;
            $entry = array();
        }

        // create data
        foreach( $data as $row ) {
            foreach( $row as $value ) {
                $entry[] = $this->enclose_value($value);
            }
            $string .= implode($delimiter, $entry).$this->linefeed;
            $entry = array();
        }

        return $string;
    }


    // ==============================================
    // ----- [ Internal Functions ] -----------------
    // ==============================================

    /**
     * Enclose values if needed
     *  - only used by unparse()
     * @param   value   string to process
     * @return  Processed value
     */
    function enclose_value ($value = null) {
        $delimiter = preg_quote($this->delimiter, '/');
        $enclosure = preg_quote($this->enclosure, '/');
        if ( preg_match("/".$delimiter."|".$enclosure."|\n|\r/i", $value) ) {
            $value = str_replace($this->enclosure, $this->enclosure.$this->enclosure, $value);
            $value = $this->enclosure.$value.$this->enclosure;
        }
        return $value;
    }

    /**
     * Check file data
     * @param   file   local filename
     * @return  true or false
     */
    function check_data ($file = null) {
        if ( empty($this->file_data) ) {
            if ( $file === null ) $file = $this->file;
            return $this->load_data($file);
        }
        return true;
    }


    /**
     * Check if passed info might be delimiter
     *  - only used by find_delimiter()
     * @return  special string used for delimiter selection, or false
     */
    function check_count ($char, $array, $depth, $prefered) {
        if ( $depth == count($array) ) {
            $first = null;
            $equal = null;
            $almost = false;
            foreach( $array as $value ) {
                if ( $first == null ) {
                    $first = $value;
                } elseif ( $value == $first && $equal !== false) {
                    $equal = true;
                } elseif ( $value == $first+1 && $equal !== false ) {
                    $equal = true;
                    $almost = true;
                } else {
                    $equal = false;
                }
            }
            if ( $equal ) {
                $match = ( $almost ) ? 2 : 1 ;
                $pref = strpos($prefered, $char);
                $pref = ( $pref !== false ) ? str_pad($pref, 3, '0', STR_PAD_LEFT) : '999' ;
                return $pref.$match.'.'.(99999 - str_pad($first, 5, '0', STR_PAD_LEFT));
            } else return false;
        }
    }

    /**
     * Read local file
     * @param   file   local filename
     * @return  Data from file, or false on failure
     */
    function rfile ($file = null){
        if ( is_readable($file) ) {
            if ( !($fh = fopen($file, 'r')) ) return false;
            $data = fread($fh, filesize($file));
            fclose($fh);
            return $data;
        }
        return false;
    }

    /**
     * Write to local file
     * @param   file     local filename
     * @param   string   data to write to file
     * @param   mode     fopen() mode
     * @param   lock     flock() mode
     * @return  true or false
     */
    function wfile($file, $string = '', $mode = 'wb', $lock = 2){
        if ( $fp = fopen($file, $mode) ) {
            flock($fp, $lock);
            $re = fwrite($fp, $string);
            $re2 = fclose($fp);
            if ( $re != false && $re2 != false ) return true;
        }
        return false;
    }

}

?>