<?php
Markup('pubstat1', '<markup', '/\(:pubstat1\\s+(\\S.*?)\\s?:\)/', "PubStat1");
SDV($HandleActions['pubstat1'], 'PubStat1');

Markup('pubstat2', '<markup', '/\(:pubstat2\\s+(\\S.*?)\\s?:\)/', "PubStat2");
SDV($HandleActions['pubstat2'], 'PubStat2');

Markup('pubstat3', '<markup', '/\(:pubstat3\\s+(\\S.*?)\\s?:\)/', "PubStat3");
SDV($HandleActions['pubstat3'], 'PubStat3');

######################################
#
# Number of publications per journal
#
######################################
function PubStat1($m)
{
  global $HTTPHeaders, $PageStartFmt, $PageEndFmt, $bibfile;
  extract($GLOBALS['MarkupToHTML'] ?? []);

  //error_log("AAAA opt=".$opt."\n", 3, "c:/temp/my-errors.log");
  $opt = ParseArgs($m[1]);

  $action = trim($_GET['action'] ?? $opt['action'] ?? "");  
  $bibfile = trim($_GET['bibfile'] ?? $opt['bibfile'] ?? "");
  $bibfilepage = trim($_GET['bibfilepage'] ?? $opt['bibfilepage'] ?? "");
  $file = trim($_GET['file'] ?? $opt['file'] ?? "");
  $format = trim($_GET['format'] ?? $opt['format'] ?? "");
  $sort = str_replace("\\'", "'", trim($_GET['statsort'] ?? $opt['statsort'] ?? ""));
  $yearbeg = trim($_GET['an1'] ?? $opt['an1'] ?? "");
  $yearend = trim($_GET['an2'] ?? $opt['an2'] ?? "");
  $issnandimpactpage = trim($_GET['issnandimpactpage'] ?? $opt['issnandimpactpage'] ?? "");

  // if bibfilepage is provided get the list of bibfile from a wiki page
  if ($bibfilepage != "")
  {
    // check if bibfilepage file exist
    $bibfilepagename = MakePageName($pagename, $bibfilepage);
    if (!PageExists($bibfilepagename))
    {
      SDV($HandleBibtexFmt, array(&$PageStartFmt,
        MarkupToHTML($pagename, "%red%Invalid BibTeX File Page (" . $bibfilepagename . " does not exists)!") , &$PageEndFmt
      ));
      PrintFmt($pagename, $HandleBibtexFmt);
      return;
    }
    // read the bibfilepage file
    $bibfile = ReadPage($bibfilepagename, READPAGE_CURRENT);
    $bibfile = trim($bibfile['text']);
  }

  $impact = [];
  if ($issnandimpactpage != "")
  {
    # Check if source file exist
    $issnandimpactpagename = MakePageName($pagename, $issnandimpactpage);
    if (!PageExists($issnandimpactpagename))
    {
       return "%red%PubStat1: Invalid ISSN and Impact source File!";
    }
      # Read the source file
    $sourceCSV = ReadPage($issnandimpactpagename, READPAGE_CURRENT);
    $sourceCSV = trim($sourceCSV['text'])."\n";

    # Parse the CSV content
    $impactCSV = CSVTemplateParseCVSContent($sourceCSV, TRUE, ",");

    foreach ($impactCSV as $i)
    {
      $impact[$i["Revue"]] = array($i["p-ISSN"], $i["e-ISSN"], $i["AvecComite"], $i["ImpactFactor2007"], $i["ImpactFactor2010"], $i["ImpactFactor2015"], $i["ImpactFactor2022"]);
    }
  }

  if ((int)$yearbeg === 0)
  {
    $yearbeg = "";
  }

  if ((int)$yearend === 0)
  {
    $yearend = "";
  }

  if ($yearend == "")
  {
    $yearend = $yearbeg;
  }

  if ($yearbeg == "")
  {
    $yearbeg = $yearend;
  }

  $yearcond = "";
  $yeartext = "";
  if ($yearbeg != "" and $yearend != "")
  {
    $yearcond = " and \$this->get('YEAR')>='" . $yearbeg . "' and \$this->get('YEAR')<='" . $yearend . "'";
    if ($yearbeg != $yearend)
    {
      $yeartext = " entre " . $yearbeg . " et " . $yearend . " (inc.)";
    }
    else
    {
      $yeartext = " en " . $yearbeg;
    }
  }

  $cond = "\$this->entrytype=='ARTICLE' and strpos(\$this->get('NOTE'),'CEFRSC')===FALSE" . $yearcond;

  $ret = '';
  $res = [];

  $res = BibCreateSelection($bibfile, $cond, $sort, "");

  if (!is_array($res))
  {
    SDV($HandleBibtexFmt, array(&$PageStartFmt,
      MarkupToHTML($pagename, $res), &$PageEndFmt
    ));
    PrintFmt($pagename, $HandleBibtexFmt);
    return;
  }

  $nbpub = count($res);

  if ($nbpub == 0)
  {
    SDV($HandleBibtexFmt, array(&$PageStartFmt,
      MarkupToHTML($pagename, "Aucune") , &$PageEndFmt
    ));
    PrintFmt($pagename, $HandleBibtexFmt);
    return;
  }

  $journal = [];
  foreach($res as $key => $value)
  {
    $jname = $value->get('JOURNAL');
    if (!array_key_exists($jname, $journal)) {
        $journal[$jname] = 0;
    }
    $journal[$jname]++;
  }

  if ($sort == "")
  {
     arsort($journal);
  }
  if (strtolower($action) == "pubstat1" and $file == 1 or strtolower($format) == "csv")
  {
    $ret = "Revue;p-ISSN;e-ISSN;AvecComite;ImpactFactor2007;ImpactFactor2010;ImpactFactor2015;ImpactFactor2022;NbPub;Pourcentage;\n";
    foreach($journal as $key => $value)      
    {
      $ret .= "\"" . $key . "\";\"" . $impact[$key][0] . "\";\"" . $impact[$key][1] . "\";\"" . $impact[$key][2] . "\";\"" . $impact[$key][3] . "\";\"" . $impact[$key][4] . "\";\"" . $impact[$key][5] . "\";\"" . $impact[$key][6] . "\";\"" . $value . "\";" . sprintf("%01.2f", 100 * $value / $nbpub) . ";\n";
    }

    foreach ($HTTPHeaders as $h)
    {
      $h = preg_replace('!^Content-type:\\s+text/html!i', 'Content-type: application/ms-excel', $h);
      $h = preg_replace('!no-store!i', 'post-check=0', $h);
      $h = preg_replace('!no-cache!i', 'pre-check=0', $h);
      header($h);
    }
    header('Content-Disposition: attachment; filename="resultat.csv"');

    echo (chr(0xEF).chr(0xBB).chr(0xBF)); // send BOM before so the file open fine in Excel
    echo (@$ret);
  }
  else
  {
    $a = array(1,2);
    $ret = "%red%'''" . $nbpub . " articles'''%% ont été publiés %red%'''" . $yeartext . "'''%% dans %red%'''" . count($journal) . " revues'''%%.

----

";

    $ret .= "(:table class=\"zebra sortable\":)
(:cellnr valign=top:)%red%'''Revue'''
(:cell align=center valign=top:)%red%'''p-ISSN'''
(:cell align=center valign=top:)%red%'''e-ISSN'''
(:cell align=center valign=top:)%red%'''Avec comité'''
(:cell align=center valign=top:)%red%'''Facteur d'impact 2007'''
(:cell align=center valign=top:)%red%'''Facteur d'impact 2010'''
(:cell align=center valign=top:)%red%'''Facteur d'impact 2015'''
(:cell align=center valign=top:)%red%'''Facteur d'impact 2022'''
(:cell align=center valign=top:)%red%'''Nombre de\\\\
publication'''
(:cell align=center valign=top:)%red%'''Pourcentage\\\\
n=" . $nbpub . "'''
(:cell align=center valign=top:)%red%'''Cliquez&nbsp;pour&nbsp;voir\\\\
ces&nbsp;publications...'''
";
    $pcttot = 0;
    foreach($journal as $key => $value)
    {
      $im = $impact[$key] ?? null;
      $ret .= "(:cellnr valign=center:)[-'''" . $key . "'''-]
(:cell align=center valign=center:)[-" . str_replace("-", "&#8209;", $im[0] ?? "") . "-]
(:cell align=center valign=center:)[-" . str_replace("-", "&#8209;", $im[1] ?? "") . "-]
(:cell align=center valign=center:)[-" . ($im === null ? "" : $im[2] ?? "") . "-]
(:cell align=center valign=center:)[-" . ($im === null ? "" : $im[3] ?? "") . "-]
(:cell align=center valign=center:)[-" . ($im === null ? "" : $im[4] ?? "") . "-]
(:cell align=center valign=center:)[-" . ($im === null ? "" : $im[5] ?? "") . "-]
(:cell align=center valign=center:)[-" . ($im === null ? "" : $im[6] ?? "") . "-]
(:cell align=center valign=center:)[-'''" . $value . "'''-]
(:cell align=center valign=center:)[-" . sprintf("%01.2f", 100 * $value / $nbpub) . "-]\n";

      $pcttot += 100 * $value / $nbpub;

      if ($value == 1)
      {
        $seetext = "Voir&nbsp;cette&nbsp;publication...";
      }
      else
      {
        $seetext = "Voir&nbsp;ces&nbsp;publications...";
      }

      $ret .= "(:cell align=center valign=center:)[-%newwin%[[Membres.PublicationsParRevue?action=bibtexquery&title=" . urlencode("Publications du CEF dans ''" . $key . "''".$yeartext) . "&bibfilepage=Membres.PublicationsPageListe&filter=" . urlencode($cond . " and \$this->get('JOURNAL')=='" . str_replace("'", "\\\\'", $key) . "'") . "|" . $seetext . "]]-]
";
    }

    $ret .= "(:tableend:)";

    if (strtolower($action) == "pubstat1")
    {
      $ret = "!!'''Dans quelles revues les membres du CEF publient-ils?'''

Cette page présente le nombre d'article publiés par les membres du CEF par revue scientifique. Seul les articles proprement dit se retrouvent dans cette compilation. Sont donc exclus les livres, chapitres de livre, mémoires, thèses, etc...

Lorsque le \"Impact Factor\" d'une revue n'a pû être identifié, celui-ci est égale à zéro (0.000).

" . $ret;

      SDV($HandleBibtexFmt, array(&$PageStartFmt,
        MarkupToHTML($pagename, $ret) , &$PageEndFmt
      ));
      PrintFmt($pagename, $HandleBibtexFmt);
    }
    else
    {
      return $ret;
    }
  }
}

######################################
#
# Number of publications with X author regular members of the CEF
#
######################################
function PubStat2($m)
{
  global $HTTPHeaders, $PageStartFmt, $PageEndFmt, $bibfile;
  extract($GLOBALS['MarkupToHTML'] ?? []);

  //error_log("AAAA opt=".$opt."\n", 3, "c:/temp/my-errors.log");
  $opt = ParseArgs($m[1]);

  $action = trim($_GET['action'] ?? $opt['action'] ?? "");  
  $bibfile = trim($_GET['bibfile'] ?? $opt['bibfile'] ?? "");
  $bibfilepage = trim($_GET['bibfilepage'] ?? $opt['bibfilepage'] ?? "");
  $file = trim($_GET['file'] ?? $opt['file'] ?? "");
  $format = trim($_GET['format'] ?? $opt['format'] ?? "");
  $sort = str_replace("\\'", "'", trim($_GET['statsort'] ?? $opt['statsort'] ?? ""));
  $yearbeg = trim($_GET['an1'] ?? $opt['an1'] ?? "");
  $yearend = trim($_GET['an2'] ?? $opt['an2'] ?? "");

  // if bibfilepage is provided get the list of bibfile from a wiki page
  if ($bibfilepage != "")
  {
    // check if bibfilepage file exist
    $bibfilepagename = MakePageName($pagename, $bibfilepage);
    if (!PageExists($bibfilepagename))
    {
      SDV($HandleBibtexFmt, array(&$PageStartFmt,
        MarkupToHTML($pagename, "%red%Invalid BibTeX File Page (" . $bibfilepagename . " does not exists)!") , &$PageEndFmt
      ));
      PrintFmt($pagename, $HandleBibtexFmt);
      return;
    }
    // read the bibfilepage file
    $bibfile = ReadPage($bibfilepagename, READPAGE_CURRENT);
    $bibfile = trim($bibfile['text'] ?? "");
  }

  if ((int)$yearbeg === 0)
  {
    $yearbeg = "";
  }

  if ((int)$yearend === 0)
  {
    $yearend = "";
  }

  if ($yearend == "")
  {
    $yearend = $yearbeg;
  }
  if ($yearbeg == "")
  {
    $yearbeg = $yearend;
  }

  $yearcond = "";
  $yeartext = "";
  if ($yearbeg != "" and $yearend != "")
  {
    $yearcond = " and \$this->get('YEAR')>='" . $yearbeg . "' and \$this->get('YEAR')<='" . $yearend . "'";
    if ($yearbeg != $yearend)
    {
      $yeartext = " entre " . $yearbeg . " et " . $yearend . " (inc.)";
    }
    else
    {
      $yeartext = " en " . $yearbeg;
    }
  }

  $cond = "(\$this->entrytype=='BOOK' or \$this->entrytype=='INBOOK' or \$this->entrytype=='INCOLLECTION' or \$this->entrytype=='PROCEEDINGS' or \$this->entrytype=='ARTICLE'  or \$this->entrytype=='INPROCEEDINGS' or \$this->entrytype=='TECHREPORT')" . $yearcond;

  //error_log("AAAA cond=".$cond."\n", 3, "c:/temp/my-errors.log");
  $ret = '';
  $res = [];

  $res = BibCreateSelection($bibfile, $cond, $sort, "");

  if (!is_array($res))
  {
    SDV($HandleBibtexFmt, array(&$PageStartFmt,
      MarkupToHTML($pagename, $res) , &$PageEndFmt
    ));
    PrintFmt($pagename, $HandleBibtexFmt);
    return;
  }

  $nbpub = 0;

  // Scan all the entries, count the number of author member of the CEF and increment a table for every type
  $pubtype = ["BOOK" => array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0),
              "BOOKCHAPTER" => array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0),
              "PROCEEDINGS" => array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0),
              "ARTICLE" => array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0),
              "INPROCEEDINGS" => array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0),
              "ARTICLEWITHOUTCOMMITEE" => array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0),
              "TECHREPORT" => array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0),
              "ALL" => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]];

  foreach($res as $key => $entry)
  {
    $autCount = getCEFActualRegMemberCnt($entry);

    if ($autCount > 0)
    {
      $nbpub++;
      $autIndex = min($autCount, 5);

      if ($entry->entrytype == 'BOOK' and $autCount > 0)
      {
        $pubtype['BOOK'][$autIndex]++;
      }
      elseif ($entry->entrytype == 'INBOOK' or $entry->entrytype == 'INCOLLECTION')
      {
        $pubtype['BOOKCHAPTER'][$autIndex]++;
      }
      elseif ($entry->entrytype == 'PROCEEDINGS')
      {
        $pubtype['PROCEEDINGS'][$autIndex]++;
      }
      elseif ($entry->entrytype == 'ARTICLE' and strpos($entry->get('NOTE') , 'CEFRSC') === false)
      {
        $pubtype['ARTICLE'][$autIndex]++;
      }
      elseif ($entry->entrytype == 'INPROCEEDINGS')
      {
        $pubtype['INPROCEEDINGS'][$autIndex]++;
      }
      elseif ($entry->entrytype == 'ARTICLE')
      {
        $pubtype['ARTICLEWITHOUTCOMMITEE'][$autIndex]++;
      }
      else // it must be a TECHREPORT
      {
        $pubtype['TECHREPORT'][$autIndex]++;
      }
      $pubtype['ALL'][$autIndex]++;
    }
  }

  foreach (array_keys($pubtype) as $key)
  {
    $sum[$key] = max(array_sum($pubtype[$key]) , 1);
  }

  $tableinfo = [
    "BOOK" => [
      "name" => "Livres",
      "type" => "BOOK",
      "filter" => "(\$this->entrytype==='BOOK')"
    ],
    "BOOKCHAPTER" => [
      "name" => "Chapitres de livre",
      "type" => "INBOOK ou INCOLLECTION",
      "filter" => "(\$this->entrytype==='INBOOK' or \$this->entrytype==='INCOLLECTION')"
    ],
    "PROCEEDINGS" => [
      "name" => "Actes de colloque",
      "type" => "PROCEEDINGS",
      "filter" => "(\$this->entrytype==='PROCEEDINGS')"
    ],
    "ARTICLE" => [
      "name" => "Articles de revues avec comité de lecture",
      "type" => "ARTICLE sans \"CEFRSC\" dans le champ NOTE",
      "filter" => "(\$this->entrytype==='ARTICLE' and strpos(\$this->get('NOTE'),'CEFRSC')===FALSE)"
    ],
    "INPROCEEDINGS" => [
      "name" => "Articles d'actes de colloque",
      "type" => "INPROCEEDINGS",
      "filter" => "(\$this->entrytype==='INPROCEEDINGS')"
    ],
    "ARTICLEWITHOUTCOMMITEE" => [
      "name" => "Articles de revues sans comité de lecture",
      "type" => "ARTICLE avec \"CEFRSC\" dans le champ NOTE",
      "filter" => "(\$this->entrytype==='ARTICLE' and strpos(\$this->get('NOTE'),'CEFRSC')!==FALSE)"
    ],
    "TECHREPORT" => [
      "name" => "Rapports scientifiques",
      "type" => "TECHREPORT",
      "filter" => "(\$this->entrytype==='TECHREPORT')"
    ],
    "ALL" => [
      "name" => "Toutes publications confondus",
      "type" => "autres que MASTERSTHESIS et PHDTHESIS",
      "filter" => "(\$this->entrytype!=='MASTERSTHESIS' and \$this->entrytype!=='PHDTHESIS')"
    ]
  ];

  $ret = "%red%'''" . $nbpub . " publications" . $yeartext . "'''%%.

