<?php
/* Copyright (C) 2004 Alexandre Courbot. <alexandrecourbot@linuxgames.com>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

See the COPYING file for more details. */

/*
 * List of author name to replace with a link to their page
*/

$authorReplaceStr = [];
# Check if source file exist
$urlpagename = MakePageName($pagename, "Membres.PublicationsAuteursURLs");
if (PageExists($urlpagename))
{
  # Read the source file
  $sourceCSV = ReadPage($urlpagename, READPAGE_CURRENT);
  $sourceCSV = trim($sourceCSV['text'] ?? "")."\n";

  # Parse the CSV content
  $urls = CSVTemplateParseCVSContent($sourceCSV);

  foreach ($urls as $i) 
  {
    $regex = "/" . str_replace(".", "\\.", $i["NomAuteur"]) . "(?=[,\\s]|\\Z)/";
    $memberpagename = "[[Membres/" . $i["PageMembre"] . "|" . $i["NomAuteur"] . "]]";
    $authorReplaceStr[$regex] = $memberpagename;
  }
}

$PublicationUploadGroup = "Publications";

//This is a special code that we can put in the Note field to recognize undergraduate theses
$BibtexBaccThesisCode = "CEFMBAC";
$BibtexMasterEssayCode = "CEFESSMAI";

XLSDV('en', [
  'links' => 'Links',
  'directlink' => 'Direct Link',
  'searchgoogleisbn' => 'Search Google by ISBN',
  'searchgoogletitle' => 'Search Google by title',
  'searchgooglebooktitle' => 'Search Google by Book Title',
  'doilink' => 'Indirect DOI Link',
  'abstract' => 'Abstract',
  'reference' => 'Reference',
  'bibtexformat' => 'BibTeX Format',
  'endnoteformat' => 'EndNote Format',
  'csvformat' => 'BibTeX-CSV Format',
  'youcan' => 'You can copy the BibTeX entry of this reference below, or',
  'import' => 'import',
  'jabref' => 'it directly in a software like %newwin%[[http://jabref.sourceforge.net | JabRef]].',
  'youcanalso' => 'You can',
  'inendnote' => 'this reference in EndNote.',
  'incsv' => 'this reference in BibTeX-CSV format.',
  'bacc' => 'Bachelor\'s thesis',
  'master' => 'Master\'s thesis',
  'essay' => 'Master\'s essay',
  'doctorat' => 'PhD thesis',
  'aucun' => 'None'
]);

XLSDV('fr', [
  'links' => 'Liens',
  'directlink' => 'Lien direct',
  'searchgoogleisbn' => 'Chercher dans Google par ISBN',
  'searchgoogletitle' => 'Chercher dans Google par titre',
  'searchgooglebooktitle' => 'Chercher dans Google par titre du livre',
  'doilink' => 'Lien DOI indirect',
  'abstract' => 'R&eacute;sum&eacute;',
  'reference' => 'R&eacute;f&eacute;rence',
  'bibtexformat' => 'Format BibTeX',
  'endnoteformat' => 'Format EndNote',
  'csvformat' => 'Format BibTeX-CSV',
  'youcan' => 'Vous pouvez copier l\'entr&eacute;e BibTeX de cette r&eacute;f&eacute;rence ci-bas, ou l\'',
  'import' => 'importer',
  'jabref' => ' directement dans un logiciel tel que %newwin%[[http://jabref.sourceforge.net | JabRef]].',
  'youcanalso' => 'Vous pouvez',
  'inendnote' => 'cette r&eacute;f&eacute;rence dans EndNote.',
  'incsv' => 'cette r&eacute;f&eacute;rence en format BibTeX-CSV.',
  'bacc' => 'M&eacute;moire de baccalaur&eacute;at',
  'master' => 'M&eacute;moire de ma&#238;trise',
  'essay' => 'Essai de ma&#238;trise',
  'doctorat' => 'Th&egrave;se de doctorat',
  'aucun' => 'Aucun'
]);

$BibtexPdfLink = "PDF";
$BibtexBibLink = "BibTeX";
$BibtexScopusLink = "Scopus";
$BibtexEndNoteLink = "EndNote";
$BibtexCSVLink = "BibTeX-CSV";
$BibtexGoogleScholarLink = "Google Scholar";

$BibtexGenerateDefaultUrlField = false;

$BibtexLang = [];

Markup("bibtexcite", "inline", "/\\{\\[(.*?),(.*?)\\]\\}/", "BibCite");


Markup("bibtexquery", "fulltext", "/\\bbibtexquery:\\[(.*?)\\]\\[(.*?)\\]\\[(.*?)\\]\\[(.*?)\\](?:\\[(.*?)\\])?/", "BibQuery");

Markup("bibtexsummary", "fulltext", "/\\bbibtexsummary:\\[(.*?),(.*?)\\]/", "BibSummary");

Markup("bibtexcomplete", "fulltext", "/\\bbibtexcomplete:\\[(.*?),(.*?)\\]/", "CompleteBibEntry");

Markup("bibcount", "fulltext", "/\\bbibcount:\\[(.*?)\\]/", "BibCount");

SDV($HandleActions['bibentry'], 'HandleBibEntry');
SDV($HandleActions['bibtexquery'], 'BibQueryURL');

$BibEntries = [];

function trim_value(&$value)
{
  $value = trim($value ?? "");
}

class BibtexEntry
{
  var $values = [];
  var $bibfile;
  var $entryname;
  var $entrytype;

  function __construct($bibfile, $entryname)
  {
    $this->bibfile = $bibfile;
    $this->entryname = $entryname;
  }

  function evalCond($cond)
  {
  //error_log("evalCond".$cond."\n", 3, "c:/temp/my-errors.log");
    $toeval = "return (" . $cond . ");";
    $toeval = str_replace("&gt;", ">", $toeval);
    return eval($toeval);
  }

  function evalGet($get)
  {
    $get = str_replace("\\\"", "\"", $get);
    $get = str_replace("&gt;", ">", $get);
    eval('$res = ' . $get . ';');
    return $res;
  }

  function getAuthorsArray($aut_limit = null)
  {
    $aut = $this->get('AUTHOR');
    //error_log("AAAA aut =".$aut." \n", 3, "c:/temp/my-errors.log");
    if ($aut == false)
    {
      return array();
    }
    $sep = " and ";
    if (strpos($aut, " AND "))
    {
      $sep = " AND ";
    }
    $ret = explode($sep, $aut);
    if (!is_null($aut_limit) && count($ret) > $aut_limit)
    {
      $ret = array_slice($ret, 0, $aut_limit);
      array_push($ret, "et al.");
    }
    array_walk($ret, 'trim_value');
    return $ret;
  }

  function getEditorsArray()
  {
    $edi = $this->get('EDITOR');
    if ($edi == false)
    {
      return array();
    }
    $sep = " and ";
    if (strpos($edi, " AND "))
    {
      $sep = " AND ";
    }
    $ret = explode($sep, $edi);
    array_walk($ret, 'trim_value');
    return $ret;
  }

  function getAuthors($ignorelinks = array(), $aut_limit = null)
  {
    global $authorReplaceStr;
    $aut = $this->getAuthorsArray($aut_limit);
    if (count($aut) == 0)
    {
      return false;
    }
    $ret = "";

    for ($i = 0;$i < count($aut);$i++)
    {
      if ($aut[$i] == "et al.")
      {
        $ret = $ret . "''" . $aut[$i] . "''";
      }
      else
      {
        $ret = $ret . $aut[$i];
      }
      if ($i < count($aut) - 1)
      {
        if ($aut[$i + 1] != "et al.")
        {
          $ret = $ret . ",";
        }
        $ret = $ret . " ";
      }
    }

    if (count($authorReplaceStr) > 0)
    {
      $keys = array_keys($authorReplaceStr);
      $keys = array_diff($keys, $ignorelinks);
      $ret = preg_replace($keys, array_diff_key($authorReplaceStr, array_fill_keys($ignorelinks, "")), $ret);
    }

    return $ret;
  }

  function getEditors($ignorelinks = array())
  {
    global $authorReplaceStr;
    $aut = $this->getEditorsArray();
    if (count($aut) == 0)
    {
      return false;
    }
    $ret = "";

    for ($i = 0;$i < count($aut);$i++)
    {
      $ret = $ret . $aut[$i];
      if ($i == count($aut) - 2)
      {
        $ret = $ret . " and ";
      }
      else if ($i < count($aut) - 2)
      {
        $ret = $ret . ", ";
      }
    }

//error_log("AAAA ret=". $ret . "\n", 3, "c:/temp/my-errors.log");
    if (count($authorReplaceStr) > 0)
    {
      $keys = array_keys($authorReplaceStr);
      $keys = array_diff($keys, $ignorelinks);
      $ret = preg_replace($keys, array_diff_key($authorReplaceStr, array_fill_keys($ignorelinks, "")), $ret);
    }

    return $ret;
  }

  function getName()
  {
    return $this->entryname;
  }

  function getTitle()
  {
    return $this->getFormat('TITLE');
  }

