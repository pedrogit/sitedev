<?php if (!defined('PmWiki')) exit();
###########################################################
##
## Center for forest research (CFR) custom PmWiki markup.
##
###########################################################

XLSDV('en', [
  'personnel' => 'Personnel',
  'ancienpersonnel' => 'Former Personnel',
  'directionetudiants' => 'Graduate Student Advisor',
  'codirectionetudiants' => 'Grad. Student Co-Advisor',
  'codirections' => 'Co-Advisor',
  'etudiantsdiplomes' => 'Former Students',
  'professionels' => 'Professionals',
  'techniciens' => 'Technicians',
  'stagiaires' => 'Interns',
  'postdocs' => 'Postdocs',
  'doctorats' => 'Doctorates',
  'maitrises' => 'Master\'s',
  'diriges' => 'Director',
  'codiriges' => 'Co-Director'
  ]);

XLSDV('fr',[
  'personnel' => 'Personnel',
  'ancienpersonnel' => 'Ancien personnel',
  'directionetudiants' => 'Directions d\'&eacute;tudiants',
  'codirectionetudiants' => 'Codirections d\'&eacute;tudiants',
  'codirections' => 'Codirections',
  'etudiantsdiplomes' => '&Eacute;tudiants dipl&#244;m&eacute;s',
  'professionels' => 'Professionnels',
  'techniciens' => 'Techniciens',
  'stagiaires' => 'Stagiaires',
  'postdocs' => 'Postdoctorats',
  'doctorats' => 'Doctorats',
  'maitrises' => 'Ma&#238;trises',
  'diriges' => 'Dirig&eacute;s',
  'codiriges' => 'Codirig&eacute;s'
  ]);


###########################################################
## personnelbox: display a yellow box featuring the personnal associated with a CEF researcher.
## By default personnel (postdocs, professionnels, technicien), direction, codirection, ancienpersonnel, diplome are all displayed.
## To remove an item, spedify it following the director name
##
## (:personnelbox dir="MacKay John" personnel direction diplome ancienpersonnel :) display only the codirection box
##
###########################################################
Markup('personnelbox','<if','/\(:personnelbox\\s+(.*?)\\s?:\)/', "PersonnelBox");