----

";
  $ret .= "(:table class=\"zebra\":)
(:cellnr rowspan=2 valign=middle:)%red%'''Type de publication'''
(:cell align=center valign=middle colspan=5:)%red%'''Nombre de publication ayant...'''
(:cellnr align=center valign=top:)%red%'''...1 auteur\\\\
membre régulier du CEF'''
(:cell align=center valign=top:)%red%'''...2 auteurs\\\\
membres réguliers du CEF'''
(:cell align=center valign=top:)%red%'''...3 auteurs\\\\
membres réguliers du CEF'''
(:cell align=center valign=top:)%red%'''...4 auteurs\\\\
membres réguliers du CEF'''
(:cell align=center valign=top:)%red%'''...plus de 4 auteurs\\\\
membres réguliers du CEF'''
";

  foreach (array_keys($tableinfo) as $key)
  {
    $ret .= "(:cellnr:)'''" . $tableinfo[$key]['name'] . "''' (type " . $tableinfo[$key]['type'] . ")
";
    for ($i = 1;$i <= 5;$i++)
    {
      $title = $tableinfo[$key]['name'];
      if ($yeartext != "")
      {
        $title .= " publié" . ($key == "ALL" ? "e" : "") . "s" . $yeartext . " et";
      }
      $title .= " ayant " . ($i === 5 ? "plus de 4" : $i) . " auteur" . ($i === 1 ? "" : "s") . " membre" . ($i === 1 ? "" : "s") . " régulier" . ($i === 1 ? "" : "s") . " du CEF";

      $URL = "Membres.NombreAuteurParPublication?action=bibtexquery&bibfilepage=Membres.PublicationsPageListe";
      $URL .= "&title=" . urlencode($title);
      $URL .= "&filter=" . urlencode($tableinfo[$key]['filter']) . urlencode(" and getCEFActualRegMemberCnt(\$this) " . ($i === 5 ? ">=" : "==") . " " . $i);
      $URL .= urlencode($yearcond);
      $URL .= "&sort=" . urlencode("!\$this->get('YEAR')");

      //error_log("xxx ".$URL."\n", 3, "c:/temp/my-errors.log");
      $ret .= "(:cell align=center valign=middle:)";

      if ($pubtype[$key][$i] > 0)
      {
        $ret .= "%newwin%[[" . $URL . "|";
      }
      $ret .= "'''" . $pubtype[$key][$i] . "'''&nbsp;(" . sprintf("%01.1f", 100 * $pubtype[$key][$i] / $sum[$key]) . "%)";
      if ($pubtype[$key][$i] > 0)
      {
        $ret .= "]]";
        if ($key == "ALL")
        {
        $ret .= "\n%newwin%[[" . $URL . "&groupbytype=true|groupés par type]]";
        }
      }
      $ret .= "
";
    }
  }

  $ret .= "