  function getAbstract()
  {
    // Unescape BibTeX braces
    return preg_replace("/\\\({|})/", "$1", $this->get('ABSTRACT'));
  }

  function getComment()
  {
    return $this->get('COMMENT');
  }

  function getPages()
  {
    $pages = $this->get('PAGES');
    if ($pages)
    {
      $found = strpos($pages, "--");
      if ($found)
      {
        return str_replace("--", "-", $pages);
      }
      else
      {
        return $pages;
      }
    }
    return "";
  }

  function getPagesWithLabel()
  {
    $pages = $this->getPages();
    if ($pages)
    {
      if (is_numeric($pages[0]) && strpos($pages, "-"))
      {
        return "pages " . $pages;
      }
      elseif (is_numeric($pages))
      {
        return "page " . $pages;
      }
    }
    return $pages;
  }

  function get($field)
  {
    $val = $this->values[$field] ?? "";
    if (strtolower($field) == 'author' || strtolower($field) == 'journal')
    {
      $val = preg_replace("/\r/", " ", $val);
      $val = preg_replace("/\t/", " ", $val);
      $val = preg_replace("/[ ]+/", " ", $val);
    }
    if ($val == false && array_key_exists(strtolower($field), $this->values))
    {
      $val = $this->values[strtolower($field)];
    }
    return trim($val ?? "");
  }

  function getFormat($field)
  {
    $ret = $this->get($field);
    if ($ret)
    {
      $ret = str_replace("{", "", $ret);
      $ret = str_replace("}", "", $ret);
    }
    return $ret;
  }

  function getCompleteEntryUrl()
  {
    global $DefaultTitle, $FarmD, $BibtexCompleteEntriesUrl;
    global $pagename;

    $Bibfile = $this->bibfile;
    $Entryname = $this->entryname;

    if ($Entryname != " ")
    {
      if (!$BibtexCompleteEntriesUrl) $BibtexCompleteEntriesUrl = FmtPageName('$PageUrl', $pagename) . '?action=bibentry&bibfile=$Bibfile&bibref=$Entryname';

      $RetUrl = preg_replace('/\$Bibfile/', "$Bibfile", $BibtexCompleteEntriesUrl);
      $RetUrl = preg_replace('/\$Entryname/', "$Entryname", $RetUrl);
    }
    return $RetUrl;
  }

  function getPreString($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    // *****************************
    // Add LANG, AUTHOR, YEAR, TITLE
    // The golden rule is to always insert a ponctuation BEFORE a field not AFTER
    // because you're never sure there is going to be something after the field inserted.
    // *****************************
    global $pagename, $BibtexLang;
    $ret = "";

    $lang = $this->get("LANG");
    if ($lang && $BibtexLang[$lang])
    {
      $ret = $ret . $BibtexLang[$lang];
    }

    $author = $this->getAuthors($ignorelinks, $aut_limit);
    if ($author)
    {
      $ret = $ret . "'''" . $author . "'''";
    }

    $year = $this->get("YEAR");
    if ($year)
    {
      $ret = $ret . " (";
      $ret = $ret . $year . ") ";
    }

    if ($this->getTitle() != "")
    {
      $lastcharpos = 1;
      if ($dourl && $this->entryname != " ")
      {
        $ret = $ret . "''%newwin%[[" . $this->getCompleteEntryUrl() . " | " . $this->getTitle() . "]]";
        $lastcharpos = 3;
      }
      else
      {
        $ret = $ret . "''" . $this->getTitle();
      }
      if (strlen($ret) > 2 && $ret[strlen($ret) - $lastcharpos] != '?' && $ret[strlen($ret) - $lastcharpos] != '!' && $ret[strlen($ret) - $lastcharpos] != '.')
      {
        $ret = $ret . ".";
      }

      $ret = $ret . "''";
    }

    return $ret;
  }

  function getPostString($dourl = true)
  {
    // *****************************************
    // Add a point, NOTE, URL, PDF and BibTeX
    // The golden rule is to always insert a ponctuation BEFORE a field not AFTER
    // because you're never sure there is going to be something after the field inserted.
    // *****************************************
    /*
        global $ScriptUrl, $BibtexUrlLink, $BibtexPdfLink, $BibtexBibLink, $BibtexScopusLink, $BibtexCSVLink, $BibtexEndNoteLink, $BibtexGoogleScholarLink, $pagename, $PublicationUploadGroup;
    
        $ret = ".";
    
        if ($dourl && $this->entryname != " ")
        {
            $ret = $ret . " [-(";
            $url = $this->get("URL");
            // Try and get url from bib2html_dl_pdf
            if (!$url)
            {
                $url = $this->get("bib2html_dl_pdf");
            }
            if ($url)
            {
                $host = 'http://'.$_SERVER['HTTP_HOST'];
                if (strpos($url, 'www.scopus.com') !== FALSE)
                    $ret = $ret . "%newwin%[[" . $url . " | $BibtexScopusLink]] | ";
                elseif (strpos($url, $host) !== FALSE)
                    $ret = $ret . "%newwin%[[Attach:". $PublicationUploadGroup."/".substr($url , strpos($url, "uploads/") + 8) . " | $BibtexPdfLink]] | ";
                elseif (strpos($url, '.pdf') !== FALSE)
                    $ret = $ret . "%newwin%[[" . $url . " | $BibtexPdfLink]] | ";
                else
                    $ret = $ret . "%newwin%[[" . $url . " | $BibtexUrlLink]] | ";
            }
    
            $pdf = $this->get("PDF");
            if ($pdf)
            {
                global $BibtexPdfUrl, $UploadUrlFmt;
                if (!$BibtexPdfUrl) $BibtexPdfUrl = FmtPageName('$UploadUrlFmt$UploadPrefixFmt', $pagename);
                    $ret = $ret . " [[$BibtexPdfUrl" . $pdf . " | $BibtexPdfLink]]";
            }
    
            if  ($this->get("ABSTRACT") != "")
                $ret = $ret . "[[" . $this->getCompleteEntryUrl() . "| $[abstract]]] | ";
            $ret = $ret . "[[" . $this->getCompleteEntryUrl() . "&format=endnote| $BibtexEndNoteLink]]";
            $ret = $ret . " | [[" . $this->getCompleteEntryUrl() . "&format=csv| $BibtexCSVLink]]";
            $ret = $ret . " | [[" . $this->getCompleteEntryUrl() . "&format=bibtex| $BibtexBibLink]]";
            $title = urlencode($this->getTitle());
            if ($title)
            {
                $ret = $ret . " | [[https://scholar.google.ca/scholar?q=" . $title . "| $BibtexGoogleScholarLink]]";
            }
            $ret = $ret . ")-]";
        }

    return $ret;
    */
  }

  function cite()
  {
    $ret = "([[" . $this->getCompleteEntryUrl() . " | " . $this->entryname . "]])";
    return $ret;
  }

  function getBibEntryText()
  {
    global $BibtexSilentFields, $BibtexGenerateDefaultUrlField;

    $ret = "@" . $this->entrytype . " {" . $this->entryname . ",\n";

    foreach ($this->values as $key => $value)
    {
      if ($BibtexSilentFields && in_array($key, $BibtexSilentFields))
      {
        continue;
      }
      $ret = $ret . "    " . $key . " = {" . $value . "},\n";
    }

    if ($BibtexGenerateDefaultUrlField && ($this->get("URL") == false))
    {
      $ret = $ret . "  URL = {" . $this->getCompleteEntryUrl() . "},\\\\\n";
    }
    $ret = $ret . "}\n";

    return $ret;
  }

  function getBibEntryCSV($exportFieldList)
  {
    $ret = '"' . $this->entrytype . '";"' . $this->entryname . '";';

    foreach ($exportFieldList as $field)
    {
      if ($field == 'ABSTRACT')
      {
        $value = $this->getAbstract();
      }
      else
      {
        $value = $this->get($field);
      }
      if ($value !== "")
      {
        $value = str_replace(array("\r\n", "\r", "\n", "\\r", "\\n", "\\r\\n"), '', $value);
        $value = str_replace('"', '""', $value);
        $value = str_replace("\t", " ", $value);
        $ret = $ret . '"' . $value . '"';
      }
      $ret = $ret . ';';
    }
    return $ret;
  }

