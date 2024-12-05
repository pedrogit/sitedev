<?php if (!defined('PmWiki')) exit();

/*  Copyright 2006 Hans Bracker.
    This file is togglebars.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    togglebars.php is part of triad skin for pmwiki 2

    changes:
    2006-08-21: added togglebar cookie settings

*/
# defined in skin.php:
#global $EnableToggleCookies;
#SDV($EnableToggleCookies, 1);

# right and left toggle switch defaults: 1=show switch
SDV($RShow, 1);
SDV($LShow, 1);
# check cookies
global $RShowCookie, $LShowCookie, $CookiePrefix;
SDV($RShowCookie, $CookiePrefix.$SkinVersion.'_setRshow');
SDV($LShowCookie, $CookiePrefix.$SkinVersion.'_setLshow');
if ($EnableToggleCookies==1 && isset($_COOKIE[$RShowCookie])) $RShow = $_COOKIE[$RShowCookie];
if ($EnableToggleCookies==1 && isset($_COOKIE[$LShowCookie])) $LShow = $_COOKIE[$LShowCookie];


# load toggle script
global $HTMLHeaderFmt,  $HTMLStylesFmt;
$HTMLHeaderFmt['showhide'] = "
<script type='text/javascript' language='JavaScript1.2'>
    var toggleCookies = '$EnableToggleCookies';
    var rcookie = '$RShowCookie';
    var lcookie = '$LShowCookie';
    var rshow = '$RShow';
    var lshow = '$LShow';
    var show = '$[Show]';
    var hide = '$[Hide]';
</script>
<script type='text/javascript' language='JavaScript1.2' src='$SkinDirUrl/togglebars.js?2'></script>
";

## define RightToggle
global $RightToggleFmt;
if($EnableRightBarToggle==0) $RightToggleFmt = "";
if($EnableRightBarToggle==1)  {
      $RightToggleFmt = "
      <script type='text/javascript' language='JavaScript1.2'>

       document.write(\"<div id='toggleright' class='togglebox' \");
       document.write(\"value='\$[Hide] &darr;' onclick='toggleRight();'>\
          <div class='togglerightbar'></div>\
          <div class='togglerightbar'></div>\
          <div class='togglerightbar'></div>\
          </div>\");
       </script>
      ";
}

## define LeftToggle
global $LeftToggleFmt;
if($EnableLeftBarToggle==0) $LeftToggleFmt = "";
if($EnableLeftBarToggle==1)  {
      $LeftToggleFmt = "
      <script type='text/javascript' language='JavaScript1.2'>
       if (toggleLeft) {
       document.write(\"<div id='toggleleftbutton' class='togglebox' \");
       document.write(\"value='\$[Hide] &darr;' onclick='toggleLeft();'>\
          <div class='toggleleftbar'></div>\
          <div class='toggleleftbar'></div>\
          <div class='toggleleftbar'></div>\
          </div>\");
       }
      </script>
      ";
}

global $MenuToggleFmt;
$MenuToggleFmt = "
      <script type='text/javascript' language='JavaScript1.2'>
           document.write(\"<div id='togglemenubutton' class='togglebox' \");
           document.write(\"value='\$[Hide] &darr;' onclick='toggleMenu();'></div>\");
      </script>
      ";