(:tableend:)";
  //error_log("yyy ".$ret."\n", 3, "c:/temp/my-errors.log");
  return $ret;
}

######################################
#
# Number of publications per regular member
#
######################################
function PubStat3($m)
{
  global $HTTPHeaders, $PageStartFmt, $PageEndFmt, $bibfile;
  extract($GLOBALS['MarkupToHTML'] ?? []);

  //error_log("AAAA opt=".$opt."\n", 3, "c:/temp/my-errors.log");
  $opt = ParseArgs($m[1]);
  $action = trim($_GET['action'] ?? $opt['action'] ?? "");  
  $bibfile = trim($_GET['bibfile'] ?? $opt['bibfile'] ?? "");
  $bibfilepage = trim($_GET['bibfilepage'] ?? $opt['bibfilepage'] ?? "");
  $file = trim($_GET['file'] ?? $opt['file'] ?? "");
  $format = trim($_GET['format'] ?? $opt['format'] ?? "");
  $sort = str_replace("\\'", "'", trim($_GET['statsort'] ?? $opt['statsort'] ?? ""));
  $yearbeg = trim($_GET['an1'] ?? $opt['an1'] ?? "");
  $yearend = trim($_GET['an2'] ?? $opt['an2'] ?? "");
  $issnandimpactpage = trim($_GET['issnandimpactpage'] ?? $opt['issnandimpactpage'] ?? "");

  if ((int)$yearbeg === 0)
  {
    $yearbeg = "";
  }

  if ((int)$yearend === 0)
  {
    $yearend = "";
  }

  // if bibfilepage is provided get the list of bibfile from a wiki page
  if ($bibfilepage != "")
  {
    // check if bibfilepage file exist
    $bibfilepagename = MakePageName($pagename, $bibfilepage);
    if (!PageExists($bibfilepagename))
    {
      SDV($HandleBibtexFmt, array(&$PageStartFmt,
        MarkupToHTML($pagename, "%red%Invalid BibTeX File Page (" . $bibfilepagename . " does not exists)!") , &$PageEndFmt
      ));
      PrintFmt($pagename, $HandleBibtexFmt);
      return;
    }
    // read the bibfilepage file
    $bibfile = ReadPage($bibfilepagename, READPAGE_CURRENT);
    $bibfile = trim($bibfile['text']);
  }

  if ($yearend == "")
  {
    $yearend = $yearbeg;
  }
  if ($yearbeg == "")
  {
    $yearbeg = $yearend;
  }

  $yearcond = "";
  $yeartext = "";
  if ($yearbeg != "" and $yearend != "")
  {
    $yearcond = " and \$this->get('YEAR')>='" . $yearbeg . "' and \$this->get('YEAR')<='" . $yearend . "'";
    if ($yearbeg != $yearend)
    {
      $yeartext = " %red%'''entre " . $yearbeg . " et " . $yearend . "'''%% inclusivement";
    }
    else
    {
      $yeartext = " %red%'''en " . $yearbeg . "'''%%";
    }
  }

  $cond = "\$this->entrytype=='ARTICLE' and strpos(\$this->get('NOTE'),'CEFRSC')===FALSE" . $yearcond;

  $ret = '';

  $res = [];
  $res = BibCreateSelection($bibfile, $cond, $sort, "");

  if (!is_array($res))
  {
    SDV($HandleBibtexFmt, array(&$PageStartFmt,
      MarkupToHTML($pagename, $res) , &$PageEndFmt
    ));
    PrintFmt($pagename, $HandleBibtexFmt);
    return;
  }

  $nbpub = count($res);

  if ($nbpub == 0)
  {
    SDV($HandleBibtexFmt, array(&$PageStartFmt,
      MarkupToHTML($pagename, "Aucune") , &$PageEndFmt
    ));
    PrintFmt($pagename, $HandleBibtexFmt);
    return;
  }

  $membersAuthorships = [];
  $membersFirstAuthorships = [];

  foreach($res as $key => $entry)
  {
    // Get the list of author as an array
    $autCount = 0;
    $authors = $entry->getAuthorsArray();
    foreach ($authors as $author)
    {
      if (($member = getCEFMember($author, $entry->entryname)) !== false &&
          ($member['Titre'] == "Chercheur émérite" ||
           $member['Titre'] == "Chercheur régulier" ||
           ($member['Titre'] == "AncienChercheur régulier" && 
            $entry->get("YEAR") <= $member['Fin'])
          )
         )
      {
        $fname = $member['Nom'] . ", " . $member['Prenom'];
        if (!array_key_exists($fname, $membersAuthorships))
        {
          $membersAuthorships[$fname] = 0;
        }
        $membersAuthorships[$fname]++;

        if ($autCount == 0)
        {
          if (!array_key_exists($fname, $membersFirstAuthorships))
          {
            $membersFirstAuthorships[$fname] = 0;
          }
          $membersFirstAuthorships[$fname]++;
        }
        elseif (!array_key_exists($fname, $membersFirstAuthorships))
        {
          $membersFirstAuthorships[$fname] = 0;
        }
      }
      $autCount++;
    }
  }

  $nbAuthorship = array_sum($membersAuthorships);
  $nbFirstAuthorship = array_sum($membersFirstAuthorships);

  if ($sort == "")
  {
    arsort($membersAuthorships);
  }

  if (strtolower($action) == "pubstat3" and $file == 1 or strtolower($format) == "csv")
  {
    $ret = "Chercheur;NbPub;PctPub;NbFirstPub;PctFirstPub;PctFirstOwnPub;\n";
    foreach($membersAuthorships as $key => $value)
    {
      $ret .= $key . ";" . $value . ";" . sprintf("%01.1f", 100 * $value / $nbAuthorship) . ";" . $membersFirstAuthorships[$key] . ";" . sprintf("%01.1f", 100 * $membersFirstAuthorships[$key] / $nbFirstAuthorship) . ";" . sprintf("%01.1f", 100 * $membersFirstAuthorships[$key] / $value) . ";\n";
    }

    foreach ($HTTPHeaders as $h)
    {

      $h = preg_replace('!^Content-type:\\s+text/html!i', 'Content-type: application/ms-excel', $h);
      $h = preg_replace('!no-store!i', 'post-check=0', $h);
      $h = preg_replace('!no-cache!i', 'pre-check=0', $h);
      header($h);
    }
    header('Content-Disposition: attachment; filename="resultat.csv"');
    
    echo (chr(0xEF).chr(0xBB).chr(0xBF)); // send BOM before so the file open fine in Excel
    echo (@$ret);
  }
  else
  {
    $ret = "Il y a %red%'''" . $nbAuthorship . " auteurs'''%% pour %red%'''" . $nbpub . " articles'''%%" . $yeartext . ". Les membres du CEF sont premiers auteurs de %red%'''" . $nbFirstAuthorship . " de ces articles'''%%.

----

";

    $ret .= "(:table class=\"zebra sortable\":)
(:cellnr valign=top:)%red%'''Chercheur régulier'''
(:cell align=center valign=top:)%red%'''Nombre de mention\\\\
à titre d'auteur'''
(:cell align=center valign=top:)%red%'''Pourcentage de toutes les mentions (n=" . $nbAuthorship . ")'''
(:cell align=center valign=top:)%red%'''Nombre d'article\\\\
premier auteur'''
(:cell align=center valign=top:)%red%'''Pourcentage de tous les articles premier auteur (n=" . $nbFirstAuthorship . ")'''
(:cell align=center valign=top:)%red%'''Pourcentage de ses propres articles comme premier auteur'''
";
    foreach($membersAuthorships as $key => $value)
    {
      $name = explode(",", $key);
      $fName = $name[1];
      $name = $name[0];

      $ret .= "(:cellnr:)[-'''[[(:simplestr " . $fName . $name . ":)|" . $key . "]]'''-]
(:cell align=center:)[-'''" . $value . "'''-]
(:cell align=center:)[-" . sprintf("%01.1f", 100 * $value / $nbAuthorship) . "-]
(:cell align=center:)[-'''" . $membersFirstAuthorships[$key] . "'''-]
(:cell align=center:)[-" . sprintf("%01.1f", 100 * $membersFirstAuthorships[$key] / $nbFirstAuthorship) . "-]\n
(:cell align=center:)[-" . sprintf("%01.1f", 100 * $membersFirstAuthorships[$key] / $value) . "-]\n";

    }

    $ret .= "(:tableend:)";

    if (strtolower($action) == "pubstat3")
    {
      $ret = "!!'''Quel est le nombre d'article publié par membre régulier?'''

Cette page présente le nombre d'article publiés par membres du CEF. Seul les articles proprement dit se retrouvent dans cette compilation. Sont donc exclus les livres, chapitres de livre, mémoires, thèses, etc...


" . $ret;

      SDV($HandleBibtexFmt, array(&$PageStartFmt,
        MarkupToHTML($pagename, $ret) , &$PageEndFmt
      ));
      PrintFmt($pagename, $HandleBibtexFmt);
    }
    else
    {
      return $ret;
    }
  }
}
###############################################
global $pubNameMemberMap, $nameMemberMap;