  function getBibEntryFRQNT()
  {
    $typemap = [
      'ARTICLE' => 'RAC',
      'BOOK' => 'LIV',
      'BOOKLET' => 'AUT',
      'CONFERENCE' => 'COC',
      'INBOOK' => 'COC',
      'INCOLLECTION' => 'COC',
      'INPROCEEDINGS' => 'CAC',
      'MANUAL' => 'RRA',
      'MASTERSTHESIS' => 'AUT',
      'MISC' => 'AUT',
      'OTHER' => 'AUT',
      'PHDTHESIS' => 'AUT',
      'PROCEEDINGS' => 'COC',
      'TECHREPORT' => 'RRA',
      'UNPUBLISHED' => 'AUT'
    ];
    $type = $typemap[$this->entrytype];

    $ret = '"' . $type . '";"";';

    $exportFieldList = array('TITLE');

    // Determine the right BibTeX field from which to get the NOM_REVUE_PUBLICATION_DETAIL
    if ($type === 'COC' || $type === 'CAC')
    {
      array_push($exportFieldList, 'BOOKTITLE');
    }
    else
    {
      array_push($exportFieldList, 'JOURNAL');
    }

    array_push($exportFieldList, 'EDITOR', 'ADDRESS', 'PUBLISHER', 'VOLUME', 'NUMBER', 'PAGE1', 'PAGE2', 'PAGE2', 'STATUT', 'YEAR');

    foreach ($exportFieldList as $field)
    {
      if ($field === 'STATUT')
      {
        $value = "PUB";
        if (preg_match("/press/i", $this->get('YEAR')))
        {
          $value = 'ACC';
        }
        else if (preg_match("/soum/i", $this->get('YEAR')))
        {
          $value = 'SOU';
        }
      }
      else if ($field === 'YEAR')
      {
        $value = "";
        if (preg_match("/[12][0-9][0-9][0-9]/", $this->get('YEAR'), $newvalue))
        {
          $value = $newvalue[0];
        }
      }
      else
      {
        $value = $this->get($field);
      }
      /*if ($value == "")
      {
        $value = $field;
      }*/

      if ($value !== "")
      {
        $value = str_replace(array("\r\n", "\r", "\n", "\\r", "\\n", "\\r\\n"), '', $value);
        $value = str_replace('"', '""', $value);
        $value = str_replace("\t", " ", $value);
        $ret = $ret . '"' . $value . '"';
      }
      $ret = $ret . ';';
    }
    $autcnt = 0;
    foreach ($this->getAuthorsArray() as $author)
    {
      if ((++$autcnt) > 5)
      {
        break;
      }
      if (strpos($author, ", "))
      {
        $expauthor = explode(", ", $author);
      }
      else
      {
        $expauthor = explode(" ", $author);
      }
      $lname = $expauthor[0];
      $fname = $expauthor[1];
      $ret = $ret . '"";"' . $lname . '";"' . $fname . '";';
    }
    if ($autcnt > 5)
    {
      $ret = $ret . implode(" and ", array_slice($this->getAuthorsArray(), 5)) . ';';
    }
    return $ret;
  }

  function getEndNoteEntryText()
  {
//http://localhost:8080/index.php?n=Membres.DamaseKhasa?action=bibtexquery&bibfile=DamaseKhasaPub&filter=$this->entryname==Khasa1984&format=endnote
    $res = [];
    $fieldmap = [
      'KEY' => '%3',
      'ADDRESS' => '%C',
      'ANNOTE' => '%Z',
      'AUTHOR' => '%A',
      'BOOKTITLE' => '%B',
      'CHAPTER' => '%&',
      'CROSSREF' => '%Z',
      'EDITION' => '%7',
      'EDITOR' => '%E',
      'EPRINT' => '%Z',
      'HOWPUBLISHED' => '%6',
      'INSTITUTION' => '%I',
      'JOURNAL' => '%B',
      'MONTH' => '%8',
      'NOTE' => '%Z',
      'NUMBER' => '%N',
      'ORGANIZATION' => '%I',
      'PAGES' => '%P',
      'PUBLISHER' => '%I',
      'SCHOOL' => '%I',
      'SERIES' => '%B',
      'TITLE' => '%T',
      'TYPE' => '%9',
      'URL' => '%U',
      'VOLUME' => '%V',
      'YEAR' => '%D',
      'ABSTRACT' => '%X',
      'AFFILIATION' => '%I',
      'CONTENTS' => '%Z',
      'COPYRIGHT' => '%Z',
      'ISBN' => '%@',
      'ISSN' => '%(',
      'KEYWORDS' => '%K',
      'LANGUAGE' => '%G',
      'LOCATION' => '%C',
      'LCCN' => '%4',
      'MRNUMBER' => '%5',
      'PRICE' => '%Z',
      'CITESEERURL' => '%U',
      'PDF' => '%1',
      'DOI' => '%Z',
      'EID' => '%Z',
      'LASTCHECKED' => '%\\',
      'COMMENT' => '%2',
      'REVIEW' => '%*',
      'OWNER' => '%#',
      'TIMESTAMP' => '%Z',
      'SIZE' => '%Z'
    ];
    $typemap = [
      'ARTICLE' => 'Journal Article',
      'BOOK' => 'Book',
      'BOOKLET' => 'Personal Communication',
      'CONFERENCE' => 'Book Section',
      'INBOOK' => 'Book Section',
      'INCOLLECTION' => 'Book Section',
      'INPROCEEDINGS' => 'Book Section',
      'MANUAL' => 'Computer Program',
      'MASTERSTHESIS' => 'Thesis',
      'MISC' => 'Generic',
      'OTHER' => 'Generic',
      'PHDTHESIS' => 'Thesis',
      'PROCEEDINGS' => 'Conference Proceedings',
      'TECHREPORT' => 'Report',
      'UNPUBLISHED' => 'Unpublished Work'
    ];
    $fieldwithmultiplevalue = array(
      '%Z'
    );
    $fieldwithouttab = [
      '%A',
      '%T',
      '%I',
      '%B',
      '%U'
    ];

    $res['%0'] = $typemap[$this->entrytype];

    foreach ($this->values as $key => $value)
    {
      $value = trim($value ?? "");
      if (($fieldmap[$key] ?? false) && 
          in_array($fieldmap[$key], $fieldwithmultiplevalue) && 
          $key != 'NOTE')
      {
        if (($res[$fieldmap[$key]] ?? false) && 
            strlen($res[$fieldmap[$key]]) > 0)
        {
          $res[$fieldmap[$key]] .= "; ";
        }
        $res[$fieldmap[$key]] = strtolower($key) . "=(" . $value . ")";
      }
    }

    $res['%F'] = $this->entryname;
    $res['%3'] = "BibTeX type = " . $this->entrytype;
    $ret = "";
    foreach ($res as $key => $value)
    {
      if (in_array($key, $fieldwithouttab))
      {
        $value = preg_replace("/\r/", " ", $value);
        $value = preg_replace("/\t/", "", $value);
      }

      //error_log("AAAA \n", 3, "$FarmD/my-errors.log");
      if ($key == "%A" or $key == "%E")
      {
        $value = str_replace(" and ", "\n" . $key . " ", $value);
      }

      $ret = $ret . $key . " " . $value . "\n";
    }

    return $ret;
  }

  function getBibEntry()
  {
    global $BibtexSilentFields, $BibtexGenerateDefaultUrlField;

    $ret = $ret . "@@@" . $this->entrytype . " { " . $this->entryname . ",\\\\\n";

    foreach ($this->values as $key => $value)
    {
      if ($BibtexSilentFields && in_array($key, $BibtexSilentFields))
      {
        continue;
      }
      $ret = $ret . "&nbsp;&nbsp;&nbsp;&nbsp;" . $key . " = { " . $value . " },\\\\\n";
    }

    if ($BibtexGenerateDefaultUrlField && ($this->get("URL") == false))
    {
      $ret = $ret . "&nbsp;&nbsp;&nbsp;&nbsp;URL = { " . $this->getCompleteEntryUrl() . " },\\\\\n";
    }
    $ret = $ret . "}@@\n";

    return $ret;
  }

  function getCompleteEntry()
  {
    $ret = "[[#" . $this->entryname . "]]\n!!!" . $this->entryname . "\n";
    $ret = $ret . '<div class="indent">' . $this->getSummary(false) . "</div>";
    $abstract = $this->getAbstract();
    if ($abstract)
    {
      $ret = $ret . "\n'''Abstract:'''\n" . '<div class="indent">' . $abstract . "</div>";
    }
    $comment = $this->getComment();
    if ($comment)
    {
      $ret = $ret . "\n'''Comment:'''\n" . '<div class="indent">' . $comment . "</div>";
    }
    $ret = $ret . "[[#" . $this->entryname . "Bib]]\n";
    $ret = $ret . "\n'''Bibtex entry:'''\n" . '<div class="indent">' . $this->getBibEntry() . "</div>";
    return $ret;
  }

