<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004-2022 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This file does simple reference counting on pages in a PmWiki site.
    Simply activate this script using
        include_once('scripts/refcount.php');
    in the config.php file and then use ?action=refcount to bring up
    the reference count form.  The output is a table where each row
    of the table contains a page name or link reference, the number
    of (non-RecentChanges) pages that contain links to the page,
    the number of RecentChanges pages with links to the page, and the
    total number of references in all pages.
    
    Script maintained by Petko YOTOV www.pmwiki.org/petko
*/

SDV($PageRefCountFmt,"<h2 class='wikiaction'>Reference Count Results</h2>");
SDV($RefCountTimeFmt," <small>%Y-%b-%d %H:%M</small>");
SDV($HandleActions['refcount'], 'HandleRefCount');
$urlpattern = "/(?:ht|f)tp(?:s?)\:\/\/[0-9a-zA-Z](?:[-.\w]*[0-9a-zA-Z])*(:(?:0-9)*)*(?:\/?)(?:[a-zA-Z0-9\-\=\.\?\,\'\/\\\+&amp;%\$#_]*[a-zA-Z0-9\-\?\,\'\/\\\+&amp;%\$#_])?/";

function PrintRefCount($pagename) {
  global $GroupPattern,$NamePattern,$PageRefCountFmt,$RefCountTimeFmt, $ScriptUrl, $urlpattern;
  $pagelist = ListPages();
  $grouplist = array();
  foreach($pagelist as $pname) {
    if (!preg_match("/^($GroupPattern)[\\/.]($NamePattern)$/",$pname,$m))
      continue;
    $grouplist[$m[1]]=$m[1];
  }

  asort($grouplist);
  $grouplist = array_merge(array('all' => 'all groups'),$grouplist);

  $wishlist = array('all','missing','existing','orphaned');
  $tolist = isset($_REQUEST['tolist']) ? $_REQUEST['tolist'] : array('all');
  $fromlist = isset($_REQUEST['fromlist']) ? $_REQUEST['fromlist'] : array('all');
  $whichrefs = @$_REQUEST['whichrefs'];
  $displayref = isset($_REQUEST['displayref']) ? $_REQUEST['displayref'] : 'refonly';
  $timeout = isset($_REQUEST['timeout']) ? $_REQUEST['timeout'] : '1';

  $submit = @$_REQUEST['submit'];

  echo FmtPageName($PageRefCountFmt,$pagename);
  echo FmtPageName("<form method='post' action='{\$PageUrl}'><input type='hidden' name='action' value='refcount'/>
    <table cellspacing='10'><tr><td valign='top'>Show
    <br/><select name='whichrefs'>",$pagename);
  foreach($wishlist as $w)
    echo "<option ",($whichrefs==$w) ? 'selected="selected"' : ''," value='$w'>$w</option>\n";
  echo "</select></td><td valign='top'> links to pages in group<br/>
    <select name='tolist[]' multiple='multiple' size='4'>";
  foreach(array_merge($grouplist, array('external' => 'external links')) as $g=>$t)
    echo "<option ",in_array($g,$tolist) ? 'selected="selected"' : ''," value='$g'>$t</option>\n";
  echo "</select></td><td valign='top'> referenced from pages in<br/>
    <select name='fromlist[]' multiple='multiple' size='4'>";
  foreach($grouplist as $g=>$t)
    echo "<option ",in_array($g,$fromlist) ? 'selected="selected"' : ''," value='$g'>$t</option>\n";
  $displayoptions = array('refonly' => 'reference only', 
                          'refpageafter' => 'referencing pages after each reference', 
                          'groupbyrefpage' => 'references grouped by referencing page');

  echo "</select></td></tr></table>
    <p><select name='displayref'>";
  foreach($displayoptions as $g=>$t)
    echo "<option ",($g == $displayref) ? 'selected="selected"' : ''," value='$g'>$t</option>\n";
  
  echo "</select></td></tr></table>
    <p>Timeout for external links: <select name='timeout'>";
  foreach(range(1, 10) as $t)
    echo "<option ",($t == $timeout) ? 'selected="selected"' : ''," value='$t'>$t</option>\n";
  echo "</select> seconds</p><p><input type='submit' name='submit' value='Search'/></p></form><hr/>";

  if ($submit) {
    // count the number of reference
    foreach($pagelist as $pname) {
      $page = ReadPage($pname, READPAGE_CURRENT); 
      if (!$page) continue;
      $tref[$pname]['time'] = $page['time'];
      if (!in_array('all',$fromlist) &&
          !in_array(FmtPageName('$Group',$pname),$fromlist)) continue;
      $rc = preg_match('/RecentChanges$/',$pname);
      foreach(explode(',',strval(@$page['targets'])) as $r) {
        if ($r=='') continue;
        if ($rc) @$tref[$r]['rc']++;
        else {
          @$tref[$r]['page']++; 
          @$pref[$r][$pname]++;
        }
      }
      preg_match_all($urlpattern, @$page['text'], $matches);
      foreach ($matches[0] as $match) {
        if ($match == '') continue;
        if ($rc) @$tref[$match]['rc']++;
        else {
          @$tref[$match]['page']++; 
          @$pref[$match][$pname]++;
        }
      }
    }

    echo "<table border=1 class='refcount sortable'>";
    if ($displayref == 'groupbyrefpage')
    {
      echo "<tr><th>Name</th><th>All References</th><th>Selected Only</th></tr>";
      if (is_array($pref))
      {
        // transform the array of references in an array of pages
        reset($pref);
        foreach($pref as $ref=>$pages) {
          foreach($pages as $page=>$d) {
            @$xref[$page]['page']++;
            @$xref[$page][$ref]++;
          }
        }
       
        uasort($xref,'RefCountCmp');
        foreach($xref as $page=>$refs) {
          $reftxt = '';
          $selcnt = 0;
          foreach ($refs as $ref=>$c)
          {
            if($ref != 'page')
            {
              $isExternal = IsExternal($ref);
              if (!in_array('external',$tolist) && $isExternal) continue;
              if (!in_array('all',$tolist) && !$isExternal && !in_array(FmtPageName('$Group',$ref),$tolist)) continue;
              if ($isExternal && $whichrefs=='missing' && !isset($httpcode[$ref])) {
                $httpcode[$ref] = HTTPCode($ref, $timeout);
              }
              if ($whichrefs=='missing' && 
                  ((!$isExternal && PageExists($ref)) || 
                   ($isExternal && in_array($httpcode[$ref], array('200', '301', '302', '303', '308', '403', '426', '429', '999'))))) continue;
              elseif ($whichrefs=='existing' && !PageExists($ref)) continue;
              elseif ($whichrefs=='orphaned' &&
                (@$tref[$ref]['page']>0 || !PageExists($ref))) continue;
              $link = MakeLink($pagename, $ref, $ref.($isExternal && $whichrefs=='missing' && $httpcode[$ref] != "" ? " (".$httpcode[$ref].")" : ""));

              $reftxt .= "<li>".MakeLinkNewWin2($link)."</li>";
              $selcnt++;
            }
          }
          if ($reftxt !== '')
          {
            $link = MakeLink($pagename, $page, $page);
            echo "<tr><td valign='top'>",MakeLinkNewWin2($link);
            echo "<br><ul>".$reftxt."</ul>";
            echo "</td>";
            echo "<td align='center' valign='top'>",@$xref[$page]['page']+0,"</td>";
            echo "<td align='center' valign='top'>",$selcnt,"</td>";
            echo "</tr>";
          }
        }
      }
    }
    else
    {
      uasort($tref,'RefCountCmp');
      echo "<tr><th>Name / Time</th><th>All Referring Pages</th><th>Recent Change Pages Only</th></tr>";
      reset($tref);
      foreach($tref as $ref=>$c) {
        $isExternal = IsExternal($ref);
        if (!in_array('external',$tolist) && $isExternal) continue;
        if (!in_array('all',$tolist) && !$isExternal && !in_array(FmtPageName('$Group',$ref),$tolist)) continue;
        if ($isExternal && $whichrefs=='missing') $httpcode = HTTPCode($ref, $timeout);
        if ($whichrefs=='missing' && 
            ((!$isExternal && PageExists($ref)) || 
             ($isExternal && in_array($httpcode, array('200', '301', '302', '403'))))) continue;
        elseif ($whichrefs=='existing' && !PageExists($ref)) continue;
        elseif ($whichrefs=='orphaned' &&
          (@$tref[$ref]['page']>0 || !PageExists($ref))) continue;
        $link = MakeLink($pagename, $ref, $ref.($isExternal && $whichrefs=='missing' && $httpcode != "" ? " (".$httpcode.")" : ""));
        echo "<tr><td valign='top'>",MakeLinkNewWin2($link);
        if (@$tref[$ref]['time']) echo PSFT($RefCountTimeFmt,$tref[$ref]['time']);
        
        if ($displayref == 'refpageafter' && is_array(@$pref[$ref])) {
          foreach($pref[$ref] as $pr=>$pc) {
            $link = LinkPage($pagename, '', $pr, '', $pr);
            echo "<dd>", MakeLinkNewWin2($link);
          }
        }
        
        echo "</td>";
        echo "<td align='center' valign='top'>",@$tref[$ref]['page']+0,"</td>";
        echo "<td align='center' valign='top'>",@$tref[$ref]['rc']+0,"</td>";
        echo "</tr>";
      }
    }
    echo "</table>";
  }
}

function RefCountCmp($ua,$ub) {
  if (@($ua['page']!=$ub['page'])) return @($ub['page']-$ua['page']);
  if (@($ua['rc']!=$ub['rc'])) return @($ub['rc']-$ua['rc']);
  return @($ub['time']-$ua['time']);
}

function HandleRefCount($pagename, $auth='read') {
  global $HandleRefCountFmt,$PageStartFmt,$PageEndFmt;
  $page = RetrieveAuthPage($pagename, $auth, true, READPAGE_CURRENT);
  if (!$page) Abort('?unauthorized');
  PCache($pagename, $page);
  SDV($HandleRefCountFmt,array(&$PageStartFmt,
    'function:PrintRefCount',&$PageEndFmt));
  PrintFmt($pagename,$HandleRefCountFmt);
}

function HTTPCode($link, $timeout = 3)
{
  global $FarmD;
  ini_set('default_socket_timeout', $timeout);
  $headers = @get_headers($link);
  if ($link === "") return false;
  $headers = (is_array($headers)) ? implode( "\n ", $headers) : $headers;
  preg_match('#^HTTP/.*\s+([0-9]{3})+\s#i', $headers, $matches);
  if ($matches[1] == "") return "timeout";
  return $matches[1];
}

function MakeLinkNewWin2($link)
{
  return str_replace("<a class", "<a target='_blank' class", $link);
}

function IsExternal($link)
{
  global $urlpattern;
  $localURLPattern = "/https?:\/\/www\.cef-cfr\.ca/";
  if (preg_match($localURLPattern, $link)) return false;
  if (preg_match($urlpattern, $link)) return true;
  return false;
}
