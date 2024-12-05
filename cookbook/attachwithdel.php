<?php if (!defined('PmWiki')) exit();
/*  This script adds the ability to delete uploaded attachment files

    In order for it to work the (:attachlist:) markup in Site.UploadQuickReference
    can be changed to (:newattachlist:), which will add a delete option to 
    all existing files.
    
    Author: Dan Weber (webmaster@drwhosting.net), with code contribution from the original upload.php script
   
    History
    -------
    0.02 - DW  - First usable version
    0.03 - DW  - Fixing a bug when no files are selected and "delete" is executed. Thank you Peter for reporting this
    0.04 - DW  - Version 0.03 broke more than it fixed. Now it should work fine.
    0.05 - jaf - Show upload input field after deleting an attachment (they were missing in older versions). Added by
                 jann.forrer@id.uzh.ch 
*/

XLSDV('en',[
  'ULdelsuccess' => 'successfully deleted',
  'ULdelfail' => 'failed to delete',
  'ULdelaction' => 'Delete Checked Files',
  'ULdelnofiles' => 'No files marked to delete',
  'ULsize' => 'Size',
  'ULmodified' => 'Last change',
  'ULdependencies' => 'Backlinks',
  'ULnoreffile' => 'None'
  ]);

XLSDV('fr',[
  'ULdelsuccess' => 'suppression r&eacute;ussie',
  'ULdelfail' => 'suppression &eacute;chou&eacute;e',
  'ULdelaction' => 'Supprime les fichiers coch&eacute;s',
  'ULdelnofiles' => 'Aucun fichier s&eacute;lectionn&eacute;',
  'ULsize' => 'Taille',
  'ULmodified' => 'Dernier changement',
  'ULdependencies' => 'Pages r&eacute;f&eacute;rencant ce fichier',
  'ULnoreffile' => 'Aucune'
  ]);

//SDV($AttachHandleCase, false);
SDV($AttachHandleCase, true);

//Markup('newattachlist', '<block', '/\\(:attachwithdel\\s*(.*?):\\)/ei', "FmtNewUploadList('$pagename', PSS('$1'))");
Markup('newattachlist', '<block', '/\\(:attachwithdel\\s*(.*?):\\)/i', "FmtNewUploadList");
SDVA($HandleActions, array('postdelattach' => 'HandleAttachmentDelete'));
SDVA($HandleAuth, array('postdelattach' => 'upload'));