  function getSolePageEntry()
  {
    // Reference
    $ret = "!" . $this->entryname . "\n";
    $ret = $ret . "\n!!!$[reference]\n";
    $ret = $ret . $this->getSummary(false);

    $abstract = $this->getAbstract();
    if ($abstract)
    {
      $ret = $ret . "\n\n!!!$[abstract]\n" . $abstract;
    }

    // Links
    $ret = $ret . "\n\n!!!$[links]\n";

    $sep = "&nbsp;|&nbsp;";

    $preceded = false;

    // Page de pub
    $ret = $ret . "(:if auth edit:)%newwin%[[$this->bibfile]]$sep(:ifend:)";

    // Direct URL
    $url = $this->get("URL");
    if ($url)
    {
      global $BibtexPdfLink, $BibtexScopusLink, $PublicationUploadGroup;

      $host = 'http://' . $_SERVER['HTTP_HOST'];
      if (strpos($url, 'www.scopus.com') !== false)
      {
        $ret = $ret . "%newwin%[[" . $url . " | $BibtexScopusLink]]";
      }
      elseif (strpos($url, $host) !== false)
      {
        $ret = $ret . "%newwin%[[Attach:" . $PublicationUploadGroup . "/" . substr($url, strpos($url, "uploads/") + 8) . " | $BibtexPdfLink]]";
      }
      elseif (strpos($url, '.pdf') !== false)
      {
        $ret = $ret . "%newwin%[[" . $url . " | $BibtexPdfLink]]";
      }
      else
      {
        $ret = $ret . "%newwin%[[" . $url . " | $[directlink]]]";
      }
      $preceded = true;
    }

    // Indirect (DOI)
    $doi = $this->get("DOI");
    if ($doi)
    {
      if ($preceded)
      {
        $ret = $ret . $sep;
      }
      if (strpos($doi, "http") === false)
      {
        $doi = "https://doi.org/" . $doi;
      }

      $ret = $ret . "%newwin%[[" . str_replace(array("(", ")", "[", "]"), array("%28", "&29", "%5B", "%5D"), $doi) . " | $[doilink]]]";
      $preceded = true;
    }

    // Google or Google Scholar
    $title = urlencode($this->getTitle());
    if ($title)
    {
      global $BibtexGoogleScholarLink;
      $isbn = $this->get("ISBN");
      $booktitle = $this->get("BOOKTITLE");
      $note = $this->get("NOTE");

      if ($preceded)
      {
        $ret = $ret . "&nbsp;|&nbsp;";
      }

      if ($this->entrytype == "BOOK" || $this->entrytype == "INCOLLECTION" || $this->entrytype == "INBOOK")
      {
        $ret = $ret . "[[https://www.google.com/search?q=" . $title . "| $[searchgoogletitle]]]";
        if ($booktitle)
        {
          $ret = $ret . $sep . "[[https://www.google.com/search?q=" . $booktitle . "| $[searchgooglebooktitle]]]";
        }
        if ($isbn)
        {
          $ret = $ret . "&nbsp;|&nbsp;[[https://www.google.com/search?q=%22" . $isbn . "%22| $[searchgoogleisbn]]]";
        }
      }
      else
      {
        if ($this->entrytype == "MASTERSTHESIS" || $this->entrytype == "PHDTHESIS" || $this->entrytype == "TECHREPORT" || strpos($note, "CEFRSC") !== false)
        {
          $ret = $ret . "[[https://www.google.com/search?q=" . $title . "| $[searchgoogletitle]]]";
        }
        else
        {
          $ret = $ret . "[[https://scholar.google.ca/scholar?q=%22" . $title . "%22| $BibtexGoogleScholarLink]]";
        }
      }
    }

    $ret = $ret . "\n\n!!!$[endnoteformat]\n$[youcanalso] [[{\$PageUrl}?action=bibtexquery&bibfile=" . $this->bibfile . "&filter=" . urlencode("\$this->entryname==\"" . $this->entryname . "\"") . "&format=endnote|$[import]]] $[inendnote]";
    $ret = $ret . "\n\n!!!$[csvformat]\n$[youcanalso] [[{\$PageUrl}?action=bibtexquery&bibfile=" . $this->bibfile . "&filter=" . urlencode("\$this->entryname==\"" . $this->entryname . "\"") . "&format=csv|$[import]]] $[incsv]";
    $ret = $ret . "\n\n!!!$[bibtexformat]\n$[youcan] [[{\$PageUrl}?action=bibtexquery&bibfile=" . $this->bibfile . "&filter=" . urlencode("\$this->entryname==\"" . $this->entryname . "\"") . "&format=bibtex|$[import]]] $[jabref]\n\n" . $this->getBibEntry();
    return $ret;
  }
}

// *****************************
// PhdThesis
// *****************************
class PhdThesis extends BibtexEntry
{
  function __construct($bibfile, $entryname)
  {
    parent::__construct($bibfile, $entryname);
    $this->entrytype = "PHDTHESIS";
  }

  function getSummary($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    $ret = parent::getPreString($dourl, $ignorelinks, $aut_limit);
    $ret = $ret . " $[doctorat]";
    $school = parent::get("SCHOOL");
    if ($school)
    {
      $ret = $ret . ", ''" . $school . "''";
    }
    return str_replace("\\&", "&", $ret . parent::getPostString($dourl));
  }
}

// *****************************
// MasterThesis
// *****************************
class MasterThesis extends BibtexEntry
{
  function __construct($bibfile, $entryname)
  {
    parent::__construct($bibfile, $entryname);
    $this->entrytype = "MASTERSTHESIS";
  }

  function getSummary($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    global $BibtexBaccThesisCode, $BibtexMasterEssayCode;
    $ret = parent::getPreString($dourl, $ignorelinks, $aut_limit);

    $note = $this->get("NOTE");
    if (strpos($note, $BibtexBaccThesisCode) !== false)
    {
      $ret = $ret . " $[bacc]";
    }
    elseif (strpos($note, $BibtexMasterEssayCode) !== false)
    {
      $ret = $ret . " $[essay]";
    }
    else
    {
      $ret = $ret . " $[master]";
    }
    $school = parent::get("SCHOOL");
    if ($school)
    {
      $ret = $ret . ", ''" . $school . "''";
    }
    return str_replace("\\&", "&", $ret . parent::getPostString($dourl));
  }
}

// *****************************
// TechReport
// *****************************
class TechReport extends BibtexEntry
{
  function __construct($bibfile, $entryname)
  {
    parent::__construct($bibfile, $entryname);
    $this->entrytype = "TECHREPORT";
  }

  function getSummary($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    $ret = parent::getPreString($dourl, $ignorelinks, $aut_limit);
    $type = parent::get("TYPE");
    if ($type)
    {
      $ret = $ret . " $type";
    }
    else
    {
      $ret = $ret . " Technical report";
    }

    $number = parent::get("NUMBER");
    if ($number)
    {
      $ret = $ret . " $number";
    }
    $institution = parent::get("INSTITUTION");
    if ($institution)
    {
      $ret = $ret . ", " . $institution;
    }
    $pString = parent::getPostString($dourl);
    if (isset($pString) && ($ret[strlen($ret) - 1] == ')' || $ret[strlen($ret) - 1] == '.'))
    {
      $pString = " " . substr($pString, 2);
    }
    return str_replace("\\&", "&", $ret . $pString);
  }
}

// *****************************
// Article
// *****************************
class Article extends BibtexEntry
{
  function __construct($bibfile, $entryname)
  {
    parent::__construct($bibfile, $entryname);
    $this->entrytype = "ARTICLE";
  }

  function getSummary($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    $ret = parent::getPreString($dourl, $ignorelinks, $aut_limit);
    $journal = parent::get("JOURNAL");
    if ($journal)
    {
      $ret = $ret . " " . str_replace("\\&", "&", $journal);
      $volume = parent::get("VOLUME");
      if ($volume)
      {
        $ret = $ret . ", " . $volume;
      }
      $number = parent::get("NUMBER");
      if ($number)
      {
        if ($volume == "")
        {
          $ret = $ret . " ";
        }
        $ret = $ret . "(" . $number . ")";
      }
      if ($volume || $number)
      {
        $pages = parent::getPages();
        if ($pages)
        {
          $ret = $ret . ":" . $pages;
        }
      }
    }
    return str_replace("\\&", "&", $ret . parent::getPostString($dourl));
  }
}

// *****************************
// InProceedings
// *****************************
class InProceedings extends BibtexEntry
{
  function __construct($bibfile, $entryname)
  {
    parent::__construct($bibfile, $entryname);
    $this->entrytype = "INPROCEEDINGS";
  }

  function getSummary($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    $ret = parent::getPreString($dourl, $ignorelinks, $aut_limit);
    $booktitle = parent::get("BOOKTITLE");
    if (!$booktitle)
    {
      $booktitle = parent::get("JOURNAL");
    }
    if ($booktitle)
    {
      $ret = $ret . " In " . $booktitle . ".";

      $address = parent::get("ADDRESS");
      if ($address)
      {
        if ($ret[strlen($ret) - 1] != '.')
        {
          $ret = $ret . ".";
        }

        $ret = $ret . " " . $address;
      }

      $month = parent::get("MONTH");
      if ($month)
      {
        if ($ret[strlen($ret) - 1] != '.')
        {
          $ret = $ret . ",";
        }

        $ret = $ret . " " . $month;
      }

      $editor = $this->getEditors($ignorelinks);
//error_log("BibCreateSelectionAA draws=".$draws."\n", 3, "$FarmD/my-errors.log");
      if ($editor && $editor != $this->getAuthors($ignorelinks))
      {
        if ($ret[strlen($ret) - 1] != '.')
        {
          $ret = $ret . ".";
        }

        $ret = $ret . " (" . $editor . ", Eds.)";
      }

      $publisher = parent::get("PUBLISHER");
      if ($publisher)
      {
        if ($ret[strlen($ret) - 1] != ')')
        {
          $ret = $ret . ".";
        }
        $ret = $ret . " " . $publisher;
      }

      $pages = $this->getPagesWithLabel();
      if ($pages && $pages != '-')
      {
        if ($ret[strlen($ret) - 1] != ')' && $ret[strlen($ret) - 1] != '.')
        {
          $ret = $ret . ",";
        }
        elseif ($pages[0] == 'p')
        {
          $pages[0] = 'P';
        }

        $ret = $ret . " " . $pages;
      }

      $organization = parent::get("ORGANIZATION");
      if ($organization)
      {
        if ($ret[strlen($ret) - 1] != ')' && $ret[strlen($ret) - 1] != '.')
        {
          $ret = $ret . ". ";
        }
        $ret = $ret . $organization;
      }
    }
    $pString = parent::getPostString($dourl);
    // Remove the front point if the string already finishes with a point
    if ($ret[strlen($ret) - 1] == ')' || $ret[strlen($ret) - 1] == '.' || substr($ret, strlen($ret) - 3) == ".''")
    {
      $pString = substr($pString ?? "", 1);
    }
    return str_replace("\\&", "&", $ret . $pString);
  }
}