function PersonnelBox($m)
{
  extract($GLOBALS['MarkupToHTML']);
  $opt = ParseArgs($m[1]);
  $dir = $opt['dir'];

  $options = $opt[''] ?? array();

  $horizontal = @in_array("horizontal", $options);
  $personnel = @!in_array("personnel", $options);
  $direction = @!in_array("direction", $options);
  $codirection = @!in_array("codirection", $options);
  $ancienpersonnel = @!in_array("ancienpersonnel", $options);
  $ancienstagiaire = @!in_array("ancienstagiaire", $options);
  $ancientechnicien = @!in_array("ancientechnicien", $options);
  $diplome = @!in_array("diplome", $options);

  if ($horizontal) {
    $firstdiv = "<div class=personnalboxh>";
    $table = "";
    $tableend = "";
    $cell = "";
    $template1 = "etubox3";
    $template2 = "etubox4";
    $div = "<div>";
    $divend = "</div>";
    $separator = "
\\\\

\\\\
";
  }
  else {
    $firstdiv = "(:pagemenu style=\"width:180px; background-color:#e8f4dd;border-left: 3px solid #990000;\":)
";
    $table = "(:table border=0 align=center width=100%:)";
    $tableend = "(:tableend:)";
    $cell = "(:cellnr align=center colspan=2 class=sectioncell style=\"color: #990000;\":)";
    $template1 = "etubox1";
    $template2 = "etubox2";
    $div = "(:div1 class=personnalbox:)";
    $divend = "(:div1end:)";
    $separator = "----";
  }

  $ret = $firstdiv;

  if ($personnel or $direction or $codirection)
    $ret .= $div."
".$table."
";

  if ($personnel)
    $ret .= $cell."$[personnel]
(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template1." header filter=\"(strpos({Directeur},'".$dir."')!==FALSE or strpos({CoDirecteur},'".$dir."')!==FALSE) and {Titre}=='Postdoctorat'\" sort=Nom categorie=$[postdocs] sep=\",\":)
(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template1." header filter=\"(strpos({Directeur},'".$dir."')!==FALSE or strpos({CoDirecteur},'".$dir."')!==FALSE) and {Titre}=='Professionnel'\" sort=Nom categorie=$[professionels] sep=\",\":)
(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template1." header filter=\"(strpos({Directeur},'".$dir."')!==FALSE or strpos({CoDirecteur},'".$dir."')!==FALSE) and {Titre}=='Technicien'\" sort=Nom categorie=$[techniciens] sep=\",\":)
(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template1." header filter=\"(strpos({Directeur},'".$dir."')!==FALSE or strpos({CoDirecteur},'".$dir."')!==FALSE) and {Titre}=='Stagiaire'\" sort=Nom categorie=$[stagiaires] sep=\",\":)
";

  if ($direction)
  {
    if ($ret != "")
      $ret .= $cell."
";
    if ($personnel)
      $ret .= "
".$separator."
";
    $ret .= "$[directionetudiants]
(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template1." header filter=\"strpos({Directeur},'".$dir."')!==FALSE and {Titre}=='Doctorat'\" sort=Nom categorie=$[doctorats] sep=\",\":)
(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template1." header filter=\"strpos({Directeur},'".$dir."')!==FALSE and {Titre}=='Maîtrise'\" sort=Nom categorie=$[maitrises] sep=\",\":)
";

  };

  if ($codirection)
  {
    if ($ret != "")
      $ret .= $cell."
";
    if ($personnel or $direction)
      $ret .= "
".$separator."
";
    if ($direction)
    {
      $ret .= "$[codirections]
";
    }
    else
    {
      $ret .= "$[codirectionetudiants]
";
    }

      $ret .= "(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template1." header filter=\"strpos({CoDirecteur},'".$dir."')!==FALSE and {Titre}=='Doctorat'\" sort=Nom categorie=$[doctorats] sep=\",\":)
(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template1." header filter=\"strpos({CoDirecteur},'".$dir."')!==FALSE and {Titre}=='Maîtrise'\" sort=Nom categorie=$[maitrises] sep=\",\":)";
  };

  if ($personnel or $direction or $codirection)
      $ret .= $tableend."
".$divend."
";

  if ($ancienpersonnel or $diplome)
      $ret .= $div."
".$table."
";
  if ($ancienpersonnel and ($personnel or $direction or $codirection))
      $ret .= "
".$separator."
";

  if ($ancienpersonnel)
      $ret .= $cell."'''$[ancienpersonnel]'''
(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template2." header filter=\"(strpos({Directeur},'".$dir."')!==FALSE or strpos({CoDirecteur},'".$dir."')!==FALSE) and ({Titre}=='AncienPostdoctorat' or {Titre}=='AncienProfessionnel'";

  if ($ancienpersonnel and $ancienstagiaire)
      $ret .= " or {Titre}=='AncienStagiaire'";

  if ($ancienpersonnel and $ancientechnicien)
      $ret .= " or {Titre}=='AncienTechnicien'";

  if ($ancienpersonnel)
      $ret .= ")\" sort=-Fin sep=\",\":)
";

  if ($diplome)
  {
      $ret .= $cell."
";

      if ($ancienpersonnel or $direction or $codirection)
        $ret .= "
".$separator."
";

      $ret .= $cell."'''$[etudiantsdiplomes]'''
(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template2." header filter=\"strpos({Directeur},'".$dir."')!==FALSE and ({Titre}=='AncienDoctorat' or {Titre}=='AncienMaîtrise')\" sort=-Fin categorie=$[diriges] sep=\",\":)
(:csvtemplate source=Membres.MembreData template=Membres.MembreTemplate#".$template2." header filter=\"strpos({CoDirecteur},'".$dir."')!==FALSE and ({Titre}=='AncienDoctorat' or {Titre}=='AncienMaîtrise')\" sort=-Fin categorie=$[codiriges] sep=\",\":)
";
  };
  if ($ancienpersonnel or $diplome)
      $ret .= $tableend."
".$divend."
";

  if ($horizontal) {
    $ret .= $divend."
";
  }
  else {
    $ret .= "(:endpagemenu:)
";
  }

//error_log("fct 111 ret=".$ret."\n", 3, "c:/temp/my-errors.log");

  return $ret;
}

###########################################################
## suite and newwinsuite: create an hyperlink that display "Lire la suite" in red italic
## (:lire Actualité/Enmanchette:)
## (:newwinlire http://www.google.com:)
###########################################################
Markup('liremanchette','<if','/\(:liremanchette\\s*:\)/',
"%red%''([[Actualité/LeBlogueDuCEF| Lire la suite]]...)''%%");

Markup('lire','<if','/\(:lire\\s+(.*?)\\s?:\)/',
"%red%''([[".PSS('$1')."| Lire la suite]]...)''%%");

Markup('newwinlire','<if','/\(:newwinlire\\s+(.*?)\\s?:\)/',
"%newwin red%''([[".PSS('$1')."| Lire la suite]]...)''%%");

Markup('newwinecouter','<if','/\(:newwinecouter\\s+(.*?)\\s?:\)/',
"%newwin red%''([[".PSS('$1')."| Écouter le reportage]]...)''%%");

###########################################################
## leaf: display a leaf
## (:leaf:)
###########################################################
Markup('leaf','<if','/\(:leaf\\s*:\)/',
"Attach:CEF/feuille.gif");


###########################################################
## publicationlist: display all kind of publication for a CEF researcher.
## (:publicationlist author="Messier, C." pagelist="ChristianMessierPub, JacquesBrissonPub, DanielGagnonPub, JeanClaudeRuelPub, HubertMorinPub, SuzanneBraisPub, DavidFGreenePub, DanielKneeshawPub, MartinJLechowiczPub, AlainLeducPub, YvesMauffettePub":)
##
###########################################################
XLSDV('en', [
  'books' => 'Books',
  'bookchapters' => 'Book chapters',
  'bookeditor' => 'Edited books, special journal editions and proceedings',
  'articlewithcommittee' => 'Peer-reviewed articles',
  'articlewithoutcommittee' => 'Non peer-reviewed articles',
  'inproceedings' => 'Articles published in proceedings',
  'techreport' => 'Scientific reports, manuals and others',
  'thesis' => 'Theses, dissertations and essays',
  'supervisedthesis' => 'Supervised theses, dissertations and essays'
  ]);

XLSDV('fr', [
  'books' => 'Livres',
  'bookchapters' => 'Chapitres de livre',
  'bookeditor' => 'Livres, num&eacute;ros sp&eacute;ciaux et actes de colloques publi&eacute;s à titre d\'&eacute;diteur',
  'articlewithcommittee' => 'Articles r&eacute;vis&eacute;s par un comit&eacute; de lecture',
  'articlewithoutcommittee' => 'Articles non r&eacute;vis&eacute;s par un comit&eacute; de lecture',
  'inproceedings' => 'Articles publi&eacute;s dans des actes de colloque (proceedings)',
  'techreport' => 'Rapports scientifiques, manuels et autres',
  'thesis' => 'Thèses, m&eacute;moires et essais',
  'supervisedthesis' => 'Thèses, m&eacute;moires et essais supervis&eacute;s'
  ]);

Markup('publicationlist','<bibtexquery','/\(:publicationlist\\s+(\\S.*?)\\s?:\)/',"PublicationList");
function PublicationList($m)
{
  //$x = eval("return 'Idrobo';");
  //$x = eval("return in_array('AA', array('AA', 'BB', 'CC'));");
  //error_log("PublicationList AA x:".$x."\n", 3, "c:/temp/my-errors.log");
  extract($GLOBALS['MarkupToHTML']);

  $opt = ParseArgs($m[1]);
  $author = $opt['author'];
  $authcond = "(";
  $thesisauthcond = "(";
  $bookauthcond = "(";
  $editorcond = "(";
  $notecond = "(";

  if ($author != "")
  {
    $aut_array = explode('||', $author);
    foreach ($aut_array as $aut)
    {
      $aut = trim($aut ?? "");
      $authcond = $authcond."in_array('$aut', \$this->getAuthorsArray()) or ";
      $thesisauthcond = $thesisauthcond."in_array('$aut', \$this->getAuthorsArray()) or ";
      $bookauthcond = $bookauthcond."(in_array('$aut', \$this->getAuthorsArray()) and ! in_array('$aut', \$this->getEditorsArray())) or ";
      $editorcond = $editorcond."in_array('$aut', \$this->getEditorsArray()) or ";
      $notecond = $notecond."strpos(\$this->get('NOTE'),'$aut')!==FALSE or ";
    }
    $authcond = substr($authcond, 0, -4).')';
    $thesisauthcond = substr($thesisauthcond, 0, -4).')';
    $bookauthcond = substr($bookauthcond, 0, -4).')';
    $editorcond = substr($editorcond, 0, -4).')';
    $notecond = substr($notecond, 0, -4).')';
  }

  //error_log("PublicationList AA authcond:".$authcond."\n", 3, "c:/temp/my-errors.log");

  $pagelist = $opt['pagelist'];
  $ignorelinks = $opt['ignorelinks'] ?? "";
  if ($ignorelinks != "")
  {
  	$ignorelinks = "[".$ignorelinks."]";
  }
  $ret = "!!!!!'''$[books]'''
\\\\

bibtexquery:[$pagelist][".$bookauthcond." and \$this->entrytype == 'BOOK'][!\$this->get('YEAR')][100]$ignorelinks
\\\\

!!!!!'''$[bookchapters]'''
\\\\

bibtexquery:[$pagelist][".$authcond." and (\$this->entrytype == 'INBOOK' or \$this->entrytype == 'INCOLLECTION')][!\$this->get('YEAR')][100]$ignorelinks
\\\\

!!!!!'''$[bookeditor]'''
\\\\

bibtexquery:[$pagelist][".$editorcond." and (\$this->entrytype == 'BOOK' or \$this->entrytype == 'PROCEEDINGS')][!\$this->get('YEAR')][100]$ignorelinks
\\\\

!!!!!'''$[articlewithcommittee]'''
\\\\

bibtexquery:[$pagelist][".$authcond." and \$this->entrytype == 'ARTICLE' and strpos(\$this->get('NOTE'),'CEFRSC')===FALSE][!\$this->get('YEAR')][1000]$ignorelinks
\\\\

!!!!!'''$[inproceedings]'''
\\\\

bibtexquery:[$pagelist][".$authcond." and \$this->entrytype == 'INPROCEEDINGS'][!\$this->get('YEAR')][100]$ignorelinks
\\\\

!!!!!'''$[techreport]'''
\\\\

bibtexquery:[$pagelist][".$authcond."and (\$this->entrytype == 'TECHREPORT' or \$this->entrytype == 'MANUAL' or \$this->entrytype == 'MISC' or \$this->entrytype == 'BOOKLET')][!\$this->get('YEAR')][100]$ignorelinks
\\\\

!!!!!'''$[thesis]'''
\\\\

bibtexquery:[$pagelist][".$thesisauthcond." and (\$this->entrytype == 'PHDTHESIS' or \$this->entrytype == 'MASTERSTHESIS')][!\$this->get('YEAR')][150]$ignorelinks
\\\\

!!!!!'''$[supervisedthesis]'''
\\\\

bibtexquery:[$pagelist][".$notecond." and (\$this->entrytype == 'PHDTHESIS' or \$this->entrytype == 'MASTERSTHESIS') and strpos(\$this->get('NOTE'),'CEFTMS')!==FALSE][!\$this->get('YEAR')][150]$ignorelinks
\\\\

!!!!!'''$[articlewithoutcommittee]'''
\\\\

bibtexquery:[$pagelist][".$authcond." and \$this->entrytype == 'ARTICLE' and strpos(\$this->get('NOTE'),'CEFRSC')!==FALSE][!\$this->get('YEAR')][100]$ignorelinks
\\\\
";
//error_log("AA ret:".$ret."\n", 3, "c:/temp/my-errors.log");

  return $ret;
}

###########################################################
## pubfileurl: display a link producing a BibTeX file of every publication of a CEF researcher.
## (:pubfileurl author="Messier, C." pagelist="ChristianMessierPub, JacquesBrissonPub, DanielGagnonPub, JeanClaudeRuelPub, HubertMorinPub, SuzanneBraisPub, DavidFGreenePub, DanielKneeshawPub, MartinJLechowiczPub, AlainLeducPub, YvesMauffettePub" format=endnote:)
##
###########################################################
Markup('pubfileurl','<bibtexquery','/\(:pubfileurl\\s+(\\S.*?)\\s?:\)/',"PubFileURL");
function PubFileURL($m)
{
  extract($GLOBALS['MarkupToHTML']);
  $opt = ParseArgs($m[1]);
  $author = $opt['author'] ?? "";
  $authorfilter = "";
  if (isset($author) && $author !== "")
  {
  	$authorfilter = "&filter=strpos%28\$this->get%28'AUTHOR'%29,'$author'%29!==FALSE or %28strpos%28\$this->get%28'EDITOR'%29,'$author'%29!==FALSE and %28\$this->entrytype == 'BOOK' or \$this->entrytype == 'PROCEEDINGS'%29%29 or %28strpos%28\$this->get%28'NOTE'%29,'$author'%29!==FALSE and %28\$this->entrytype == 'PHDTHESIS' or \$this->entrytype == 'MASTERSTHESIS'%29 and strpos%28\$this->get%28'NOTE'%29,'CEFTMS'%29!==FALSE%29";
  }
  $pagelist = $opt['pagelist'] ?? "";
  $format = $opt['format'] ?? "";
  if ($format == "")
    $format = "bibtex";
  $ret = "{\$PageUrl}?action=bibtexquery&bibfile=$pagelist".$authorfilter."&format=$format";
//error_log("AA ret:".$ret."\n", 3, "c:/temp/my-errors.log");
  return $ret;
}


###########################################################
## pubimportbox: display a box with links to import references
## (:pubimportbox author="Messier, C." pagelist="ChristianMessierPub, JacquesBrissonPub, DanielGagnonPub, JeanClaudeRuelPub, HubertMorinPub, SuzanneBraisPub, DavidFGreenePub, DanielKneeshawPub, MartinJLechowiczPub, AlainLeducPub, YvesMauffettePub" format=endnote:)
##
###########################################################
XLSDV('fr',[
  'vouspouvez' => 'Vous pouvez t&eacute;l&eacute;charger toutes mes r&eacute;f&eacute;rences bibliographiques en format',
  'orinformat' => 'ou',
  'endformat' => ''
  ]);

XLSDV('en',[
  'vouspouvez' => 'You can download all my bibliography in the',
  'orinformat' => 'or',
  'endformat' => ' format'
  ]);

Markup('pubimportbox','<pubfileurl','/\(:pubimportbox\\s+(\\S.*?)\\s?:\)/',"PubImportBox");
function PubImportBox($m)
{
  extract($GLOBALS['MarkupToHTML']);
  $opt = ParseArgs($m[1]);
  $author = $opt['author'] ?? "";
  $pagelist = $opt['pagelist'] ?? "";
  $frqnt = $opt['frqnt'] ?? "";
  $authorfilter = "";
  if (isset($author) && $author !== "")
  {
  	$authorfilter = "author=\"$author\"";
  }
  $frqnt_fmt = "";
  if (isset($frqnt))
  {
  	$frqnt_fmt = ", [[(:pubfileurl $authorfilter pagelist=\"$pagelist\" format=frqnt:)|'''FRQNT''']] ";
  }
  $ret = "&gt;&gt;rgreybox center width=180px&lt;&lt;\n$[vouspouvez] [[(:pubfileurl $authorfilter pagelist=\"$pagelist\" format=bibtex:)|'''BibTeX''']], [[(:pubfileurl $authorfilter pagelist=\"$pagelist\" format=csv:)|'''BibTeX-CSV''']]".$frqnt_fmt." $[orinformat] [[(:pubfileurl $authorfilter pagelist=\"$pagelist\" format=endnote:)|'''EndNote''']]$[endformat]\n&gt;&gt;&lt;&lt;";
  return $ret;

}

###########################################################
## Simplestr: remove any special character (accent, space, hyphen) from a string.
## (:simplestr "Éric Côté-Doré":) becomes EricCoteDore
##
###########################################################
Markup('simplestr','<if','/\(:simplestr\\s+(.*?)\\s?:\)/',"simplestrm");
function simplestrm($m)
{
  return simplestr($m[1]);
}

function simplestr($m)
{
  $ret = strtr($m, array(')'=>'','('=>'','/'=>'','\''=>'',' '=>'', '-'=>'', '.'=>'', ','=>'', 'à'=>'a', 'À'=>'A', 'á'=>'a', 'Á'=>'A', 'â'=>'a', 'Â'=>'A', 'ä'=>'a', 'Ä'=>'A', 'Ä'=>'A', 'ç'=>'c', 'Ç'=>'C', 'é'=>'e', 'É'=>'E', 'è'=>'e', 'È'=>'E', 'ê'=>'e', 'Ê'=>'E', 'ë'=>'e', 'Ë'=>'E','ì'=>'i','í'=>'i', 'Ì '=>'I', 'î'=>'i', 'Î'=>'I', 'ï'=>'i', 'Ï'=>'I', 'ö'=>'o', 'Ö'=>'O', 'ô'=>'o', 'Ô'=>'O', 'ò'=>'o', 'Ò'=>'O', 'ó'=>'o', 'Ó'=>'O'));
  return $ret;
}

###########################################################
## Custom Markup Expressions
###########################################################
$MarkupExpr['simplestr'] = 'simplestr($args[0])';

$MarkupExpr['isint'] = 'myisint($args[0])';
function myisint($val) {
	return (is_numeric($val) && is_int((int)$val)) ? "true" : "false";
}

$MarkupExpr['empty'] = '(($args[0] == "" || $args[0] == " ") ? "true" : "false")';

###########################################################
## lienrapide: a list of rapid external links
## (:lienrapide planvachon:)
##
###########################################################
Markup('lien','directives','/\(:lien\\s+(.*?)\\s?:\)/',"lienrapide");
function lienrapide($m)
{
    $map = [];

    $map['vachon'] = array('http://www.ulaval.ca/Al/interne/plan/AlexandreVachon/reference.htm', 'Pavillon Vachon');
    $map['abitibi'] = array('http://www.ulaval.ca/Al/interne/plan/Abitibi/reference.htm', 'Pavillon Abitibi-Price');
    $map['laurentienne'] = array('http://www.ulaval.ca/Al/interne/plan/Laurentienne/reference.htm', 'Pavillon Laurentienne');
    $map['dekoninck'] = array('http://www.ulaval.ca/Al/interne/plan/DeKoninck/reference.htm', 'Pavillon DeKoninck');
    $map['envirotron'] = array('http://www.ulaval.ca/Al/interne/plan/DelEnvirotron/reference.htm', 'Envirotron');
    $map['pouliot'] = array('http://www.ulaval.ca/Al/interne/plan/AdrienPouliot/reference.htm', 'Pavillon Pouliot');
    $map['comtois'] = array('http://www.ulaval.ca/Al/interne/plan/ComtoisPaul/reference.htm', 'Pavillon Comtois');
    $map['casault'] = array('http://www.ulaval.ca/Al/interne/plan/Casault/reference.htm', 'Pavillon Casault');
    $map['marchand'] = array('http://www.ulaval.ca/Al/interne/plan/CharlesEMarchand/reference.htm', 'Pavillon Marchand');
    $map['pouliot'] = array('http://www.ulaval.ca/Al/interne/plan/AdrienPouliot/reference.htm', 'Pavillon Adrien-Pouliot');
    $map['desjardins'] = array('http://www.ulaval.ca/Al/interne/plan/Desjardins/reference.htm', 'Pavillon Desjardins');
    $map['envirotron'] = array('http://www.ulaval.ca/Al/interne/plan/DelEnvirotron/reference.htm', 'Envirotron');


    if ($m[1] == 'tous')
    {
       foreach ($map as $m[1] => $value)
            $ret .= "(:lien ".$m[1]." :) donne %newwin%[[".$value[0]."|".$value[1]."]]<br>";
       return $ret;
    }

    return "%newwin%[[".$map[$m[1]][0]."|".$map[$m[1]][1]."]]";
}

###########################################################
## MakeLinkWithIcon: put an icon after Attach of known type 
## document (pdf, doc) and open them in a new window
###########################################################
Markup('[[ext|','<[[|',"/(?>\\[\\[([^|\\]]*\.([Pp][Dd][Ff]|[Dd][Oo][Cc]|[Pp][Pp][Tt]|[Xx][Ll][Ss]|[Zz][Ii][Pp]))\\s*\\|\\s*)(.*?)\\s*\\]\\]($SuffixPattern)/", "MakeLinkWithIcon");
Markup('[[ext','<[[', "/(?>\\[\\[\\s*(.*?\.([Pp][Dd][Ff]|[Dd][Oo][Cc]|[Pp][Pp][Tt]|[Xx][Ll][Ss]|[Zz][Ii][Pp]))\\s*\\]\\])()($SuffixPattern)/", "MakeLinkWithIcon");
function MakeLinkWithIcon($m)
{
    global $UrlLinkFmt;
    extract($GLOBALS['MarkupToHTML']);
    $target = $m[1];
    $ext = $m[2];
    $text = $m[3];
    $suffix = $m[4];

    $fmt = $UrlLinkFmt;

    $textext = strtolower(substr($text, -3));

    if ($textext != "jpg" && $textext != "gif")
    {
        $fmt = $fmt."<span style=\"white-space:nowrap;display:inline;\">&nbsp;<img style=\"vertical-align:middle;\" src=/pub/icons/".$ext.".gif></span>";
    }

    if (strtolower($ext) != "zip")
    {
        $fmt = "%newwin%".$fmt;
    }

    if (empty($text))
    {
        return Keep(MakeLink($pagename, $target, NULL, $suffix, $fmt), 'L');
    }
    else
    {
        return Keep(MakeLink($pagename, $target, $text, $suffix, $fmt), 'L');
    }
}


###########################################################
## MakeLinkNoIcon: do not put an icon after Attach of known 
## type document (pdf, doc) and open them in a new window
###########################################################
Markup('[[ext|noicon','<[[ext|',"/(?>\\[\\[([^|\\]]*\.([Pp][Dd][Ff]|[Dd][Oo][Cc]|[Pp][Pp][Tt]|[Xx][Ll][Ss]|[Zz][Ii][Pp]))\\s*\\|\\s*)(?:\\s*noicon\\s*\\|\\s*)(.*?)\\s*\\]\\]($SuffixPattern)/", "MakeLinkNoIcon");
function MakeLinkNoIcon($m)
{
    global $UrlLinkFmt;
    extract($GLOBALS['MarkupToHTML']);
    $target = $m[1];
    $ext = $m[2];
    $text = $m[3];
    $suffix = $m[4];

    $fmt = $UrlLinkFmt;

    if (strtolower($ext) != "zip")
    {
        $fmt = "%newwin%".$fmt;
    }

    if (empty($text))
    {
        return Keep(MakeLink($pagename, $target, NULL, $suffix, $fmt), 'L');
    }
    else
    {
        return Keep(MakeLink($pagename, $target, $text, $suffix, $fmt), 'L');
    }
}

###########################################################
## MakeLinkUpdatable: put an icon after Attach of known 
## type document (pdf, doc), add an update link and open them in a new window
###########################################################
Markup('[[ext|updatable','<[[ext|',"/(?>\\[\\[([^|\\]]*\.([Pp][Dd][Ff]|[Dd][Oo][Cc]|[Pp][Pp][Tt]|[Xx][Ll][Ss]|[Zz][Ii][Pp]))\\s*\\|\\s*)(?:\\s*updatable\\s*\\|\\s*)(.*?)\\s*\\]\\]($SuffixPattern)/", "MakeLinkUpdatable");
function MakeLinkUpdatable($m)
{
    global $UrlLinkFmt;
    extract($GLOBALS['MarkupToHTML']);
    $target = $m[1];
    $ext = $m[2];
    $text = $m[3];
    $suffix = $m[4];

    $fmt = $UrlLinkFmt;
    $filename = str_replace("Attach:", "", $target);

    $PageUrl = PageVar($pagename, '$PageUrl');
    $href = $PageUrl."?action=upload&upname=".$filename;
    $fmt = "%newwin%".$fmt."<span style=\"white-space:nowrap;display:inline;\">&nbsp;<img style=\"vertical-align:middle;\" src=/pub/icons/".$ext.".gif></span>&nbsp;<a class='createlink' href='$href' title='\$LinkAlt' rel='nofollow'>Δ</a>";

    if (empty($text))
    {
        return Keep(MakeLink($pagename, $target, NULL, $suffix, $fmt), 'L');
    }
    else
    {
        return Keep(MakeLink($pagename, $target, $text, $suffix, $fmt), 'L');
    }
}

###########################################################
## MakeLinkNewWin: put an icon after http:// links and open them in a new window
###########################################################
Markup('[[newwin|noicon|','<[[newwin|',"/(?>\\[\\[\\s*((?:http|https|ftp):\/\/[^|\\]]*)\\s*\\|\\s*)(?:\\s*(noicon)\\s*\\|\\s*)(.*?)\\s*\\]\\]($SuffixPattern)/", "MakeLinkNewWin");
Markup('[[newwin|','<[[|',"/(?>\\[\\[\\s*((?:http|https|ftp):\/\/[^|\\]]*)\\s*\\|\\s*)()(.*?)\\s*\\]\\]($SuffixPattern)/", "MakeLinkNewWin");
Markup('[[newwin','<[[', "/(?>\\[\\[\\s*((?:http|https|ftp):\/\/.*?)\\s*\\]\\])()()($SuffixPattern)/", "MakeLinkNewWin");
function MakeLinkNewWin($m)
{
    global $UrlLinkFmt, $_SERVER;
    extract($GLOBALS['MarkupToHTML']);
    $target = $m[1];
    $icon = $m[2];
    $text = $m[3];
    $suffix = $m[4];

    $host = 'https://'.$_SERVER['HTTP_HOST'];

    $ret = "";
    if (empty($text))
    {
        $ret = MakeLink($pagename, $target, NULL, $suffix);
    }
    else
    {
        $ret = MakeLink($pagename, $target, $text, $suffix);
    }

    // We do not make the link external if it is internal
    if (substr_count($target, $host) == 0)
    {
        $ret = str_replace(" rel='nofollow'", " target='_blank' rel='nofollow'", $ret);
        if (strlen($icon) == 0 && substr_count($text, "Attach:") == 0)
        {
            $ret = $ret."<span style=\"white-space:nowrap;display:inline;\">&nbsp;<img style=\"vertical-align:middle;\" src=/pub/icons/ext.gif></span>";
        }
    }    
    return Keep($ret, "L");
}

###########################################################
## Conditional link: create a link only if page exist
## [[[link]]]
## [[[link | text]]]
###########################################################
Markup('[[[','<[[',"/(?>\\[\\[\\[\\s*(.*?)\\]\\]\\])($SuffixPattern)/", "conditionalLink");
Markup('[[[|','<[[|', "/(?>\\[\\[\\[([^|\\]]*)\\|\\s*)(.*?)\\s*\\]\\]\\]($SuffixPattern)/", "conditionalLink");
function conditionalLink($m)
{
    extract($GLOBALS['MarkupToHTML']);
    $target = $m[1];
    $text = $m[2];
    $suffix = $m[3] ?? null;

    $targetpage = MakePageName($pagename, $target);

    if (!PageExists($targetpage))
        if (empty($text))
        {
            $target = preg_replace('/\\([^)]*\\)/','',$target);
            $target = preg_replace('!/\\s*$!', '', $target);
            $target = preg_replace('!^.*[^<]/!', '', $target);
            return $target;
        }
        else {
          return $text;
        }
    else {
        if (empty($text))
            return Keep(MakeLink($pagename,$target, NULL, $suffix), 'L');
        else
            return Keep(MakeLink($pagename,$target, $text, $suffix), 'L');
    }
}

Markup('[[[|a','<[[[|', "/(?>\\[\\[\\[Attach:([^|\\]]*)\\|\\s*)(.*?)\\s*(?:\\|\\s*(.*?)\\s*)?\\]\\]\\]($SuffixPattern)/", "conditionalAttach");
function conditionalAttach($m)
{
    global $UploadFileFmt, $UploadDir;
    $path = $m[1];
    $text = $m[2];
    $replacement = $m[3];
    $suffix = $m[4];

    $ret = "file does not exist";
    if (file_exists($UploadDir."/".$path))
    {
        return "[[Attach:".$path."|".$text."]]";
    }
    if ($replacement != "")
    {
        return $replacement;
    }
    return $text;
}


###########################################################
## multipleConditionalLink
###########################################################
Markup('multiCondLink','<simplestr','/\(:multicondlink\\s+(.*?)\\s?:\)/',"multiCondLink");
function multiCondLink($m)
{
//  $opt = ParseArgs($opt);
//  $str = $opt['str'];
  $str = $m[1];
  $template = "[[[$1]]]";
  $retA = []; 

//error_log("multiCondLink AA template:".$template."\n", 3, "c:/temp/my-errors.log");
//error_log("multiCondLink BB m[1]:".$m[1]."\n", 3, "c:/temp/my-errors.log");
  foreach (explode(",", $str) as $value)
  {
//error_log("multiCondLink CC value:".$value."\n", 3, "c:/temp/my-errors.log");
//error_log("multiCondLink CC simplestr(value):".simplestr($value)."\n", 3, "c:/temp/my-errors.log");
    if (!empty($value)) {
      $retA[] = str_replace ("$1", "Membres/".simplestr($value)."|".$value, $template);
    }
  }

  $ret = implode(", ", $retA);

//error_log("multiCondLink EE ret:".$ret."\n", 3, "c:/temp/my-errors.log");
  return $ret;
}

###########################################################
## Usefull to include javascript code
###########################################################
Markup('html', 'fulltext', '/\\(:html:\\)(.*?)\\(:htmlend:\\)/msi', "htmlcode");
function htmlcode($m)  {
  return Keep(str_replace(array('&lt;', '&gt;', '&amp;'), array('<', '>', '&'), $m[1]));
}
###########################################################
## Strrep: Replace string in a string.
## (:strrep stringtomodify firststr2search4 firststr2replace secondstr2search4 secondstr2replace:)
## (:strrep aabbccdd "bb" "xx" "cc" "yy":) becomes aaxxyydd
###########################################################
Markup('strrep','directives','/\(:strrep(all)?\\s+(.*?)\\s?:\)/',"strrep");
function strrep($m)
{
    $search = [];
    $replace = [];
    $all = isset($m[1]) && ! empty($m[1]);

    $opt = ParseArgs($m[2]);
//error_log("AA strrep:".implode("#", $opt[''] )."\n", 3, "c:/temp/my-errors.log");

    $subject = array_shift($opt['']);
    $result = $subject;
    while($opt[''] && ($result == $subject || $all))
    {
        $search = array_shift($opt['']);
        if ($opt[''])
            $replace = array_shift($opt['']);
        else
            $replace = "";
        $result = str_replace($search, $replace, $result);
    }
    return $result;
}

###########################################################
## Strtemplate: Explode a list of string separated by a separator into a list of templates.
##              $0 is replaced in the template with the original string.
## (:strtemplate instring separator template:)
## (:strtemplate "banana , apple" "," "[[addresse#$0|$0]]":) becomes [[addresse#banana|banana]],[[addresse#apple|apple]]
###########################################################
Markup('strtemplate','directives','/\(:strtemplate\\s+(.*?)\\s?:\)/',"strtemplate");
function strtemplate($m)
{
    $opt = ParseArgs($m[1]);

    if ($opt[''])
        $subject = array_shift($opt['']);
    else
        $subject = "";

    if ($opt[''])
        $separator = array_shift($opt['']);
    else
        $separator = ",";

    if ($opt[''])
        $template = array_shift($opt['']);
    else
        $template = "$0";

    return implode($separator." ", preg_replace('/(.+)/', $template, array_map('trim', explode($separator, $subject))));
}

###########################################################
## creatememberpage Action: Create a member page after requesting for a password
###########################################################
SDV($HandleActions['creatememberpage'],'HandleCreateMemberPage');
SDV($CreatePageAuth, 'edit');

XLSDV('en',[
  'CMPNoMember' => 'Invalid member name!',
  'CMPMessage' => '<h2><b>New member page creation</b></h2>
  You are about to create a new page for a member of the CEF.<BR><BR>
  The objective is that this person modifies his page by himself. We thus give you here
  the opportunity to create an account for this person by simply entering a password.
  He will then be able to edit his page with the login made up with the name of its page
  (without space, accents, hyphen, etc... E.g.: MelanieLouiseLeblanc) and the password that
  you enter here. We recommand that you ask her to choose her own password so she can remember it easily.
  As the creator of his page you will also be authorized to modify this page.
  <BR><BR><B>Caution: </B> Create this account only if you will be able to communicate
  this password to this member within a short period of time. Otherwise you would create a
  useless access account to the CEF\'s website, which we try to avoid as much as possible.
  Carefully write down the login and password because it won’t be impossible for us to remind
  them to you afterward.<BR><BR>If you to click on "Create page only" or if you leave the "Password"
  field empty, the page will be created, it will be editable by you only and no account will be
  created for this member.<BR><BR>
  For more info contact the <a href=index.php? n=Membres.admin>administrator</a>.',
  'CMPLogin' => 'New member <b>Login</b>:',
  'CMPPassword' => 'New member <b>Password:</b>',
  'CMPConfirmPassword' => '<b>Confirm</b> Password:',
  'CMPPasswordDiff' => 'The two password were different... Start again.',
  'CMPCreate' => 'Create new account',
  'CMPCreatePageOnly' => 'Create page only',
  'CMPReturn' => 'Cancel and return to '
  ]);

XLSDV('fr',[
  'CMPNoMember' => 'Nom de membre invalide!',
  'CMPMessage' => '<h2><b>Cr&eacute;ation nouvelle page de membre</b></h2>
  Vous êtes sur le point de cr&eacute;er une nouvelle page pour un membre du CEF.<BR><BR>
 L\'objectif est que cette personne modifie elle-même sa page. Nous vous donnons donc ici
 l\'occasion de cr&eacute;er un compte pour elle, rapidement, simplement en saisissant un mot de passe. Elle pourra
 ensuite &eacute;diter sa page avec le login constitu&eacute; du nom de sa page (sans espace, accents, tiret, etc...
 Ex.: MelanieLouiseLeblanc) et du mot de passe que vous lui aurez attribu&eacute;. Un courriel sera envoy&eacute; automatiquement
 à cette personne pour l\'informer de la cr&eacute;ation de sa page et de son compte. Nous vous recommandons de
 lui demander un mot de passe qu\'elle aura choisie elle même afin qu\'elle s\'en souvienne plus facilement.
 En tant que cr&eacute;ateur de sa page vous serez vous aussi autoris&eacute; à la modifier.<BR><BR><B>Attention:</B>
 Cr&eacute;er cette autorisation seulement si vous êtes en mesure de communiquer ce mot de passe à ce membre dans les plus brefs d&eacute;lais.
 Sinon vous cr&eacute;eriez un code d\'accès inutile au site du CEF, ce que nous tentons d\'&eacute;viter le plus possible.
 Prenez bien en note le login et le mot de passe car, il nous sera impossible de vous les rappeler plus tard.<BR><BR>
 Si vous cliquer sur "Cr&eacute;er page seulement", ou que vous laissez le champ "Mot de passe" vide, la page sera cr&eacute;&eacute;e, elle
 sera modifiable par vous uniquement et aucun compte ne sera cr&eacute;&eacute; pour ce membre. <BR><BR>Pour plus d\'info contactez
 l\'<a href=index.php?n=Membres.admin>admin</a>.',
  'CMPLogin' => '<b>Login</b> pour le nouveau membre:',
  'CMPPassword' => '<b>Mot de passe</b> pour le nouveau membre:',
  'CMPConfirmPassword' => '<b>Confirmez</b> le Mot de passe:',
  'CMPPasswordDiff' => 'Les deux mots de passe &eacute;taient diff&eacute;rents... Recommencez.',
  'CMPCreate' => 'Cr&eacute;er page et nouveau compte',
  'CMPCreatePageOnly' => 'Cr&eacute;er page seulement',
  'CMPReturn' => 'Annuler et retourner à '
  ]);

function HandleCreateMemberPage($pagename)
{
    global $SiteAdminGroup, $PageStartFmt, $PageEndFmt, $PageHeaderFmt, $ScriptUrl, $AuthorGroup, $CreatePageAuth, $AuthId, $AuthUserPageFmt;

    $sourcepage = "Membres.MembreData";
    $tempfilename = "Membres.MembreTemplate#memberpage1";
    $header = true;

    $namenumber = 1;
    $name = $_GET['name'];
    $namewithoutnum = $name;
    $lastchar = substr($name, -1);
    if (is_numeric($lastchar) && $lastchar != "1")
    {
        $namenumber = $lastchar;
        $namewithoutnum = substr($name, 0, -1);
    }

    $sourcepage = MakePageName($pagename, $sourcepage);
    if (!PageExists($sourcepage))
        return "%red%HandleCreateMemberPage: Invalid source File!";

    # Read the source file
    $sourceCSV = ReadPage($sourcepage, READPAGE_CURRENT);
    $sourceCSV = $sourceCSV['text'];

    # Parse the CSV content if it was not already
    $parsedCSV = CSVTemplateParseCVSContent($sourceCSV, $header, ",");

    # Check for the name and its associated info in the member database
    $member = [];

    foreach ($parsedCSV as $rowvalues)
    {
        if (simplestr($rowvalues['Prenom'].$rowvalues['Nom']) == $namewithoutnum && $rowvalues['NoIndividus'] == $namenumber)
        {
            $member = $rowvalues;
            break;
        }
    }
    if (!$member)
    {
        # Return an error message
        SDV($HandleCreateMemberPageFmt,array(&$PageStartFmt, "CreateMemberPage: $[CMPNoMember]", &$PageEndFmt));
        PrintFmt($pagename,$HandleCreateMemberPageFmt);
        exit;
    }

    # Define the name of the member page
    $targetname = $AuthorGroup.".".$name;

    $page = [];
    $targetname = MakePageName($pagename, $targetname);

    # Retrieve a data structure for the page
    $page = RetrieveAuthPage($targetname, $CreatePageAuth, true);
    $newpage = $page;

    # Get the password and the password confirmation from the POSTed variable
    $passwd = trim(stripmagic($_POST['cmppass'] ?? ""));
    $passwdcon = trim(stripmagic($_POST['cmpconpass'] ?? ""));

    if (isset($_POST['cmppass']) && $passwd != '' && $passwd === $passwdcon)
    {
        $AuthPage = $SiteAdminGroup.".AuthUser";
        # Write the login and password to the AuthUser page
        $pwpage = ReadPage($AuthPage);
        $newpwpage = $pwpage;
        $newpwpage['text'] = $newpwpage['text']."\n".$name.": ".pmcrypt($passwd)."\n";

        UpdatePage($AuthPage, $pwpage, $newpwpage);

        if ($rowvalues['Courriel'] != '') {
            $subject = "Acces au site web du CEF";
            $msg = "<html>Bonjour,<br><br>Vous avez maintenant acc&egrave;s en mode &eacute;dition au site web du CEF.<br><br>Votre identifiant est: $name<br><br>Et votre mot de passe est: $passwd<br><br>Cet acc&egrave;s devrait vous permettre de modifier <a href=https://www.cef-cfr.ca/index.php?n=Membres.$name>votre propre page web</a> et d'acc&eacute;der &agrave; l'<a href=https://www.cef-cfr.ca/index.php?n=Intranet.Accueil>intranet</a> du site. Merci de lire la page suivante avant de commencer &agrave; &eacute;diter:<br><br><a href=https://www.cef-cfr.ca/index.php?n=Support.IntroductionEditionSite >https://www.cef-cfr.ca/index.php?n=Support.IntroductionEditionSite </a><br><br>S'il y a un probl&egrave;me, contactez l'<a href=https://www.cef-cfr.ca/index.php?n=Membres.admin>administrateur</a> du site web.</html>";
        
           $headers = "From: webmestre.cef@sbf.ulaval.ca".PHP_EOL;
           $headers .= "CC: webmestre.cef@sbf.ulaval.ca".PHP_EOL;
           $headers .= "MIME-Version: 1.0".PHP_EOL;
           $headers .= "Content-type: text/html".PHP_EOL;
           $headers .= "charset=iso-8859-1".PHP_EOL;
           
           mail($rowvalues['Courriel'], $subject, $msg, $headers);
        }

    }
    elseif (!isset($_POST['pageonly']) && (!isset($_POST['cmppass']) || ($passwd != $passwdcon)))
    {
        # The password confirmation is invalid or no password was entered, we have to request the password again
        $ErrorMsg = '';
        if ($passwd != $passwdcon)
            $ErrorMsg = "<tr><td colspan=4 align=center><b><i style='color:#990000'>$[CMPPasswordDiff]<i></b></td></tr>";

        #Request for a password
        SDV($HandleCreateMemberPageFmt,array(&$PageStartFmt,
            "$[CMPMessage]
             <form name='cmprequestpass' action='{$_SERVER['REQUEST_URI']}' method='post'>
                 <input type='hidden' name='n' value='$pagename' />
                 <input type='hidden' name='action' value='creatememberpage' /> \n
                 <input type='hidden' name='name' value='$name' /> \n
                 <br>
                 <table style='background-color: #ffec89;border:1px solid #aaaaaa;padding:5px;border-radius: 5px';>".$ErrorMsg."
                 <tr><td align=right>
                    $[CMPLogin]</td><td><b style='color:#990000'>$name</b>
                 </td></tr>
                 <tr><td align=right valign=middle>$[CMPPassword]</td>
                     <td valign=middle><input class=inputbox tabindex='1' type='password' name='cmppass' value='' /></td></tr>
                 <tr><td align=right valign=middle>$[CMPConfirmPassword]</td>
                     <td valign=middle><input class=inputbox tabindex='2' type='password' name='cmpconpass' value='' /></td>
                     <tr>
                       <td></td>
                       <td valign=middle><input class=inputbuttonbig type='submit' value='$[CMPCreate]' />
             </form>
             <form name='cmponlypage' action='{$_SERVER['REQUEST_URI']}' method='post'>
                 <input type='hidden' name='n' value='$pagename' />
                 <input type='hidden' name='action' value='creatememberpage' />
                 <input type='hidden' name='name' value='$name' />
                 <input type='hidden' name='pageonly' value='true' />
                       <input class=inputbuttonbig type='submit' value='$[CMPCreatePageOnly]' /></td>
                     </tr>
                 </table>
             </form><br>
             $[CMPReturn] <a href=index.php?n=$pagename>$pagename</a>
             <script language='javascript' type='text/javascript'><!--
                  document.cmprequestpass.cmppass.focus() //--></script>", &$PageEndFmt));
        PrintFmt($pagename, $HandleCreateMemberPageFmt);
        exit;
    }

    # Make the page only if it does not already exist
    if (!PageExists($targetname))
    {
        # Separate the name of the template page from the name of the anchor
        list($tempfilename, $tempanchor) = explode('#', $tempfilename, 2);

        # Check if template file exist
        $templatefile = MakePageName($pagename, $tempfilename);
        if (!PageExists($templatefile))
            return "%red%HandleCreateMemberPage: Invalid template File: ".$tempfilename."!";

        # If an anchor is defined parse the content
        if ($tempanchor)
        {
            $templatestring = IncludeText($pagename, $templatefile."#".$tempanchor, true);
            $templatestring = preg_replace('/\\[\\[#[A-Za-z][-.:\\w]*\\]\\]/', '', $templatestring);
        }
        else
        {
            $templatestring = ReadPage($templatefile, READPAGE_CURRENT);
            $templatestring = $templatestring['text'];
        }

        # Remove <:vspace> string
        $templatestring = str_replace("<:vspace>","", $templatestring);

        # Replace every variable with its value
        #$newpage['text'] = preg_replace('/\{([^[$]*?)\}/e', "stripmagic(\$rowvalues[PSS('$1')])", $templatestring);  # replace {name} fields
        #$newpage['text'] = preg_replace('/\{([^[$]*?)\}/e', "getNonEmptyVarValue('$1', \$rowvalues)", $templatestring);  # replace {name} fields
        $newpage['text'] = preg_replace_callback('/\{([^[$]*?)\}/', function($m) use($rowvalues) {
          return getNonEmptyVarValue($m[1], $rowvalues);
        }, $templatestring);  # replace {name} fields
    }


    # Modify permissions so only the author, the creator and administrators can modify the page.
    $newpage['passwdedit'] = "id:@administrateurs,$AuthId,".$name;
    $newpage['passwdattr'] = "id:@administrateurs,$AuthId,".$name;

    UpdatePage($targetname, $page, $newpage);

    Redirect($targetname);
}

function getNonEmptyVarValue($varName, $values)
{
    $result = $values[PSS($varName)];
    if ($result == '')
        $result = $varName."Inconnu";

    return stripmagic($result);
}

$SearchPatterns['equiplist'][] = '!Equipements\\.(Template|Accueil|NavigationPage|SearchResults|Documentation|Results|EquipementsData|RecentChanges|Recherche|GroupAttributes)!';

SDV($HandleActions['equipsearch'], 'HandleEquipSearch');
function HandleEquipSearch($pagename)
{
  global $PageStartFmt, $PageEndFmt, $FmtV, $SearchPatterns;


  foreach ($_GET as $key => $value)
  {
    if ($key == "q")
    {
        $getstr .= stripmagic($value)." ";
        $q = stripmagic($value);
    }
    elseif (substr($key,0,4) == "ptv_")
        $getstr .= "$:".substr($key, 4)."=".stripmagic($value)." ";
    else
        $getstr .= $key."=".stripmagic($value)." ";
  }
//error_log("AA getstr:".$getstr."\n", 3, "c:/temp/my-errors.log");
  $pre = "!!'''Recherche de \"".$q."\" dans les pages d'équipements...'''

(:input form 'https://www.cef-cfr.ca/index.php' get:)
(:input hidden n Equipements.SearchResults:)
(:input hidden action equipsearch:)
(:input hidden list equiplist:)
(:input hidden group Equipements:)
(:input hidden $:Categorie -:)
(:input hidden fmt Template#listsearch:)
(:input text q:)(:input submit value=Rechercher:)
(:input end:)
";


  $result = FmtPageList('$MatchList', $pagename, array('o' => $getstr));

  $post = "

%red%'''".$FmtV['$MatchCount']."%% équipements trouvés sur %red%".$FmtV['$MatchSearched']."%% équipements.'''

[[Equipements.Accueil | Retour à la page d'accueil des équipements]]

";

  SDV($HandlePageListFmt,array(&$PageStartFmt, MarkupToHTML($pagename, $pre.$result.$post), &$PageEndFmt));
  PrintFmt($pagename, $HandlePageListFmt);
  return;
}


SDV($HandleActions['equiplist'], 'HandleEquipList');
function HandleEquipList($pagename)
{
  global $PageStartFmt, $PageEndFmt, $FmtV, $SearchPatterns;

  foreach ($_GET as $key => $value)
  {
    if (substr($key,0,4) == "ptv_")
        $getstr .= "$:".substr($key, 4)."=".stripmagic($value)." ";
    else
        $getstr .= $key."=".stripmagic($value)." ";
  }

  $result = FmtPageList('$MatchList', $pagename, array('o' => $getstr));

  SDV($HandlePageListFmt,array(&$PageStartFmt, MarkupToHTML($pagename, $result), &$PageEndFmt));
  PrintFmt($pagename, $HandlePageListFmt);
  return;
}

Markup('fbgpmeta', 'directives', '/\\(:fbgpmeta\\s(.*?):\\)/i', "FbGpMeta");
function FbGpMeta($m)
{
  global $HTMLHeaderFmt;

  $o = ParseArgs($m[1]);
  $im = $tm = $dm = '';
  if($o['image'] ?? false)
  {
    $o['image'] = PUE($o['image']);
    $im = "\n<meta property=\"og:image\" content=\"".$o['image']."\"/>\n"
        ."<meta itemprop=\"image\" content=\"".$o['image']."\" />\n";
  }

  if($o['title'] ?? false)
  {
    $title = htmlspecialchars(  $o['title'], ENT_QUOTES);
    $tm = "\n<meta itemprop=\"name\" content=\"".$title."\" />\n"
        ."<meta property=\"og:title\" content=\"".$title."\"/>\n";
  }

  if($o['description'] ?? false)
  {
    $description =  htmlspecialchars(  $o['description'], ENT_QUOTES);
    $dm = "\n<meta itemprop=\"description\" content=\"".$description."\" />\n"
        ."<meta property=\"og:description\" content=\"".$description."\"/>\n";
  }

  $HTMLHeaderFmt['fbgpmeta'] = $im.$tm.$dm;
}

###########################################################
## montageimage: Return an arrangement of images based on provided parameters.
##
## (:montageimage images="membres/yanboulanger.jpg membres/louisbelanger.jpg membres/juniortremblay.jpg" width=60 nbcol=2 floatleft=true lienphoto=true:)
##
###########################################################
Markup('photomontageold','<bibtexquery','/\(:(?:photomontageold|montagephotoold)\\s+(\\S.*?)\\s?:\)/',"PhotoMontageOld");
function PhotoMontageOld($m)
{
  $defaults = array('imageslist' => '', 'width' => '100', 'nbcol' => '', 'floatleft' => 'false', 'lienphoto' => 'false');

  $opt = array_merge($defaults, ParseArgs($m[1]));
  $imageslist = trim($opt['images'] ?? "");
  $width = $opt['width'];
  $nbcol = $opt['nbcol'];
  $floatleft = $opt['floatleft'];
  $lienphoto = $opt['lienphoto'];

  if ($imageslist == "")
  {
    return "";
  }

  if (!is_numeric($width))
  {
    unset($width);
    unset($nbcol);
  }

  $debug = "\$opt=".implode("|",$opt)."\n\n";
  $debug = $debug."\$imageslist=".$imageslist."\n";
  $debug = $debug."\$width=".$width."\n";
  $debug = $debug."\$nbcol=".$nbcol."\n";
  $debug = $debug."\$floatleft=".((strcasecmp($floatleft, "true") == 0) ? "true" : "false")."\n";
  $debug = $debug."\$lienphoto=".((strcasecmp($lienphoto, "true") == 0) ? "true" : "false")."\n";

  $ret = $ret."(:div1 class=\"photomontage\" style=\"max-width: 50%;float:";

  # determine floating
  if (strcasecmp($floatleft, "true") == 0)
  {
    $ret = $ret."left; margin-right:5px;";
  }
  else
  {
    $ret = $ret."right; margin-left:5px;";
  }

  $ret = $ret." display:flex; justify-content:center;";

  # determine width
  if ($nbcol != '')
  {
    $ctn_width = $nbcol * $width + 1;
    $ctn_width = " width:".$ctn_width."px; flex-wrap:wrap;";
    $ret = $ret.$ctn_width;
  }

  $ret = $ret."\":)";

  foreach (explode(" ", $imageslist) as $image)
  {
    $ret = $ret."%frame width=".$width."px%";
    if (strcasecmp($lienphoto, "true") == 0)
	  {
	    $ret = $ret."%newwin%[[https://www.cef-cfr.ca/uploads/".$image."|";
    }
    $ret = $ret."Attach:".$image;
    if (strcasecmp($lienphoto, "true") == 0)
	  {
	    $ret = $ret."]]";
    }
  }

  $ret = $ret."%%\n(:div1end:)";

  $debug = "[-[@".$debug."\n\n".$ret."@]-]";

  return $ret;
  #return $debug;
  #return $ret."\n[[<<]]".$debug;
}

Markup('photomontage','<bibtexquery','/\(:(?:photomontage|montagephoto)\\s+(\\S.*?)\\s?:\)/',"PhotoMontage");
function PhotoMontage($m)
{
  $defaults = array('imageslist' => '', 'width' => '80', 'floatleft' => 'false', 'lienphoto' => 'false', 'noclear' => 'false', 'noborder' => 'false');

  $opt = array_merge($defaults, ParseArgs($m[1]));
  
  $imageslist = trim($opt['images'] ?? "");
  $width = $opt['width'];
  $floatleft = $opt['floatleft'];
  $lienphoto = $opt['lienphoto'];
  $noclear = $opt['noclear'];
  $noborder = $opt['noborder'];

  if ($imageslist == "")
  {
    return "";
  }

  if (!is_numeric($width))
  {
    $width = '80';
  }

  //$debug = "\$opt=".implode("|",$opt)."\n\n";
  //$debug = $debug."\$imageslist=".$imageslist."\n";
  //$debug = $debug."\$width=".$width."\n";
  //$debug = $debug."\$floatleft=".((strcasecmp($floatleft, "true") == 0) ? "true" : "false")."\n";
  //$debug = $debug."\$lienphoto=".((strcasecmp($lienphoto, "true") == 0) ? "true" : "false")."\n";

  $grid = "(:div1 class=\"photomontage2\" style=\"display:grid; grid-template-columns: repeat(auto-fit, ".$width."px);";

  if (strcasecmp($noclear, "true") == 0) {
    $grid = $grid."clear: none !important;";
  }
  
  $grid = $grid."max-width: min(50%,".($width*2)."px);min-width:".$width."px;float:";

  # determine floating
  if (strcasecmp($floatleft, "true") == 0)
  {
    $grid = $grid."left; margin-right:5px;";
  }
  else
  {
    $grid = $grid."right; margin-left:5px;";
  }

  $grid = $grid."\":)";

  $ret = $grid;
  $cnt = 0;
  $imageslist = preg_replace('/\s+/', ' ', $imageslist);
  $imgarr = explode(" ", $imageslist);
  foreach ($imgarr as $image)
  {
    //$debug = $debug."\ncnt=".$cnt." count(img)=".count($imgarr)."\n";
    if (($cnt % 2) == 0 && $cnt == (count($imgarr) - 1))
    {
      $ret = $ret."\n(:div1end:)\n".$grid;
    }
    $cnt++;
    if (strcasecmp($noborder, "true") == 0) {
      $ret = $ret."\n%float";
    }
    else {
      $ret = $ret."\n%frame";
    }
    $ret = $ret." width=".$width."px";
    if (strcasecmp($lienphoto, "true") == 0)
	  {
	    $ret = $ret." newwin%[[https://www.cef-cfr.ca/uploads/".$image."|";
    }
    else {
      $ret = $ret."%";
    }
    $ret = $ret."Attach:".$image;
    if (strcasecmp($lienphoto, "true") == 0)
	  {
	    $ret = $ret."]]";
    }
    $ret = $ret."%%";
  }

  $ret = $ret."\n(:div1end:)";

  //$debug = "[-[@".$debug."\n\n".$ret."@]-]";

  return $ret;
  #return $debug;
  #return $ret."\n[[<<]]".$debug;
}

###########################################################
## (:centercol:)
## (:wrapingrow:)
###########################################################
Markup('centercolend','<if','/\(:centercolend\\s*:\)/',
"</div>");

Markup('centercol','<if','/\(:centercol\\s*:\)/',
'<div style="display:flex; flex-direction:column; align-items:center; gap:10px;">');

Markup('wrapingrowend','<if','/\(:wrapingrowend\\s*:\)/',
"</div>");

Markup('wrapingrow','<if','/\(:wrapingrow(?:\s+([a-z-_]*?))?(?:\s+"([^"]*?)")?\s*:\)/',
'<div id="$1" style="display:flex; justify-content:center; flex-wrap:wrap; gap:10px;$2">');
###########################################################
## Redefine the fox javascript validation function
###########################################################
function MyFoxJSFormCheck($formcheck, $name = '') {
  global $userlang;
  SDVA($langOrder, array("fr"=>0, "en"=>1));
  $reqfields = preg_split("/[,|]+/", $formcheck, -1, PREG_SPLIT_NO_EMPTY);

  $nblang = 1;
  if (is_numeric (current($reqfields)))
  {
     $nblang = current($reqfields);
     next($reqfields);
  }
  $fieldNames = [];
  $langstrings = [];
  
  while (current($reqfields))
  {
     $fieldKey = current($reqfields);
     for ($i = 1; $i <= $nblang; $i++)
     {
        $langstrings[] = next($reqfields);
     }
     $fieldNames[$fieldKey] = $langstrings;

     unset($langstrings);
     next($reqfields);
  }

  $out = "   
  <script type='text/javascript' language='JavaScript1.2'><!--
    function checkform$name ( form ) {
      ";

  $elemNameArray = "var elemName = new Array(";
  $elemMessageArray = "var elemMessage = new Array(";

  foreach($fieldNames as $key => $fieldName)
  {
      $elemNameArray .= "\"".$key."\",";
      if ($userlang == "fr")
      {
         $elemMessageArray .= "\"".$fieldName[$langOrder["fr"]]."\",";
         $elemMessagePrefix = "Le champ ";
         $elemMessagePostfix = " est requis!";
         $twoElemMessagePrefix = "Au moins un des champs ";
         $elemMessagePostfix = " est requis!";
         $radioMessagePrefix = "Vous devez sélectionner ";
         $radioMessagePostfix = "...";
      }
      else
      {
         $elemMessageArray .= "\"".$fieldName[$langOrder["en"]]."\",";
         $elemMessagePrefix = "Field ";
         $elemMessagePostfix = " is required!";
         $twoElemMessagePrefix = "At least one of ";
         $radioMessagePrefix = "You must select ";
         $radioMessagePostfix = "...";
      }
  }
  $elemNameArray = rtrim ($elemNameArray, ',');
  $elemMessageArray = rtrim ($elemMessageArray, ',');
  
  $out .= $elemNameArray.");
      ".$elemMessageArray.");
      ";
  
  $out .= 
     "var nIx =0;
      while (elemName[nIx])
      {
        elemName1 = elemName[nIx].split(':')[0];
        elemName2 = elemName[nIx].split(':')[1];
        elem1 = form.elements[elemName1];
        elem2 = form.elements[elemName2];
        if (elem1)
        {
          radioelem1 = elem1[0];
          if (elem1.type && elem1.type == \"file\" && elem1.value == \"\")
          {
            window.alert('$radioMessagePrefix' + elemMessage[nIx] + '$radioMessagePostfix');
            elem1.focus();
            return false;                
          }
          else if (elem1.type && elem1.value == \"\")
          {
            if (elem2 && elem2.type) {
              if (elem2.value == \"\") {
                window.alert('$twoElemMessagePrefix\'' + elemMessage[nIx] + '\'$elemMessagePostfix');
              }
              else {
                return true;
              }
            }
            else {
              window.alert('$elemMessagePrefix\'' + elemMessage[nIx] + '\'$elemMessagePostfix');
            }
            elem1.focus();
            return false;
          }
          else if (radioelem1 && radioelem1.type == 'radio')
          {
            oneischecked = false;
            i = 0;
            while (radioelem1)
            {
              if (radioelem1.checked)
                oneischecked = true;
              i++;
              radioelem1 = form.elements[elemName1][i];
            }
            if (!oneischecked)
            {
              window.alert('$radioMessagePrefix' + elemMessage[nIx] + '$radioMessagePostfix' );
              return false ;
            }
          }
        }
        nIx++;
      }
      ";
  $out .= 
  "return true ;
    }
  --></script>
  ";
  return $out;    
} //}}}


###########################################################
## Default translation link to Google Translate if the page is not explicitly translated
###########################################################
Markup('selectlang2', '<selectlang', "/\\(:selectlang2\s*(.*?):\\)/i", "LanguageSelection2");
function LanguageSelection2($m)
{
  global $LanguageSelectionFmt, $pagename, $PCache, $DefaultLanguages;

  $args = ParseArgs($m[1]);
  $pn = $args['page'] ?? $pagename;

  $googleTranslateFmt = "%newwin%[[http://translate.google.com/translate?js=n&sl=auto&tl=$1&u={\$PageUrl}|noicon|$[$1]]] ";

  $pn = MakePageName($pagename,$pn);
  if (!PageExists($pn)) return '';
  if (!isset($PCache[$pn])) {
  	PCache($pn, ReadPage($pn, READPAGE_CURRENT));
  }

  if (!($PCache[$pn] ?? false) || !($PCache[$pn]['languages'] ?? false)) {
  	$PageLanguages = [];
  } else {
  	$PageLanguages = explode(',',@$PCache[$pn]['languages']);
  }

  $mid = '';
  foreach ($DefaultLanguages as $lang) {
  	if (in_array($lang,$PageLanguages)) {
      $mid .= str_replace('$1',$lang,$LanguageSelectionFmt);
  	} 
      /*else if (isset($args['default'])) {
    $mid .= FmtPageName(str_replace('$1',$lang,$LanguageSelectionFmt),$args['default'])."X";
  	}*/
    else 
    {
      $mid .= str_replace('$1',$lang,$googleTranslateFmt);
    }
  }
  foreach ($PageLanguages as $lang) {
  	if (!in_array($lang,$DefaultLanguages)) {
      $mid .= str_replace('$1',$lang,$LanguageSelectionFmt);
  	}
  }
  return FmtPageName($mid,$pn);
}

###########################################################
## Fetch MembreData data from UQAM service
###########################################################
Markup('updatemembredata', 'directives', "/\\(:updatemembredata\s*:\\)/i", "UpdateMembreData");

function UpdateMembreData()
{
  $membreDataPageName = "Membres/MembreData";
  $membreDataPage = ReadPage($membreDataPageName);
  $newMembreDataPage = $membreDataPage;
  $newMembreDataPage['text'] = fetchCSVData("https://cef-cfr.uqam.ca/membres/membreData.php");
  UpdatePage($membreDataPageName, $membreDataPage, $newMembreDataPage);

  return "'''Update to ".$membreDataPageName." %red%Done...%%'''";
}

//Markup('foxinclude','<markup','/\(:foxinclude\\s+(\\S.*?)\\s?:\)/',"CSVTemplate");

###########################################################
## matchdateregex conditional
## return true if given date match format
###########################################################
$Conditions['matchdateregex'] = 'MatchDateRegex($pagename, $condparm)';
function MatchDateRegex($pagename, $condparm) {
  // https://stackoverflow.com/questions/13194322/php-regex-to-check-date-is-in-yyyy-mm-dd-format
  $pattern = '/^\s*(((((1[26]|2[048])00)|[12]\d([2468][048]|[13579][26]|0[48]))-((((0[13578]|1[02])-(0[1-9]|[12]\d|3[01]))|((0[469]|11)-(0[1-9]|[12]\d|30)))|(02-(0[1-9]|[12]\d))))|((([12]\d([02468][1235679]|[13579][01345789]))|((1[1345789]|2[1235679])00))-((((0[13578]|1[02])-(0[1-9]|[12]\d|3[01]))|((0[469]|11)-(0[1-9]|[12]\d|30)))|(02-(0[1-9]|1\d|2[0-8])))))\s*$/';
  return preg_match($pattern, trim($condparm ?? "", "\"\' \n\r\t\v\x00"));
}

Markup('stylesheet', '<[=',"/\\(:stylesheet:\\)(.*?)\\(:stylesheetend:\\)/si", "mu_stylesheet");
function mu_stylesheet($m) {$GLOBALS['HTMLStylesFmt'][] = $m[1];}