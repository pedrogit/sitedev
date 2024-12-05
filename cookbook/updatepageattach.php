<?php if (!defined('PmWiki')) exit();

SDVA($HandleActions, array('updatepageattachcheck' => 'HandleUpdatePageAttachCheck'));
SDVA($HandleActions, array('updatepageattach' => 'HandleUpdatePageAttach'));
function HandleUpdatePageAttachCheck($pagename) {
  return HandleUpdatePageAttach($pagename, false);
}

function HandleUpdatePageAttach($pagename, $doit = true) {
    global $UploadDir, $UploadPrefixFmt, $UploadUrlFmt, $EnableUploadOverwrite,
    $TimeFmt, $EnableDirectDownload, $HandleAuth, $FarmD, $PageStartFmt, $PageEndFmt;
    
  $updir = FmtPageName("$UploadDir$UploadPrefixFmt", $pagename);
//error_log("HandleUpdatePageAttach AAAA updir = ".$updir."\n", 3, "$FarmD/my-errors.log");

    $dirp = @opendir($updir);
    if (!$dirp)
    {
//error_log("HandleUpdatePageAttach BBBB filelist[".$fileindex."] = ".$filelist[$fileindex]."\n", 3, "$FarmD/my-errors.log");
      return '';
      
    }
    
    # Build the file list
    $filelist = array();
    while (($file = readdir($dirp)) !== false)
    {
        if ($file[0] == '.')
          continue;
        if (@$matchext && !preg_match(@$matchext, $file))
          continue;
            
          $fileindex = $file;
            
        $filelist[$fileindex] = $file;
//error_log("HandleUpdatePageAttach CCCC filelist[".$fileindex."] = ".$filelist[$fileindex]."\n", 3, "$FarmD/my-errors.log");
    }
    closedir($dirp);

    #Build the list of every attachment on the whole site
    $attachlist = array();
//error_log("HandleUpdatePageAttach CCCC filelist = ".$filelist."\n", 3, "$FarmD/my-errors.log");
    $attachlist = GetPagesAttachmentsCaseUnsensitive();
//error_log("HandleUpdatePageAttach DDDD attachlist = ".implode(", ", $attachlist)."\n", 3, "$FarmD/my-errors.log");

    $out = "<table border=1 class='zebra sortable' id=join style='width=100% font-family:Arial; font-size:13px'>";
    $out .= "<tr><td><strong>File</strong></td><td><strong>Pages</strong></td><td><strong>".($doit ? "Updated" : "To Update")."</strong></td>";
    foreach($filelist as $fileindex => $filename)
    {
       $out .= "<tr>";
       //$out .= "<td>".$filename."</td>";
       //$out .= "<td>".$fileindex."</td>";

       $indexOri = FmtPageName("$UploadPrefixFmt/".$fileindex, $pagename);
       //$index = FmtPageName("$UploadPrefixFmt/".strtolower($fileindex), $pagename);
       $index = strtolower(FmtPageName("$UploadPrefixFmt/".$fileindex, $pagename));
  $index = substr($index, 1);
       //$out .= "<td>".$indexOri."</td>";
       $out .= "<td>".$index."</td>";
//error_log("HandleUpdatePageAttach DDDD index = ".$index."\n", 3, "$FarmD/my-errors.log");
//error_log("HandleUpdatePageAttach DDDD strtolower(index) = ".strtolower($index)."\n", 3, "$FarmD/my-errors.log");

       $rewritten = false;
       $onerewritten = false;
       //if ($attachlist[$index])
       if ($attachlist[$index])
       {
//error_log("HandleUpdatePageAttach EEEE index = ".$index."\n", 3, "$FarmD/my-errors.log");

            $delete = "";
            $pagelist = "";
            $attachlistcount = array_count_values($attachlist[$index]);
            $uniquearray = array_unique($attachlist[$index]);
            asort($uniquearray);
            foreach ($uniquearray as $link)
            {
                //$rewritten = $rewritten || ReWriteAttachement($link, $filename, $doit);
                $rewritten = ReWriteAttachement($link, $filename, $doit);
//error_log("HandleUpdatePageAttach XXXX link = ".$link."\n", 3, "$FarmD/my-errors.log");
//error_log("HandleUpdatePageAttach YYYY filename = ".$filename."\n", 3, "$FarmD/my-errors.log");
//error_log("HandleUpdatePageAttach ZZZZ rewritten = ".($rewritten ? "true" : "false")."\n", 3, "$FarmD/my-errors.log");
                $onerewritten = $onerewritten || $rewritten;
//error_log("HandleUpdatePageAttach WWWW onerewritten = ".($onerewritten ? "true" : "false")."\n", 3, "$FarmD/my-errors.log");
                if ($rewritten)
                {
                $counttxt = "";
                if ($attachlistcount[$link] > 1)
                    $counttxt = "($attachlistcount[$link])";
                
                $pagelist .= MakeLink($pagename, PSS($link), NULL, NULL).$counttxt.", ";
                }
            }
            $pagelist = rtrim($pagelist, " ,");
            if ($onerewritten == false)
               $pagelist = FmtPageName("$[ULnoreffile]", $pagename);
        }
        else
            $pagelist = FmtPageName("$[ULnoreffile]", $pagename);
          
        $out .= "<td>".$pagelist."</td>";
//error_log("HandleUpdatePageAttach DDDD filename = ".$filename."\n", 3, "$FarmD/my-errors.log");
//error_log("HandleUpdatePageAttach EEEE index = ".$index."\n", 3, "$FarmD/my-errors.log");
        $out .= "<td".($onerewritten ? " style='background-color:#FF0000'" : "").">".($onerewritten ? "Yes" : "No")."</td>";
        $out .= "</tr>";
    }
    $out .= "</table>";
//error_log("HandleUpdatePageAttach FFFF out = ".$out."\n", 3, "$FarmD/my-errors.log");
    //PrintFmt($pagename, $out);
    
    SDV($HandlePageListFmt,array(&$PageStartFmt, $out, &$PageEndFmt));
    PrintFmt($pagename, $HandlePageListFmt);
    return;
    //return $out;
}