// *****************************
// Book
// *****************************
class Book extends BibtexEntry
{
  function __construct($bibfile, $entryname)
  {
    parent::__construct($bibfile, $entryname);
    $this->entrytype = "BOOK";
  }

  function getSummary($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    $author = $this->getAuthors($ignorelinks, $aut_limit);
    $editor = $this->getEditors($ignorelinks);
    $ret = "";
    if (!$author)
    {
      $ret = $ret . "'''" . $editor . ", Eds.'''";
    }

    $ret = $ret . parent::getPreString($dourl, $ignorelinks, $aut_limit);

    $series = parent::get("SERIES");
    if ($series)
    {
      $ret = $ret . " In ''" . $series;

      $number = parent::get("NUMBER");
      if ($number)
      {
        $ret = $ret . ", " . $number . ".''";
      }
      else
      {
        $ret = $ret . ".''";
      }
    }

    if ($editor && $author && $editor != $author)
    {
      $ret = $ret . " (" . $editor . ", Eds.)";
    }

    $publisher = parent::get("PUBLISHER");
    if ($publisher)
    {
      $ret = $ret . " " . $publisher;
    }

    $address = parent::get("ADDRESS");
    if ($address)
    {
      if ($ret && $ret[strlen($ret) - 1] != "." && $ret[strlen($ret) - 1] != "'")
      {
        $ret = $ret . ",";
      }
      $ret = $ret . " $address";
    }

    $pages = $this->getPages();
    if (($publisher || $address) && $pages && $ret && $ret[strlen($ret) - 1] != ".")
    {
      $ret = $ret . ".";
    }

    if ($pages)
    {
      $ret = $ret . " " . $pages . " p";
    }

    // Remove the point at the end of the string if only the title was provided
    if ($ret && $ret[strlen($ret) - 3] == '.')
    {
      $ret = substr_replace($ret, "", strlen($ret) - 3, 1);
    }

    $post = parent::getPostString($dourl);
    $ret = $ret . $post;

    return str_replace("\\&", "&", $ret);
  }
}

// *****************************
// InBook
// *****************************
class InBook extends BibtexEntry
{
  function __construct($bibfile, $entryname)
  {
    parent::__construct($bibfile, $entryname);
    $this->entrytype = "INBOOK";
  }

  function getSummary($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    $ret = parent::getPreString($dourl, $ignorelinks, $aut_limit);

    $chapter = parent::get("CHAPTER");
    if ($chapter)
    {
      if (strlen($ret) > 2 && $ret[strlen($ret) - 3] != '?' && $ret[strlen($ret) - 3] != '.')
      {
        $ret = $ret . ".";
      }

      $ret = $ret . " (Chap. " . $chapter . ")";
    }

    $booktitle = parent::get("BOOKTITLE");
    $series = parent::get("SERIES");
    if ($booktitle || $series)
    {
      if ($booktitle)
      {
        $ret = $ret . " In " . $booktitle;
      }
      else
      {
        $ret = $ret . " In " . $series;
      }

      $editor = $this->getEditors($ignorelinks);
      if ($editor)
      {
        if ($ret[strlen($ret) - 1] != '.')
        {
          $ret = $ret . ".";
        }

        $ret = $ret . " (" . $editor . ", Eds.)";
      }

      $address = parent::get("ADDRESS");
      if ($address)
      {
        if ($ret[strlen($ret) - 1] != '.' && $ret[strlen($ret) - 1] != ')')
        {
          $ret = $ret . ".";
        }

        $ret = $ret . " " . $address;
      }

      $publisher = parent::get("PUBLISHER");
      if ($publisher)
      {
        if ($ret[strlen($ret) - 1] != ',' && $ret[strlen($ret) - 1] != ')')
        {
          $ret = $ret . ",";
        }
        $ret = $ret . " " . $publisher;
      }

      $pages = $this->getPagesWithLabel();
      if ($pages)
      {
        if ($ret[strlen($ret) - 1] != ')')
        {
          $ret = $ret . ",";
        }
        elseif ($pages[0] == 'p')
        {
          $pages[0] = 'P';
        }

        $ret = $ret . " " . $pages;
      }

      $organization = parent::get("ORGANIZATION");
      if ($organization)
      {
        if ($ret[strlen($ret) - 1] != ')')
        {
          $ret = $ret . ", ";
        }
        $ret = $ret . ". " . $organization;
      }
    }
    return str_replace("\\&", "&", $ret . parent::getPostString($dourl));
  }
}

// *****************************
// InCollection
// *****************************
class InCollection extends BibtexEntry
{
  function __construct($bibfile, $entryname)
  {
    parent::__construct($bibfile, $entryname);
    $this->entrytype = "INCOLLECTION";
  }

  function getSummary($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    $ret = parent::getPreString($dourl, $ignorelinks, $aut_limit);

    $chapter = parent::get("CHAPTER");
    if ($chapter)
    {
      if (strlen($ret) > 2 && $ret[strlen($ret) - 3] != '?' && $ret[strlen($ret) - 3] != '.')
      {
        $ret = $ret . ".";
      }

      $ret = $ret . " (Chap. " . $chapter . ")";
    }

    $booktitle = parent::get("BOOKTITLE");
    $series = parent::get("SERIES");
    if ($booktitle || $series)
    {
      if ($booktitle)
      {
        $ret = $ret . " In " . $booktitle;
      }
      else
      {
        $ret = $ret . " In " . $series;
      }

      $editor = parent::getEditors();
      if ($editor)
      {
        if ($ret[strlen($ret) - 1] != '.')
        {
          $ret = $ret . ".";
        }

        $ret = $ret . " (" . $editor . ", Eds.)";
      }

      $address = parent::get("ADDRESS");
      if ($address)
      {
        if ($ret[strlen($ret) - 1] != '.' && $ret[strlen($ret) - 1] != ')')
        {
          $ret = $ret . ".";
        }

        $ret = $ret . " " . $address;
      }

      $publisher = parent::get("PUBLISHER");
      if ($publisher)
      {
        if ($ret[strlen($ret) - 1] != ',' && $ret[strlen($ret) - 1] != ')')
        {
          $ret = $ret . ",";
        }
        $ret = $ret . " " . $publisher;
      }

      $pages = $this->getPagesWithLabel();
      if ($pages)
      {
        if ($ret[strlen($ret) - 1] != ')')
        {
          $ret = $ret . ",";
        }
        elseif ($pages[0] == 'p')
        {
          $pages[0] = 'P';
        }

        $ret = $ret . " " . $pages;
      }

      $organization = parent::get("ORGANIZATION");
      if ($organization)
      {
        if ($ret[strlen($ret) - 1] != ')')
        {
          $ret = $ret . ", ";
        }
        $ret = $ret . ". " . $organization;
      }
    }
    return str_replace("\\&", "&", $ret . parent::getPostString($dourl));
  }

}

// *****************************
// Proceedings
// *****************************
class Proceedings extends BibtexEntry
{
  function __construct($bibfile, $entryname)
  {
    parent::__construct($bibfile, $entryname);
    $this->entrytype = "PROCEEDINGS";
  }

  function getSummary($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    $editor = $this->getEditors($ignorelinks);
    if ($editor)
    {
      $ret = "'''" . $editor . ", Eds.'''";
    }

    $year = $this->get("YEAR");
    if ($year)
    {
      $ret = $ret . " (";
      $ret = $ret . $year . ") ";
    }

    $title = $this->getTitle();
    if ($title)
    {
      $ret = $ret . "''" . $title;

      if (strlen($ret) > 2 && $ret[strlen($ret) - 1] != '?')
      {
        $ret = $ret . ".";
      }

      $ret = $ret . "''";
    }

    $volume = parent::get("VOLUME");
    if ($volume)
    {
      $ret = $ret . "volume " . $volume;
      $series = parent::get("SERIES");
      if ($series != "")
      {
        $ret = $ret . " of ''$series''";
      }
    }
    //error_log("AA ret=".$ret."\n", 3, "$FarmD/my-errors.log");
    $address = parent::get("ADDRESS");
    if ($address)
    {
      if (strlen($ret) > 3 && $ret[strlen($ret) - 3] != '.')
      {
        $ret = $ret . ",";
      }
      $ret = $ret . " $address";
    }
    $orga = parent::get("ORGANIZATION");
    if ($orga)
    {
      if (strlen($ret) > 3 && $ret[strlen($ret) - 3] != '.')
      {
        $ret = $ret . ",";
      }
      $ret = $ret . " $orga";
    }
    $publisher = parent::get("PUBLISHER");
    if ($publisher)
    {
      if (strlen($ret) > 3 && $ret[strlen($ret) - 3] != '.')
      {
        $ret = $ret . ",";
      }
      $ret = $ret . " $publisher";
    }
    $ret = $ret . parent::getPostString($dourl);
    return str_replace("\\&", "&", $ret);
  }
}