function HandleAttachmentDelete($pagename, $auth = 'upload') {
    global $UploadDir, $UploadPrefixFmt, $PageStartFmt, $PageEndFmt, $AttachHandleCase, $Author, $CurrentTime;
    $page = RetrieveAuthPage($pagename, $auth, true, READPAGE_CURRENT);
    if (!$page) Abort("?cannot delete from $pagename");


    PCache($pagename,$page);
//error_log("HandleAttachmentDelete AAAA UploadDir = ".$UploadDir."\n", 3, "c:/temp/my-errors.log");
//error_log("HandleAttachmentDelete BBBB UploadPrefixFmt = ".$UploadPrefixFmt."\n", 3, "c:/temp/my-errors.log");
//error_log("HandleAttachmentDelete BBBB pagename = ".$pagename."\n", 3, "c:/temp/my-errors.log");

    $uploaddir = FmtPageName("$UploadDir$UploadPrefixFmt", $pagename);

    //$out = array();
    //$out[] = "<div id='wikiupload'>
    $out = "<div id='wikiupload'>
            <h2 class='wikiaction'>$[Attachments for] \$FullName</h2>
            <h3>Delete Result</h3>
            <p>";
    if (count(@$_REQUEST['files']) == 0)
    {
        //$out[] = "$[ULdelnofiles]<br>";
        $out .= "$[ULdelnofiles]<br>";
    }
    else
    {
        foreach (@$_REQUEST['files'] as $fn)
        {
            $fn = urldecode(preg_replace('/^[.\\/\\\\]*/', '', $fn));
            
            if (!file_exists($UploadDir . "/trash"))
                mkdir($UploadDir . "/trash");
            copy($uploaddir . "/" . $fn, $UploadDir . "/trash/" . $fn );

            if (@unlink($uploaddir . "/" . $fn))
            {
                //$out[] = "$fn ... $[ULdelsuccess]<br>";
                $out .= "$fn ... $[ULdelsuccess]<br>";
            }
            else
            {
                //$out[] = "$fn ... $[ULdelfail]<br>";
                $out .= "$fn ... $[ULdelfail]<br>";
            }
            error_log($CurrentTime.", ".$Author.", ".$uploaddir."/".$fn."\n", 3, $UploadDir."/deletehistory.log");
        }
    }
    //$out[] = "<br></p></div>";
    $out .= "<br></p></div>";
//error_log("HandleAttachmentDelete DDDD out = ".implode("##", $out)."\n", 3, "c:/temp/my-errors.log");
//error_log("HandleAttachmentDelete DDDD out = ".$out."\n", 3, "c:/temp/my-errors.log");
//error_log("HandleAttachmentDelete EEEE pagename = ".$pagename."\n", 3, "c:/temp/my-errors.log");
//error_log("HandleAttachmentDelete FFFF FmtPageName = ".FmtPageName($out, $pagename)."\n", 3, "c:/temp/my-errors.log");

    SDV($PageDeleteFmt,array(FmtPageName($out, $pagename), 
     $PageUploadFmt,array("
       <div id='wikiupload'>
       <h2 class='wikiaction'>$[Attachments for] {\$FullName}</h2>
       <form enctype='multipart/form-data' action='{\$PageUrl}' method='post'>
          <input type='hidden' name='n' value='{\$FullName}' />
          <input type='hidden' name='action' value='postupload' />
          <table border='0'>
            <tr><td align='right'>$[File to upload:]</td><td><input
             name='uploadfile' type='file' /></td></tr>
            <tr><td align='right'>$[Name attachment as:]</td>
            <td><input type='text' name='upname' value='' /><input
             type='submit' value=' $[Upload] ' /><br />
            </td></tr></table></form></div>"), 
     "wiki:$[Site.UploadQuickReference]"));
    SDV($HandleDeleteFmt,array(&$PageStartFmt,&$PageDeleteFmt,&$PageEndFmt));
    PrintFmt($pagename,$HandleDeleteFmt);
}


function FmtNewUploadList($m) {
    global $UploadDir, $UploadPrefixFmt, $UploadUrlFmt, $EnableUploadOverwrite,
    $TimeFmt, $EnableDirectDownload, $HandleAuth, $AttachHandleCase;
    extract($GLOBALS['MarkupToHTML']);

    $opt = ParseArgs($m[1]);
    if (@$opt[''][0]) $pagename = MakePageName($pagename, $opt[''][0]);
    if (@$opt['ext']) 
    $matchext = '/\\.(' 
      . implode('|', preg_split('/\\W+/', $opt['ext'], -1, PREG_SPLIT_NO_EMPTY))
      . ')$/i';
//error_log("FmtNewUploadList AAAA UploadDir = ".$UploadDir."\n", 3, "c:/temp/my-errors.log");
//error_log("FmtNewUploadList BBBB UploadPrefixFmt = ".$UploadPrefixFmt."\n", 3, "c:/temp/my-errors.log");
//error_log("FmtNewUploadList BBBB pagename = ".$pagename."\n", 3, "c:/temp/my-errors.log");

    $uploaddir = FmtPageName("$UploadDir$UploadPrefixFmt", $pagename);
    $uploadurl = FmtPageName(IsEnabled($EnableDirectDownload, 1) 
                          ? "$UploadUrlFmt$UploadPrefixFmt/"
                          : "\$PageUrl?action=download&amp;upname=",
                      $pagename);

    $dirp = @opendir($uploaddir);
    if (!$dirp) 
      return '';
    
    # Build the file list
    $filelist = array();
    while (($file = readdir($dirp)) !== false)
    {
        if ($file[0] == '.')
          continue;
        if (@$matchext && !preg_match(@$matchext, $file))
          continue;

        if (!$AttachHandleCase)
          $fileindex = strtolower($file);
        else
          $fileindex = $file;

        $filelist[$fileindex] = $file;
//error_log("AA filelist[".$fileindex."] = ".$filelist[$fileindex]."\n", 3, "c:/temp/my-errors.log");
    }
    closedir($dirp);
    
    # Retrieve the upload page
    $page = RetrieveAuthPage($pagename, $HandleAuth['postdelattach'], false, READPAGE_CURRENT);
    
    # Set the beginning of the output string
    $out = array();
    if ($page)
    {
        $out[] = FmtPageName("<form enctype='multipart/form-data' action='\$PageUrl' method='post'>", $pagename);
        $out[] = FmtPageName("<input type='hidden' name='n' value='\$FullName' />", $pagename);
        $out[] = FmtPageName("<input type='hidden' name='action' value='postdelattach' /><table border=0 class='zebra sortable' id=join style='width=100% font-family:Arial; font-size:13px'><tr><td>&nbsp;</td><td><strong>$[Attach file]</strong></td><td align=center><strong>$[ULsize]</strong> (bytes)</td><td align=center><strong>$[ULmodified]</strong></td><td align=center><strong>$[ULdependencies]</strong></td></tr>", $pagename);
    }

    # Sort the filelist
    asort($filelist);
    
    #Build the list of every attachment on the whole site
    $attachlist = array();
    $attachlist = GetPagesAttachments();
    
    $overwrite = '';
    $class = "ind1";
    foreach($filelist as $fileindex => $filename)
    {
        $index = FmtPageName("$UploadPrefixFmt/".$fileindex, $pagename);
        
        if (!$AttachHandleCase)
            $index = strtolower($index);

        $name = PUE("$uploadurl$filename");
        $stat = stat("$uploaddir/$filename");
        if ($EnableUploadOverwrite)
            $overwrite = FmtPageName("<a class='createlink' href='\$PageUrl?action=upload&amp;upname=$filename'>&nbsp;&Delta;</a>", $pagename);
        $delete = "";   
        if ($page)
            $delete = FmtPageName("<input type='checkbox' name='files[]' value='$filename' />", $pagename);        
       
        if (array_key_exists($index, $attachlist))
        {
            $delete = "";
            $pagelist = "";
            $attachlistcount = array_count_values($attachlist[$index]);
            foreach (array_unique($attachlist[$index]) as $link)
            {
                $counttxt = "";
                if ($attachlistcount[$link] > 1)
                    $counttxt = "($attachlistcount[$link])";
                
                $pagelist .= MakeLink($pagename, PSS($link), NULL, NULL).$counttxt.", ";
            }
            $pagelist = rtrim($pagelist, " ,");;
        }
        else
            $pagelist = FmtPageName("$[ULnoreffile]", $pagename);

        if ($class == "ind2")
            $class = "ind1";
        else
            $class = "ind2";
        $out[] = "<tr class='".$class.
                 "'><td>$delete</td><td><a href='$name' target=new>$filename</a>$overwrite</td><td align=right>".
                 $stat['size']."</td><td align=center>".date('Y/m/d, H\hi', $stat['mtime']).
                 "</td><td>".$pagelist."</td></tr>";
    }
    if ($page)
    {
        $out[] = FmtPageName("</table><br><input type='submit' value='$[ULdelaction]' />", $pagename);
        $out[] = "</form>";
    }
    return implode("\n", $out);
}

function GetPagesAttachments()
{
    global $UrlExcludeChars, $ImgExtPattern, $UploadExts, $UploadPrefixFmt, $AttachHandleCase, $UploadUrlFmt;

    $UplExtPattern = "(?:.".implode("|.", array_keys($UploadExts)).")";
    # we take care for attachments without an extension
    $UplExtPattern = preg_replace("/\\|\\.(\\W)/","|[\w]\${1}",$UplExtPattern);

    $pagelist = ListPages(NULL);
    $attachlist = array();
    
    foreach ($pagelist as $page)
    {
        $rcpage = ReadPage($page);
        # ignore outcommented Attachs
        $rcpage['text'] = preg_replace("/\\[[@=].*?[@=]\\]/s","",$rcpage['text'] ?? '');
        if (preg_match_all("/\\b(?>(Attach:\\s?))([^\\s%$UrlExcludeChars]+($ImgExtPattern|$UplExtPattern))/", $rcpage['text'], $pageattachments, PREG_PATTERN_ORDER))
        {
            foreach ($pageattachments[2] as $file)
            {
//error_log("AA file:".$file."\n", 3, "c:/temp/my-errors.log");
                $strippednames = FindImageFilepath($file, $page);
//error_log("BB strippednames:".implode(",", $strippednames)."\n", 3, "c:/temp/my-errors.log");
                $index = FmtPageName("$UploadUrlFmt$UploadPrefixFmt/".$strippednames['image'], $strippednames['page']);
                
                if (!$AttachHandleCase)
                    $index = strtolower($index);
                
//error_log("CC attachlist[".$index."] = ".$attachlist[$index]."\n", 3, "c:/temp/my-errors.log");
                $attachlist[$index][] = $page;
            }
        }
    }
    return $attachlist;
}
#*/

/**
 * Helps to find the image filepath depending on Attach syntax and $UploadPrefixFmt
 * 
 * Keep in sync with imgpopup!
 * 
 * @param string $imagename the image name only "$UploadPrefixFmt/image.ext" string
 * @param string $pagename
 * @return array array with 'image' as the new $imagename and 'page' as the new $pagename 
 * @version v1.3
 */

function FindImageFilepath($imagename, $pagename) {
    global $UploadPrefixFmt;
    $pgn = explode(".", $pagename);
    $upl = preg_split("/(\/|\.(?=.*\/))/", $imagename);
    if (isset ($upl[1])) {
        if ($UploadPrefixFmt == '/$Group')
            $pagename = $upl[0].".Upload";
        else {
            $pagename = $pgn[0].".".$upl[0];
        }
        $imagename = $upl[1];
    }
    if (isset ($upl[2])) {
        $pagename = $upl[0].".".$upl[1];
        $imagename = $upl[2];
    }
    return array ('image' => $imagename, 'page' => $pagename);
}

?>
