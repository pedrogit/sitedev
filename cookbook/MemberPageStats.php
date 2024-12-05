<?php if (!defined('PmWiki')) exit();

SDV($HandleActions['memberpagestat'],'HandleMemberPageStat');

function HandleMemberPageStat($pagename, $opt)
{
    global $parsedCSV, $HTTPHeaders;

    $sourcepage = "Membres.MembreData";
    $AuthorGroup = "Membres";
    $header = true;

    $sourcepage = MakePageName($pagename, $sourcepage);
    if (!PageExists($sourcepage))
        return "%red%Membres.MembreData n'existe pas!";

    if (!$parsedCSV[$sourcepage])
    {
        # Read the source file
        $sourceCSV = ReadPage($sourcepage, READPAGE_CURRENT);
        $sourceCSV = $sourceCSV['text'];

        # Parse the CSV content if it was not already
        $parsedCSV = CSVTemplateParseCVSContent($sourceCSV, $header, ",");
    }

    # Create the output CSV file
    $ret = "\"Titre\";\"Prenom\";\"Nom\";\"Directeur\";\"CoDirecteur\";\"Institution\";\"Courriel\";Fin;PageExist;\n";

    foreach ($parsedCSV as $rowvalues)
    {
        # Create the name of the member page
        $targetname = $AuthorGroup.".".simplestr($rowvalues['Prenom'].$rowvalues['Nom']);
        $targetname = MakePageName($pagename, $targetname);

        $ret .= "\"".$rowvalues['Titre']."\";\"".$rowvalues['Prenom']."\";\"".$rowvalues['Nom']."\";\"".$rowvalues['Directeur']."\";\"".$rowvalues['CoDirecteur']."\";\"".$rowvalues['Institution']."\";\"".$rowvalues['Courriel']."\";".$rowvalues['Fin'].";".(PageExists($targetname) ? '1' : '0').";\n";
    }


    foreach ($HTTPHeaders as $h)
    {

      $h = preg_replace('!^Content-type:\\s+text/html!i', 'Content-type: application/ms-excel', $h);
      $h = preg_replace('!no-store!i', 'post-check=0', $h);
      $h = preg_replace('!no-cache!i', 'pre-check=0', $h);
      header($h);
    }
    header('Content-Disposition: attachment; filename="pagememberexist.csv"');
    echo (chr(0xEF).chr(0xBB).chr(0xBF)); // send BOM before so the file open fine in Excel
    echo(@$ret);
}