// *****************************
// Misc
// *****************************
class Misc extends BibtexEntry
{
  function __construct($bibfile, $entryname)
  {
    parent::__construct($bibfile, $entryname);
    $this->entrytype = "MISC";
  }

  function getSummary($dourl = true, $ignorelinks = array(), $aut_limit = null)
  {
    $ret = parent::getPreString($dourl, $ignorelinks, $aut_limit);

    $howpublished = parent::get("HOWPUBLISHED");
    if ($howpublished)
    {
      $ret = $ret . " " . $howpublished . ".";
    }

    $ret = $ret . substr_replace((parent::getPostString($dourl) ?? ""), "", 0, 1);

    return str_replace("\\&", "&", $ret);
  }
}

function sortByField($a, $b)
{
  global $SortField;
  $f1 = $a->evalGet($SortField);
  $f2 = $b->evalGet($SortField);

  if ($f1 == $f2)
  {
    return 0;
  }

  return ($f1 < $f2) ? -1 : 1;
}

function BibCreateSelection($files, $cond, $sort, $max)
{
  global $BibEntries, $SortField;

  $files = trim($files ?? "");
  $cond = trim($cond ?? "");
  $sort = trim($sort ?? "");

  if ($sort !== "" && $sort[0] == '!')
  {
    $reverse = true;
    $sort = substr($sort, 1);
  }
  else $reverse = false;

  if ($cond == '')
  {
    $cond = 'true';
  }

//error_log("BibCreateSelectionAA cond=".$cond."\n", 3, "$FarmD/my-errors.log");

  if (!array_key_exists($files, $BibEntries))
  //if (!$BibEntries[$files])
  {
    $result = ParseBibFiles($files);
    if ($result !== true)
    {
      return "%red%Invalid BibTeX File List (" . $result . " does not exists)!";
    }
  }

  $res = [];
  $bibselectedentries = $BibEntries[$files];
//error_log("BibCreateSelectionBB cntBeforeCond=" . count($bibselectedentries) . "\n", 3, "$FarmD/my-errors.log");
  foreach ($bibselectedentries as $value)
  {
    if ($value->evalCond($cond))
    {
      $res[] = $value;
    }
  }
//error_log("BibCreateSelectionCC cntAfterCond=" . count($res) . "\n", 3, "$FarmD/my-errors.log");

  if ($sort != '')
  {
    if ($sort == 'random')
    {
      $lastIndex = count($res) - 1;
      $draws = min($max, $lastIndex + 1);

      $newArr = [];
      while ($draws > 0)
      {
        //error_log("BibCreateSelectionAA draws=".$draws."\n", 3, "$FarmD/my-errors.log");
        $rndIndex = rand(0, $lastIndex);
        $test = array_splice($res, $rndIndex, 1);
        $newArr[] = $test[0];
        $draws--;
        $lastIndex--;
      }

      $res = $newArr;
    }
    else
    {
      $SortField = $sort;
      usort($res, "sortByField");

      if ($reverse)
      {
        $res = array_reverse($res);
      }
    }
  }

  if ($max != '' && $sort != 'random')
  {
    $res = array_slice($res, 0, (int)$max);
  }

  return $res;
}

//function BibQuery($files, $cond, $sort, $max, $ignorelinks)
function BibQuery($m)
{
  $files = $m[1];
  $cond = $m[2];
  $sort = $m[3];
  $max = $m[4];
  $ignorelinks = $m[5] ?? "";

  if (isset($_GET['bibsort']))
  {
    $sort = trim($_GET['bibsort'] ?? "");
    $sort = str_replace("\\'", "'", trim($_GET['sort'] ?? ""));
  }

  if (isset($_GET['bibfilter']))
  {
    $cond = trim($_GET['bibfilter'] ?? "");
    $cond = str_replace("\\'", "'", trim($cond ?? ""));
  }

  $ret = '';

  $res = [];
  $res = BibCreateSelection($files, $cond, $sort, $max);
  if (!is_array($res))
  {
    return $res;
  }

  $ignore_links_as_regex = explode(";", $ignorelinks);

  $ignore_links_as_regex = preg_filter(array("/^\s*/", "/\./", "/\s*$/"), array("/", "\\.", "(?=[,\s]|\Z)/"), $ignore_links_as_regex);

  foreach ($res as $value)
  {
    $ret .= "#" . $value->getSummary(true, $ignore_links_as_regex, 10) . "\n";
  }
  
  if ($ret == "") return "$[aucun]\\\\
";

  return $ret;
}

