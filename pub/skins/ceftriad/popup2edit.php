<?php if (!defined('PmWiki')) exit();
/*  Copyright 2006 Hans Bracker. 
    This file is popup2edit.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    
    popup2edit.php is an alternative to popupedit.php
    Instead of loading styles via the html head it loads the styles via css files.
    popup2edit.css and popup2edit-noscript.css need to be copied to Farmpub/css/
*/

# set EditForm
global $PageEditForm, $HTMLHeaderFmt, $action, $_COOKIE, $javascript;
SDV($PageEditForm, 'Site.Popup-EditForm');

# set session cookie with javascript to set a flag
$HTMLHeaderFmt['javascript-cookie'] = "
  <script type='text/javascript'><!--
      document.cookie = 'javascript=true; path=/';
  --></script>
";
  
# load stylesheet for edit mode only, either for script enabled or noscript 
if ($action=='edit') {
$HTMLHeaderFmt['popupedit'] = "
  <script type='text/javascript'><!--
      document.write(\"<link href='$SkinDirUrl/css/popup2edit.css' rel='stylesheet' type='text/css' />\");
  --></script>
  <noscript>
      <link href='$SkinDirUrl/css/popup2edit-noscript.css' rel='stylesheet' type='text/css' />
  </noscript>
  ";
} 

## automatic loading of Popup-EditForm and Popup-EditQuickRef pages
#    $where = count($WikiLibDirs);
#    if ($where>1) $where--;
#    array_splice($WikiLibDirs, $where, 0, 
#        array(new PageStore("$FarmD/cookbook/popupedit/wikilib.d/\$FullName")));