function ReWriteAttachement($link, $filename, $doit = true)
{
  global $CreatePageAuth, $FarmD;
//error_log ("ReWriteAttachement AAAA link = ".$link."\n", 3, "$FarmD/my-errors.log");
//error_log("ReWriteAttachement BBBB filename = ".$filename."\n", 3, "$FarmD/my-errors.log");
  # Retrieve a data structure for the page
  //$page = RetrieveAuthPage($link, $CreatePageAuth, true);
  $page = RetrieveAuthPage($link, $CreatePageAuth, true);
  $newpage = $page;
  //$newpage['text'] = preg_replace_callback('/\{([^[$]*?)\}/', function($m) use($rowvalues) {
//error_log("ReWriteAttachement CCCC page['text'] = ".$page['text']."\n", 3, "$FarmD/my-errors.log");
  $newpage['text'] = str_ireplace($filename, $filename, $newpage['text']);
//error_log("ReWriteAttachement DDDD newpage['text'] = ".$newpage['text']."\n", 3, "$FarmD/my-errors.log");

  if ($newpage['text'] != $page['text'])
  //if (stripos($newpage['text'], $filename) == true)
  {
//error_log("ReWriteAttachement CCCC TO BE UPDATED\n", 3, "$FarmD/my-errors.log");
//error_log("ReWriteAttachement CCCC page['text'] = ".$page['text']."\n", 3, "$FarmD/my-errors.log");
//error_log("ReWriteAttachement DDDD newpage['text'] = ".$newpage['text']."\n", 3, "$FarmD/my-errors.log");
    if ($doit)
    {
      UpdatePage($link, $page, $newpage);
//error_log("ReWriteAttachement DD UPDATED\n", 3, "$FarmD/my-errors.log");
    }
    return true;
  }
  return false;
}

function GetPagesAttachmentsCaseUnsensitive()
{
    global $UrlExcludeChars, $ImgExtPattern, $UploadExts, $UploadPrefixFmt, $AttachHandleCase, $FarmD;;
//error_log("GetPagesAttachmentsCaseUnsensitive AA \n", 3, "$FarmD/my-errors.log");

    $UplExtPattern = "(?:.".implode("|.", array_keys($UploadExts)).")";
//error_log("AA UplExtPattern:".$UplExtPattern."\n", 3, "$FarmD/my-errors.log");
    # we take care for attachments without an extension
    //$UplExtPattern = preg_replace("/\\|\\.(\\W)/","|[\w]\${1}",$UplExtPattern);

    $pagelist = ListPages(NULL);
    $attachlist = array();
//error_log("AA file:".$file."\n", 3, "$FarmD/my-errors.log");

    foreach ($pagelist as $page)
    {
//error_log("AA page:".$page."\n", 3, "$FarmD/my-errors.log");
//if ($page == "Intranet.CarréDeSable") {
//if ($page == "Actualité.DansLesMédiasAnciens2020") {
//error_log("AA page:".$page."\n", 3, "$FarmD/my-errors.log");

        $rcpage = ReadPage($page);
        # ignore outcommented Attachs
        $rcpage['text'] = preg_replace("/\\[[@=].*?[@=]\\]/s","",$rcpage['text']);
/*
        if (preg_match_all("/\\b(?>(Attach:\\s?))([^\\s%$UrlExcludeChars]+($ImgExtPattern|$UplExtPattern))/i", $rcpage['text'], $pageattachments, PREG_PATTERN_ORDER))
        if (preg_match_all("/\\b(?>(Attach:\\s?))([^\\s%$UrlExcludeChars]+(.gif|.jpg|.png|.pdf|.zip))/i", $rcpage['text'], $pageattachments, PREG_PATTERN_ORDER))
*/
        if (preg_match_all("/\\b(?>(Attach:\\s?|\\s|\\=\"))([^\\s%$UrlExcludeChars]+(.gif|.jpg|.png|.pdf|.zip))/i", $rcpage['text'], $pageattachments, PREG_PATTERN_ORDER))

        {
//error_log("BB\n", 3, "$FarmD/my-errors.log");
            foreach ($pageattachments[2] as $file)
            {
//error_log("\nCC page:".$page."\n", 3, "$FarmD/my-errors.log");
//error_log("CC file:".$file."\n", 3, "$FarmD/my-errors.log");
                $strippednames = FindImageFilepath($file, $page);
//error_log("CC strippednames:".implode(",", $strippednames)."\n", 3, "$FarmD/my-errors.log");
                //$index = FmtPageName("$UploadUrlFmt$UploadPrefixFmt/".$strippednames['image'], $strippednames['page']);
                $index = strtolower(FmtPageName("$UploadUrlFmt$UploadPrefixFmt/".$strippednames['image'], $strippednames['page']));
  $index = substr($index, 1);
                //if (!$AttachHandleCase)
                 //   $index = strtolower($index);
                
                //$attachlist[$index][] = $page;
//error_log("DD index:".$index."\n", 3, "$FarmD/my-errors.log");
                $attachlist[$index][] = $page;
//error_log("DD attachlist[".$index."] = ".implode(", ", $attachlist[$index])."\n", 3, "$FarmD/my-errors.log");
            }
        }
    }
//}
    return $attachlist;
}
?>