function BibQueryURL($pagename)
{
  global $HTTPHeaders, $PageStartFmt, $PageEndFmt, $bibentry, $bibfile, $FarmD;

  $bibfile = trim($_GET['bibfile'] ?? "");
  $bibfilepage = trim($_GET['bibfilepage'] ?? "");
  $title = trim($_GET['title'] ?? "");
  $file = trim($_GET['file'] ?? "");
  $format = trim($_GET['format'] ?? "");
  $cond = str_replace("\\'", "'", trim($_GET['filter'] ?? ""));
  $sort = str_replace("\\'", "'", trim($_GET['sort'] ?? ""));
  $groupbytype = trim($_GET['groupbytype'] ?? "");

  $ret = '';
  $res = [];

  // if bibfilepage is provided get the list of bibfile from a wiki page
  if ($bibfilepage != "")
  {
    // check if bibfilepage file exist
    $sourcefile = MakePageName($pagename, $bibfilepage);
    if (!PageExists($sourcefile))
    {
      SDV($HandleBibtexFmt, array(&$PageStartFmt, MarkupToHTML($pagename, "%red%Invalid BibTeX File Page (" . $sourcefile . " does not exists)!"), &$PageEndFmt));
      PrintFmt($pagename, $HandleBibtexFmt);
      return;
    }
    // read the bibfilepage file
    $bibfile = ReadPage($sourcefile, READPAGE_CURRENT);
    $bibfile = trim($bibfile['text'] ?? "");
  }

  $res = BibCreateSelection($bibfile, $cond, $sort, "");
  if (!is_array($res))
  {
    SDV($HandleBibtexFmt, array(&$PageStartFmt, MarkupToHTML($pagename, $res), &$PageEndFmt));
    PrintFmt($pagename, $HandleBibtexFmt);
    return;
  }

  // Send a bibtex file or a page of formated reference
  if ($file == 1 or strtolower($format) == "bib" or strtolower($format) == "bibtex")
  {
    foreach ($res as $key => $value)
    {
      $ret .= $value->getBibEntryText() . "\n";
    }

    foreach ($HTTPHeaders as $h)
    {
      $h = preg_replace('!^Content-type:\\s+text/html!i', 'Content-type: text/bibtex', $h);
      $h = preg_replace('!no-store!i', 'post-check=0', $h);
      $h = preg_replace('!no-cache!i', 'pre-check=0', $h);
      header($h);
    }
    header('Content-Disposition: attachment; filename="resultat.bib"');

    //echo (iconv("Windows-1252", "UTF-8", @$ret));
    echo (@$ret);
  }
  elseif (strtolower($format) == "endnote")
  {
    foreach ($res as $value)
    {
      $ret .= $value->getEndNoteEntryText() . "\n";
    }

    foreach ($HTTPHeaders as $h)
    {
      $h = preg_replace('!^Content-type:\\s+text/html!i', 'Content-type: text/endnote', $h);
      $h = preg_replace('!no-store!i', 'post-check=0', $h);
      $h = preg_replace('!no-cache!i', 'pre-check=0', $h);
      header($h);
    }
    header('Content-Disposition: attachment; filename="resultat.enw"');
    echo (@$ret);
  }
  elseif (strtolower($format) == "csv")
  {
    $exportFieldList = [
      'ABSTRACT',
      'ADDRESS',
      'ANNOTE',
      'ASSIGNEE',
      'AUTHOR',
      'BOOKTITLE',
      'CHAPTER',
      'CROSSREF',
      'COMMENTS',
      'DAY',
      'DAYFILED',
      'DOI',
      'EDITION',
      'EDITOR',
      'EID',
      'FILE',
      'HOWPUBLISHED',
      'INSTITUTION',
      'JOURNAL',
      'KEY',
      'KEYWORDS',
      'LANGUAGE',
      'LASTCHECKED',
      'MONTH',
      'NOTE',
      'NUMBER',
      'ORGANIZATION',
      'OWNER',
      'PAGES',
      'PART',
      'PUBLISHER',
      'REVIEW',
      'REVISION',
      'SCHOOL',
      'SERIES',
      'TIMESTAMP',
      'TITLE',
      'TYPE',
      'URL',
      'VOLUME',
      'YEAR',
      'YEARFILED'
    ];

    $ret = "bibtype;bibtexkey;" . implode(";", array_map('strtolower', $exportFieldList)) . "\n";

    foreach ($res as $value)
    {
      $ret .= $value->getBibEntryCSV($exportFieldList) . "\n";
    }

    foreach ($HTTPHeaders as $h)
    {
      $h = preg_replace('!^Content-type:\\s+text/html!i', 'Content-type: text/csv', $h);
      $h = preg_replace('!no-store!i', 'post-check=0', $h);
      $h = preg_replace('!no-cache!i', 'pre-check=0', $h);
      header($h);
    }
    header('Content-Disposition: attachment; filename="resultat.csv"');
    echo (@$ret);
  }
  elseif (strtolower($format) == "frqnt")
  {
    $exportFieldList = [
      'CATEGORIE_PUBLICATION',
      'AUTRE_CATÉGORIE',
      'TITRE',
      'NOM_REVUE_PUBLICATION_DETAIL',
      'DIRECTEUR_PUBLICATION',
      'LIEU_PUBLICATION',
      'MAISON_EDITION',
      'NUMERO_VOLUME',
      'NUMERO_PUBLICATION',
      'NOMBRE_PAGE',
      'NUMERO_PAGE_DEBUT',
      'NUMERO_PAGE_FIN',
      'STATUT',
      'DATE_PUBLICATION',
      '1_AUTEUR_NIP',
      '1_AUTEUR_NOM',
      '1_AUTEUR_PRENOM',
      '2_AUTEUR_NIP',
      '2_AUTEUR_NOM',
      '2_AUTEUR_PRENOM',
      '3_AUTEUR_NIP',
      '3_AUTEUR_NOM',
      '3_AUTEUR_PRENOM',
      '4_AUTEUR_NIP',
      '4_AUTEUR_NOM',
      '4_AUTEUR_PRENOM',
      '5_AUTEUR_NIP',
      '5_AUTEUR_NOM',
      '5_AUTEUR_PRENOM',
      'AL'
    ];

    $ret = implode(";", $exportFieldList) . "\n";

    foreach ($res as $value)
    {
      $ret .= $value->getBibEntryFRQNT() . "\n";
    }

    foreach ($HTTPHeaders as $h)
    {
      $h = preg_replace('!^Content-type:\\s+text/html!i', 'Content-type: text/csv', $h);
      $h = preg_replace('!no-store!i', 'post-check=0', $h);
      $h = preg_replace('!no-cache!i', 'pre-check=0', $h);
      header($h);
    }
    header('Content-Disposition: attachment; filename="resultat.csv"');
    echo (@$ret);
  }
  // return a web page
  else
  {
    // handle title
    if ($title == "")
    {
      $title = "(:if userlang fr:)R&eacute;sultats(:if userlang en:)Results(:if:)";
    }
    $ret .= "!!'''" . $title . "'''\n\n";

    // add format links
    $ret .= "(:if userlang fr:)T&eacute;l&eacute;charger ces r&eacute;sultats en format(:if userlang en:)Download these results in(:if:) ";

    $baseurl = "http://" . $_SERVER['HTTP_HOST'] . str_replace("$", "%24", $_SERVER['REQUEST_URI']);

    $ret .= "%newwin%[[" . $baseurl . "&format=bibtex|BibTeX]]"; //bibtex
    $ret .= ", %newwin%[[" . $baseurl . "&format=bibtex|EndNote]]"; // endnote
    $ret .= " (:if userlang fr:)ou(:if userlang en:)or(:if:) %newwin%[[" . $baseurl . "&format=csv|BibTeX&#8209;CSV]]."; // bibtex-csv
    $ret .= "\n\n\n";

    if (count($res) == 0)
    {
      $ret .= "Aucune";
    }
    else
    {
      if ($groupbytype)
      {
        $types = [
          '$[books]' => "\$this->entrytype == 'BOOK'",
          '$[bookchapters]' => "\$this->entrytype == 'INBOOK' or \$this->entrytype == 'INCOLLECTION'",
          '$[bookeditor]' => "\$this->entrytype == 'PROCEEDINGS'",
          '$[articlewithcommittee]' => "\$this->entrytype == 'ARTICLE' and strpos(\$this->get('NOTE'),'CEFRSC')===FALSE", 
          '$[inproceedings]' => "\$this->entrytype == 'INPROCEEDINGS'",
          '$[techreport]' => "\$this->entrytype == 'TECHREPORT' or \$this->entrytype == 'MANUAL' or \$this->entrytype == 'MISC' or \$this->entrytype == 'BOOKLET'",
          '$[thesis]' => "\$this->entrytype == 'PHDTHESIS' or \$this->entrytype == 'MASTERSTHESIS'",
          '$[articlewithoutcommittee]' => "\$this->entrytype == 'ARTICLE' and strpos(\$this->get('NOTE'),'CEFRSC')!==FALSE"
        ];
        foreach ($types as $type => $cond)
        {
          $ret .= "!!!!!'''" . $type . "'''\n\n\n";
          foreach ($res as $value)
          {
            if ($value->evalCond($cond))
            {
              $ret .= "#" . $value->getSummary() . "\n";
            }
          }
          $ret .= "\n\n\n";
        }
      }
      else
      {
        foreach ($res as $key => $value)
        {
          $ret .= "#" . $value->getSummary() . "\n";
        }
      }
    }

    SDV($HandleBibtexFmt, array(&$PageStartFmt, MarkupToHTML($pagename, $ret), &$PageEndFmt));
    PrintFmt($pagename, $HandleBibtexFmt);
  }
}

function HandleBibEntry($pagename)
{
  global $HTTPHeaders, $PageStartFmt, $PageEndFmt, $ScriptUrl, $bibentry, $bibfile, $bibref;

  $bibfile = $_GET['bibfile'];
  $bibref = $_GET['bibref'];
  $format = trim($_GET['format'] ?? "");
  SDV($ScriptUrl, FmtPageName('$PageUrl', $pagename));

  $bibentry = GetEntry($bibfile, $bibref);

  // Send a bibtex file or a page of formated reference
  if ($file == 1 or strtolower($format) == "bib" or strtolower($format) == "bibtex")
  {
    $ret = $bibentry->getBibEntryText() . "\n";

    foreach ($HTTPHeaders as $h)
    {
      $h = preg_replace('!^Content-type:\\s+text/html!i', 'Content-type: text/bibtex', $h);
      $h = preg_replace('!no-store!i', 'post-check=0', $h);
      $h = preg_replace('!no-cache!i', 'pre-check=0', $h);
      header($h);
    }
    header('Content-Disposition: attachment; filename="resultat.bib"');
    echo (@$ret);
  }
  elseif (strtolower($format) == "endnote")
  {
    $ret = $bibentry->getEndNoteEntryText() . "\n";

    foreach ($HTTPHeaders as $h)
    {
      $h = preg_replace('!^Content-type:\\s+text/html!i', 'Content-type: text/endnote', $h);
      $h = preg_replace('!no-store!i', 'post-check=0', $h);
      $h = preg_replace('!no-cache!i', 'pre-check=0', $h);
      header($h);
    }
    header('Content-Disposition: attachment; filename="resultat.enw"');
    echo (@$ret);
  }
  elseif (strtolower($format) == "csv")
  {
    $exportFieldList = [
      'ABSTRACT',
      'ADDRESS',
      'ANNOTE',
      'ASSIGNEE',
      'AUTHOR',
      'BOOKTITLE',
      'CHAPTER',
      'CROSSREF',
      'COMMENTS',
      'DAY',
      'DAYFILED',
      'DOI',
      'EDITION',
      'EDITOR',
      'EID',
      'FILE',
      'HOWPUBLISHED',
      'INSTITUTION',
      'JOURNAL',
      'KEY',
      'KEYWORDS',
      'LANGUAGE',
      'LASTCHECKED',
      'MONTH',
      'NOTE',
      'NUMBER',
      'ORGANIZATION',
      'OWNER',
      'PAGES',
      'PART',
      'PUBLISHER',
      'REVIEW',
      'REVISION',
      'SCHOOL',
      'SERIES',
      'TIMESTAMP',
      'TITLE',
      'TYPE',
      'URL',
      'VOLUME',
      'YEAR',
      'YEARFILED'
    ];
    $ret = "bibtype;bibtexkey;" . implode(";", array_map('strtolower', $exportFieldList)) . "\n";
    $ret .= $bibentry->getBibEntryCSV($exportFieldList) . "\n";

    foreach ($HTTPHeaders as $h)
    {
      $h = preg_replace('!^Content-type:\\s+text/html!i', 'Content-type: text/csv', $h);
      $h = preg_replace('!no-store!i', 'post-check=0', $h);
      $h = preg_replace('!no-cache!i', 'pre-check=0', $h);
      header($h);
    }
    header('Content-Disposition: attachment; filename="resultat.csv"');
    echo (@$ret);
  }
  // return a web page
  else
  {
    $page = [
      'timefmt' => @$GLOBALS['CurrentTime'],
      'author' => @$GLOBALS['Author']
    ];

    SDV($HandleBibtexFmt, array(&$PageStartFmt,
      'function:PrintCompleteEntry', &$PageEndFmt
    ));
    PrintFmt($pagename, $HandleBibtexFmt);
  }
}