$pubNameSynonyms = [];
// This list is for when it is not possible to construct the author name from the name in MembreData
// The index is the synomym (the right author name. There should not duplicate but there can be many) and the
// assigned string is what is construct from MembreData by the pubNameFromNomprenom function
$pubNameSynonyms['Kneeshaw, D.D.'] = "Kneeshaw, D.";
$pubNameSynonyms['Munson, A.D.'] = "Munson, A.";
$pubNameSynonyms['Khasa, D.P.'] = "Khasa, D.";
$pubNameSynonyms['Namroud, M.C.'] = "Namroud, M.-C.";
$pubNameSynonyms['Coyea, M.R.'] = "Coyea, M.";
$pubNameSynonyms['McIntire, E.J.B.'] = "McIntire, E.";
$pubNameSynonyms['Greene, D.'] = "Greene, D.F.";
$pubNameSynonyms['Buddle, C.M.'] = "Buddle, C.";
$pubNameSynonyms['MacKay, J.J.'] = "MacKay, J.";
$pubNameSynonyms['Ruel, J.C.'] = "Ruel, J.-C.";
$pubNameSynonyms['Work, T.T.'] = "Work, T.";
$pubNameSynonyms['Fournier, R.A.'] = "Fournier, R.";
$pubNameSynonyms['Kembel, S.W.'] = "Kembel, S.";
$pubNameSynonyms['Cumming, S.'] = "Cumming, S.G.";
$pubNameSynonyms['Handa, I.T.'] = "Handa, T.";
//$pubNameSynonyms['Bradley, R.L.'] = "Bradley, R.";
$pubNameSynonyms['Harvey, B.D.'] = "Harvey, B.";
$pubNameSynonyms['James, P.M.A.'] = "James, P.";
$pubNameSynonyms['Tremblay, M.F.'] = "Tremblay, F.";
$pubNameSynonyms['Chapman, C.A.'] = "Chapman, C.";
$pubNameSynonyms['Fenton, N.J.'] = "Fenton, N.";
$pubNameSynonyms['Ziter, C.D.'] = "Ziter, C.";
$pubNameSynonyms['Sivarajah, S.'] = "Sivarajah, J.";
$pubNameSynonyms['Mazerolle, M.J.'] = "Mazerolle, M.";
//$pubNameSynonyms['Villarreal, J.C.'] = "Villarreal Aguilar, J.C.";
$pubNameSynonyms['Guittonny, M.'] = "Guittonny, M.";
$pubNameSynonyms['Guittonny-Larcheveque, M.'] = "Guittonny, M.";

function getCEFActualRegMemberCnt($entry, $editorOnly = false)
{
  $autCount = 0;
  $authors = $entry->getEditorsArray();
  if (!$editorOnly)
  {
    $authors = array_merge($entry->getAuthorsArray() , $authors);
  }
  foreach ($authors as $author)
  {
    if (($member = getCEFMember($author, $entry->entryname)) !== false and
        ($member['Titre'] == "Chercheur émérite" or
         $member['Titre'] == "Chercheur régulier" or
         ($member['Titre'] == "AncienChercheur régulier" and $entry->get("YEAR") <= $member['Fin'])))
    {
      $autCount++;
    }
  }
  return $autCount;
}

// check that an author (or an editor) is still listed as a regular member
function getCEFMember($author, $entrykey)
{
  global $parsedCSV, $pubNameMemberMap, $pubNameSynonyms, $nameMemberMap;

  $titleRank = [
    "Chercheur émérite" => 20,
    "Chercheur régulier" => 19,
    "AncienChercheur régulier" => 18,
    "Chercheur associé" => 17,
    "Professionnel" => 16,
    "Professionnel CEF" => 15,
    "Technicien" => 14,
    "Postdoctorat" => 13,
    "Doctorat" => 12,
    "Maîtrise" => 11,
    "Stagiaire" => 10,
    "Baccalauréat" => 9,
    "AncienChercheur associé" => 8,
    "AncienProfessionnel" => 7,
    "AncienProfessionnel CEF" => 6,
    "AncienTechnicien" => 5,
    "AncienPostdoctorat" => 4,
    "AncienDoctorat" => 3,
    "AncienMaîtrise" => 2,
    "AncienStagiaire" => 1,
    "AncienBaccalauréat" => 0
  ];
  // Get the list of member
  $memredatapagename = "Membres.MembreData";
  if (!PageExists($memredatapagename))
  {
    return "%red%Membres.MembreData n'existe pas!";
  }

  // Parse the CSV content of the page if it was not already parsed
  if (!array_key_exists($memredatapagename, $parsedCSV))
  {
    # Read the source file
    $sourceCSV = ReadPage($memredatapagename, READPAGE_CURRENT);
    $sourceCSV = trim($sourceCSV['text']) . "\n";

    $parsedCSV[$memredatapagename] = CSVTemplateParseCVSContent($sourceCSV, true, ",");
  }

  // If $pubNameMemberMap was not already created
  // pubNameMemberMap map a computed name of the style "Racine, P." with the corresponding entry in the member table
  if (is_null($pubNameMemberMap))
  {
    $pubNameMemberMap = [];
    // for each line in the member structure, associate a computed name
    foreach ($parsedCSV[$memredatapagename] as $member)
    {
      $key = pubNameFromNomprenom($member['Nom'], $member['Prenom']);

      if (!array_key_exists($key, $pubNameMemberMap) || 
          (($member['Titre'] ?? false) &&
           ($titleRank[$member['Titre']] ?? false) &&
           ($pubNameMemberMap[$key] ?? false) &&
           ($titleRank[$pubNameMemberMap[$key]['Titre']] ?? false) &&
           $titleRank[$member['Titre']] > $titleRank[$pubNameMemberMap[$key]['Titre']]))
      {
        $pubNameMemberMap[$key] = $member;

        // Create a second list with only the first initial so we can later identify possible synonyms
        $nameMemberMap[removeAccent($member['Nom'] . ", " . $member['Prenom'][0] . ".") ] = $member;
      }
    }

    // Add the possible synonyms
    foreach ($pubNameSynonyms as $synonym => $entryKey)
    {
      if (array_key_exists($entryKey, $pubNameMemberMap)){
        $pubNameMemberMap[$synonym] = $pubNameMemberMap[$entryKey];
      }
    }
  }

  // If the author is not found in the CEF author list
  if (!array_key_exists(removeAccent($author), $pubNameMemberMap))
  {
    //$authName = removeAccent(substr($author, 0, strpos($author, ".") + 1));
    //  error_log("authName=".$authName."\n", 3, "c:/temp/my-errors.log");
    //if (!is_null($nameMemberMap[$authName]) and $nameMemberMap[$authName]['Titre'] == "Chercheur régulier")
    //   error_log($entrykey." In pub=".$author." computed key=".pubNameFromNomprenom($nameMemberMap[$authName]['Nom'], $nameMemberMap[$authName]['Prenom'])."\n", 3, "c:/temp/my-errors.log");
    return false;
  }
  return $pubNameMemberMap[removeAccent($author) ];
}