function PrintCompleteEntry()
{
  global $bibentry, $bibfile, $bibref, $pagename;
  if ($bibentry == false)
  {
    echo MarkupToHTML($pagename, "%red%Invalid BibTex Entry: [" . $bibfile . ", " . $bibref . "]!");
  }
  else
  {
    echo MarkupToHTML($pagename, $bibentry->getSolePageEntry());
  }
}

function GetEntry($bib, $ref)
{
  global $BibEntries;
  $ref = str_replace("\\", "", trim($ref ?? ""));
  $bib = trim($bib ?? "");
  $bibtable = $BibEntries[$bib];
  if ($bibtable == false)
  {
    ParseBibFiles($bib);
    $bibtable = $BibEntries[$bib];
  }

  reset($bibtable);

  foreach ($bibtable as $value)
  {
    if ($value->getName() == $ref)
    {
      $bibref = $value;
      break;
    }
  }

  if ($bibref == false)
  {
    return false;
  }
  return $bibref;
}

function BibCite($m)
{
  $entry = GetEntry($m[1], $m[2]);
  if ($entry == false)
  {
    return "%red%Invalid BibTex Entry!";
  }
  return $entry->cite();
}

function BibCount($m)
{
  global $BibEntries;
  $bib = trim($m[1] ?? "");
  $bibtable = $BibEntries[$bib];
  if ($bibtable == false)
  {
    ParseBibFiles($bib);
    $bibtable = $BibEntries[$bib];
  }
  if ($bibtable == false)
  {
    return "%red%Invalid BibTex Entry!";
  }

  return count($bibtable);
}

function CompleteBibEntry($m)
{
  $bib = $m[1];
  $ref = $m[2];
  $entry = GetEntry($bib, $ref);
  if ($entry == false)
  {
    return "%red%Invalid BibTex Entry!";
  }
  return $entry->getCompleteEntry();
}

function BibSummary($m)
{
  $bib = $m[1];
  $ref = $m[2];
  $entry = GetEntry($bib, $ref);
  if ($entry == false)
  {
    return "%red%Invalid BibTex Entry!";
  }
  return $entry->getSummary();
}

function ParseEntries($fname, $entries, $filelist)
{
  global $BibEntries;
  $nb_entries = count($entries[0]);

//error_log("ParseEntries 1111 fname=".$fname." nb_entries=".$nb_entries."\n", 3, "$FarmD/my-errors.log");

  $bibfileentry = [];
  for ($i = 0;$i < $nb_entries;++$i)
  {
    $entrytype = strtoupper($entries[1][$i]);

    $entryname = $entries[2][$i];

    //if ($i < 5)
    //  print "<font color=#FF0000>Allo nb_entries=$nb_entries entryname=$entryname</font><br>\n";
    if ($entrytype == "ARTICLE")
    {
      $entry = new Article($fname, $entryname);
    }
    else if ($entrytype == "INPROCEEDINGS" or $entrytype == "CONFERENCE")
    {
      $entry = new InProceedings($fname, $entryname);
    }
    else if ($entrytype == "PHDTHESIS")
    {
      $entry = new PhdThesis($fname, $entryname);
    }
    else if ($entrytype == "MASTERSTHESIS")
    {
      $entry = new MasterThesis($fname, $entryname);
    }
    else if ($entrytype == "INCOLLECTION")
    {
      $entry = new InCollection($fname, $entryname);
    }
    else if ($entrytype == "BOOK")
    {
      $entry = new Book($fname, $entryname);
    }
    else if ($entrytype == "INBOOK")
    {
      $entry = new InBook($fname, $entryname);
    }
    else if ($entrytype == "TECHREPORT")
    {
      $entry = new TechReport($fname, $entryname);
    }
    else if ($entrytype == "PROCEEDINGS")
    {
      $entry = new Proceedings($fname, $entryname);
    }
    else
    {
      $entry = new Misc($fname, $entryname);
    }

    // match all keys
    preg_match_all("/([\w-]+)\s*=\s*([^¶]+)¶?/", $entries[3][$i], $all_keys);

    for ($j = 0;$j < count($all_keys[0]);$j++)
    {
      $key = strtoupper($all_keys[1][$j]);
      $value = $all_keys[2][$j];
      // Remove the leading and ending braces or quotes if they exist.
      $value = preg_replace('/^\s*{(.*)}\s*$/', '\1', $value);
      // TODO: only run this regexp if the former didnt match
      $value = preg_replace('/^\s*"(.*)"\s*$/', '\1', $value);
      if ($key == 'JOURNAL')
      {
        $value = str_replace(array("\r\n", "\n", "\r", " \t", "\t"), " ", $value);
      }
      //error_log("key=".$key."\n", 3, "$FarmD/my-errors.log");
      //error_log("value=".$value."\n", 3, "$FarmD/my-errors.log");
      $entry->values[$key] = $value;
    }

    $bibfileentry[] = $entry;
  }

//error_log("ParseEntries 1111 CountBibEntries=".count($BibEntries)."\n", 3, "$FarmD/my-errors.log");
  //if (count($BibEntries[$filelist]) > 0)
  if (array_key_exists($filelist, $BibEntries) && count($BibEntries[$filelist]) > 0)
  {
    $BibEntries[$filelist] = array_merge($BibEntries[$filelist], $bibfileentry);
  }
  else
  {
    $BibEntries[$filelist] = $bibfileentry;
  }
}

function ParseBib($bib_file, $bib_file_string, $bib_file_list)
{
  // first split the bib file into several part
  // first let's do an ugly trick to replace the first { and the last } of each bib entry by another special char (to help with regexp)
  $count = 0;

  // Replace common character containing ¤ with other equivalent
  $bib_file_string = preg_replace("/â‰¤/", "&#8804;", $bib_file_string);
  $bib_file_string = preg_replace("/Ã¤/", "ä", $bib_file_string);
  for ($i = 0;$i < strlen($bib_file_string);$i++)
  {
    if ($bib_file_string[$i] == '{')
    {
      if ($count == 0)
      {
        $bib_file_string[$i] = '¤';
      }
      $count++;
    }
    else if ($bib_file_string[$i] == '}')
    {
      $count--;
      if ($count == 0)
      {
        $bib_file_string[$i] = '¤';
      }
    }
    else if ($bib_file_string[$i] == ',' && $count == 1)
    {
      $bib_file_string[$i] = '¶';
    }
    else if ($bib_file_string[$i] == "\r" && $count == 1)
    {
      $bib_file_string[$i] = '¶';
    }
  }
  $bib_file_string = preg_replace("/¶¶/", "¶", $bib_file_string);
  $nb_bibentry = preg_match_all("/@(\w+)\s*¤\s*([^¶¤]*)¶([^¤]*)¤/", $bib_file_string, $matches);

  ParseEntries($bib_file, $matches, $bib_file_list);
}

function ParseBibFiles($bib_file_list)
{
  $bibFileA = [];
  $bibFileA = explode(",", $bib_file_list);
  $result = true;

  foreach ($bibFileA as $value)
  {
    $result = ParseBibFile($value, $bib_file_list);
    if (!$result)
    {
      return trim($value ?? "");
    }
  }
  return $result;
}

function ParseBibFile($bib_file, $bib_file_list)
{
  global $BibtexBibDir, $pagename;

  trim($bib_file ?? "");
  $wikibib_file = MakePageName($pagename, $bib_file);

  if (PageExists($wikibib_file))
  {
    $bib_file_string = ReadPage($wikibib_file, READPAGE_CURRENT);
    $bib_file_string = $bib_file_string['text'];

    $bib_file_string = preg_replace("/\n/", "\r", $bib_file_string); // %0a

    ParseBib($bib_file, $bib_file_string, $bib_file_list);
    return true;
  }
  else
  {
    if (!$BibtexBibDir)
    {
      $BibtexBibDir = FmtPageName('$UploadDir$UploadPrefixFmt', $pagename);
    }

    if (is_file($BibtexBibDir . $bib_file))
    {
      $f = fopen($BibtexBibDir . $bib_file, "r");
      $bib_file_string = "";

      if ($f)
      {
        while (!feof($f))
        {
          $bib_file_string = $bib_file_string . fgets($f, 1024);
        }

        $bib_file_string = preg_replace("/\n/", "", $bib_file_string);

        ParseBib($bib_file, $bib_file_string, $bib_file_list);

        return true;
      }
      return false;
    }
  }
}

$UploadExts['bib'] = 'text/plain';

?>