function removeAccent($str)
{
  return strtr($str, array('é'=>'e', 'É'=>'E', 'è'=>'e', 'È'=>'E', 'ê'=>'e', 'Ê'=>'E', 'ë'=>'e', 'Ë'=>'E', 'à'=>'a', 'À'=>'A', 'ç'=>'c', 'Ç'=>'C', 'â'=>'a', 'Â'=>'A', 'ä'=>'a', 'Ä'=>'A', 'Ä'=>'A', 'ö'=>'o', 'Ö'=>'O', 'ô'=>'o', 'Ô'=>'O','ì'=>'i', 'Ì '=>'I', 'î'=>'i', 'Î'=>'I', 'ï'=>'i', 'Ï'=>'I'));
}

function pubNameFromNomprenom($name, $fname)
{
  $fnames = [];
  $glue = "";
  if (strpos($fname, " ") !== false)
  {
    $fnames = explode(" ", $fname);
  }
  elseif (strpos($fname, "-") !== false)
  {
    $fnames = explode("-", $fname);
    $glue = "-";
  }
  else
  {
    $fnames[] = $fname;
  }

  $result = $name . ", ";
  foreach ($fnames as $fn)
  {
    $result .= ($fn[0] ?? "") . "." . $glue;
  }

  if ($glue != "")
  {
    $result = substr($result, 0, strlen($result) - 1);
  }

  return removeAccent($result);
}

