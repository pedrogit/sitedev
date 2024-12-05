<?php if (!defined('PmWiki')) exit();
/*  Copyright 2014 Hans Bracker. 
    This file is skin.php; part of the Triad skin for pmwiki 2.2.56+
    you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
*/
global $FmtPV, $SkinName, $SkinVersionDate, $SkinVersionNum, $SkinVersion, $SkinRecipeName,
       $SkinSourceURL, $RecipeInfo;
# Some Skin-specific values
$SkinName = 'ceftriad';
$SkinVersionDate = '2023-05-04';
$SkinRecipeName = "TriadSkin";
$SkinVersionNum = str_replace("-","",$SkinVersionDate);
$SkinVersion = $SkinName." ".$SkinVersionDate;
$SkinSourceUrl = 'http://www.pmwiki.org/wiki/Cookbook/'.$SkinRecipeName;

# setting variables as page variables
$FmtPV['$SkinName'] = '$GLOBALS["SkinName"]';
$FmtPV['$SkinVersionDate'] = '$GLOBALS["SkinVersionDate"]';
$FmtPV['$SkinVersionNum'] = '$GLOBALS["SkinVersionNum"]';
$FmtPV['$SkinVersion'] = '$GLOBALS["SkinVersion"]';
$FmtPV['$SkinRecipeName'] = '$GLOBALS["SkinRecipeName"]';
$FmtPV['$SkinSourceUrl'] = 'PUE($GLOBALS["SkinSourceUrl"])';

# for use in conditional markup  (:if enabled TriadSkin:)
global $TriadSkin; $TriadSkin = 1;

global $DefaultColor, $DefaultFont, $DefaultPageWidth,
       $EnableStyleOptions, $EnableThemes,
       $EnableColorOptions, $EnableFontOptions, $EnablePageWidthOptions,
       $EnableGroupTitle, $EnableRightBar, $EnableMarkupShowRight,
       $EnableEmptyRightBar, $EnableRightBarToggle, $EnableLeftBarToggle,
       $EnableToggleCookies, $EnableViewSwitcher, $EnableFontSizer,$EnablePopupEditForm;

# Set default color and font scheme, and default page width style
# Variables can also be set in config.php
SDV($DefaultColor,'green'); # lightblue, silverblue, seagreen, green, gold or white
SDV($DefaultFont, 'verdana'); # verdana or georgia
SDV($DefaultPageWidth, 'wide');  # wide, 800 or border

# By default style options are enabled,
# SDV($EnableStyleOptions, 0); #disables all option setting via cookies.
SDV($EnableStyleOptions, 1);
# option switching can be disabled by type setting any of the following to zero
SDV($EnableColorOptions, 1);
SDV($EnableFontOptions, 1);
SDV($EnablePageWidthOptions, 0); # page width option switching disabled by default

# By default markup (:theme colorname fontname:)is enabled,
# SDV($EnableThemes, 0); #disables theme display.
SDV($EnableThemes, 1);

# Enables grouplink in titlebar; set to 0 for no grouplink in titlebar.
# The group name can also be hidden on pages with markup (:nogroup:)
SDV($EnableGroupTitle, 1);

# Enables default rightbar, set to 0 if no right column is needed sitewide
SDV($EnableRightBar, 1);

# enable markup (:showright:), which can show RightBar for individual pages,
# if showing of RightBar is sitewide disabled with switch $EnableRightBar = 0; above.
SDV($EnableMarkupShowRight, 1);

# Do not show empty right bar column. Only show RightBar column if RightBar exists.
# Set to 1 to show empty column when there is no RightBar page.
SDV($EnableEmptyRightBar, 0);

# adds rightbar toggle switch to topmenubar to show/hide rightbar
SDV($EnableRightBarToggle, 1);
# adds left sidebar toggle switch to topmenubar to show/hide left sidebar
SDV($EnableLeftBarToggle, 1);
# enable persistent toggle state setting via cookies
SDV($EnableToggleCookies, 1);

# add big view  - normal view switcher
SDV($EnableViewSwitcher, 0);

# add font sizer, use (:fontsizer:) markup in header or sidebar or where needed
SDV($EnableFontSizer, 0);

## adding  preview popup for edit window
SDV($EnablePopupEditForm, 1);

# array lists of available style options
global $PageColorList, $PageFontList, $PageWidthList;
SDVA($PageColorList, array (
        'lightblue' => 'c-lightblue.css',
        'seagreen' => 'c-seagreen.css',
        'green' => 'c-green.css',
        'white' => 'c-white.css',
        'silverblue' => 'c-silverblue.css',
        'gold' => 'c-gold.css',
        ));
SDVA($PageFontList, array (
        'verdana' => 'font-verdana.css',
        'georgia' => 'font-georgia.css',
        ));
SDVA($PageWidthList, array (
        'wide' => 'wide',
        '800' => '800',
        'border' => 'border',
        ));

# =========== end of configuration section of skin.php ================= #

# compatibility check with pmwiki version number
global $VersionNum, $CompatibilityNotice;
if($VersionNum < '2001016')
   $CompatibilityNotice = "<p style='color:yellow'>Compatibility problem: Please upgrade to the latest pmwiki version</p>";

# check for javascript cookie, set $javascript var for (:if enabled javascript:) switch
global $javascript;
if (isset($_COOKIE['javascript'])) $javascript = $_COOKIE['javascript'];

global $ColorCss, $FontCss, $PageWidth;
$sc = $DefaultColor;
$sf = $DefaultFont;
$sw = $DefaultPageWidth;
$ColorCss = $PageColorList[$sc];
$FontCss = $PageFontList[$sf];
$PageWidth = $PageWidthList[$sw];

# add stylechange.php for cookie setting code if set.
if ($EnableStyleOptions == 1) include_once("$SkinDir/stylechange.php");

# page width layout
global $HTMLStylesFmt;
if ($sw=='800') { $HTMLStylesFmt['fixedwidth']=" body{padding:10px 0;} \n
    #header-box { width:778px; height:90%; } \n"; };
if ($sw=='border') { $HTMLStylesFmt['border']=" body{padding:10px;} \n ";};

## automatic loading of skin default config pages
global $WikiLibDirs, $SkinDir;
    $where = count($WikiLibDirs);
    if ($where>1) $where--;
    array_splice($WikiLibDirs, $where, 0,
        array(new PageStore("$SkinDir/wikilib.d/\$FullName")));

# popup editform load switch
global $ShowHide, $PageEditForm, $SiteGroup;
if($EnablePopupEditForm==1 || $PageEditForm=='$SiteGroup.Popup-EditForm') {
    if (!$ShowHide) include_once("$SkinDir/showhide.php");
    include_once("$SkinDir/popup2edit.php");
    SDV($PageEditForm,'Site.Popup-EditForm');
   }
# set default edit form and configuration page
global $XLLangs;
SDV($PageEditForm,'Site.Triad-EditForm');
XLPage('triad', 'Site.Triad-Configuration' );
   array_splice($XLLangs, -1, 0, array_shift($XLLangs));

# load views script if enabled
if($EnableViewSwitcher==1) {
    $ViewList['big'] = 'big'; # add 'big' to list of view keywords
    SDV($ViewCookie, $CookiePrefix.$SkinName.'_setview');
    include_once("$SkinDir/views.php"); # load views script
  # set inits for 'big' view
    global $FontSizeDefault, $FontSizeIncrement, $RTInit, $LTInit;
    if(isset($View) && $View=='big') {
             $FontSizeDefault = '120'; # base size percentage
             $FontSizeIncrement = '10';      # size increment in percent
             $RShow = '0';            # no right bar initially
             $LShow = '1';            # (0=no) left bar initially
    };
};

## adds fontsizer if enabled.
# Fontsizer action links are inserted by default in Site.PageHeader
# using markup (:fontsizer:). It could be used in other places, like the SideBar.
# Remove (:fontsizer:) markup if not wanted.
if($EnableFontSizer==1) include_once("$SkinDir/fontsizer.php");

##########################################################
# This line include the pageMenu.php script to enable page toggle menus
##########################################################
include_once("$SkinDir/pagemenu.php");

# set base font size for 'big' view if fontsizer disabled
global $HTMLStylesFmt;
if($EnableFontSizer==0 && isset($View) && $View=='big') {
    //$HTMLStylesFmt[] = " body {font-size:150%} ";
}

# set TriadSkin as global variable for (:if enable TriadSkin:) markup
global $TriadSkin;
$TriadSkin = 1;

# add {$PageLogoUrl} to page variables to use on default PageHeader page
global $FmtPV;
$FmtPV['$PageLogoUrl'] = '$GLOBALS["PageLogoUrl"]';

# do not show topmenu bar if PageTopMenu is empty
$gtm = FmtPageName('$Group.PageTopMenu',$pagename);
$stm = FmtPageName('$SiteGroup.PageTopMenu',$pagename);
if (PageExists($gtm)) $page = ReadPage($gtm);
$nogtm = $nostm = 0;
if (@$page['text']=='') $nogtm = 1;
if (PageExists($stm)) $page = ReadPage($stm);
if (@$page['text']=='') $nostm = 1;
if ($nogtm==1 && $nostm==1){
#        SetTmplDisplay('PageTopMenuFmt',0);
       };
/*
if (PageExists('$Group.PageTopMenu')) $page = ReadPage('$Group.PageTopMenu');
if (PageExists('$SiteGroup.PageTopMenu')) $page .= ReadPage('$SiteGroup.PageTopMenu');
echo $page;
if(@$page['text']=='') {
        SetTmplDisplay('PageTopMenuFmt',0);
       };
*/

## use alternative searchbox markup
include_once("$SkinDir/searchbox2.php");
/*
## redefine searchbox format:
function SearchBox2($pagename, $opt) {
  global $SearchBoxFmt, $SearchBoxOpt, $SearchQuery, $EnablePathInfo, $PageSearchForm;
 SDVA($SearchBoxOpt, array(
    'size'   => '20',
    'label'  => FmtPageName('$[Search]', $pagename),
    'group'  => @$_REQUEST['group'],
    'focus'  => @$_REQUEST['focus'],
    'value'  => str_replace("'", "&#039;", $SearchQuery)));
  $opt = array_merge((array)$SearchBoxOpt, (array)$opt);
  $group = $opt['group'];
  $focus = $opt['focus'];
  $out = FmtPageName("
    id='search-box' class='wikisearch' action='\$PageUrl' method='get'><input
    type='hidden' name='action' value='search' />", $pagename);
  if (!IsEnabled($EnablePathInfo, 0))
    $out .= "<input type='hidden' name='n' value='$pagename' />";
  if ($group)
    $out .= "<input type='hidden' name='group' value='$group' />";
    $out .= "<input type='text' class='inputbox wikisearchbox'
    name='q' value='{$opt['value']}' size='{$opt['size']}' ";
  if ($focus)
    $out .= "onfocus=\"if(this.value=='{$opt['value']}') {this.value=''}\"
             onblur=\"if(this.value=='') {this.value='{$opt['value']}'}\" ";
    $out .= " /> <input class='inputbutton wikisearchbutton' type='submit' value='{$opt['label']}' /></form>";
  return "<form ".Keep($out);
}
Markup('searchbox', '>links',
  '/\\(:searchbox(\\s.*?)?:\\)/e',
  "SearchBox2(\$pagename, ParseArgs(PSS('$1')))");
*/

## set var $RightBar=1 if RightBar exists
if ($EnableRightBar==1 || $EnableMarkupShowRight==1) {
        $prb = FmtPageName('$FullName-RightBar',$pagename);
        $grb = FmtPageName('$Group.RightBar',$pagename);
        $srb = FmtPageName('$SiteGroup.RightBar',$pagename);
        if (PageExists($prb))  $RightBar = 1;
        if (PageExists($grb))  $RightBar = 1;
        if (PageExists($srb))  $RightBar = 1;
}

# empty right column logic
if ($EnableEmptyRightBar==0 && $RightBar==0) SetTmplDisplay('PageRightFmt',0);
if ($EnableEmptyRightBar==1 && $EnableRightBar==1) SetTmplDisplay('PageRightFmt',1);

# disable rightbar logic
if (!$EnableRightBar==1) SetTmplDisplay('PageRightFmt',0);

# add left & right bar toggle switches if enabled
if($EnableRightBarToggle==1 || $EnableLeftBarToggle==1) {
    include_once("$SkinDir/togglebars.php"); }

# changes to extended markup recipe for selflink definition:
global $LinkPageSelfFmt;
$LinkPageSelfFmt = "<a class='selflink'>\$LinkText</a>";

# switch to hide group-link in titlebar
global $PageGroup;
$PageGroup = FmtPageName('',$pagename);
if ($EnableGroupTitle == 1) $PageGroup = FmtPageName('$Groupspaced',$pagename);
else { $PageGroup = FmtPageName('',$pagename);

    }

#adding switch for 'Pagename-Titlebar' subpage for fancy font titlebars
$ftb = FmtPageName('$FullName-TitleBar',$pagename);
if(PageExists($ftb))  $HTMLStylesFmt[] = " .titlelink { display:none } \n ";

# Markup (:nopagegroup:) to hide group in titlebar
function NoPageGroup2() {
  global $PageGroup, $HTMLStylesFmt;
  extract($GLOBALS['MarkupToHTML']);
  $PageGroup = FmtPageName('',$pagename);
  $HTMLStylesFmt[]=" #pagegroup { display:none } \n";
  return '';
}
Markup('nogroup','directives','/\\(:nogroup:\\)/',
  "NoPageGroup2");

## markup (:noleft:)
function NoLeft2() {
    global $HideLeftFmt;
    $HideLeftFmt = "0px";
    return '';
}

Markup('noleft','directives','/\\(:noleft:\\)/', "NoLeft2");

## markup (:noright:)
function NoRight2() {
    global $HideRightFmt;
    $HideRightFmt = "0px";
    return '';
}

Markup('noright','directives','/\\(:noright:\\)/', "NoRight2");

## Markup (:showright:)
function showright() {
  SetTmplDisplay('PageRightFmt',1);
}
if ($EnableMarkupShowRight==1) {
    Markup('showright','directives','/\\(:showright:\\)/',
        "showright");
};

## Markup (:notopmenu:)
function NoTopMenu2() {
    global $HTMLStylesFmt;
    SetTmplDisplay('PageTopMenuFmt',0);
    $HTMLStylesFmt[] = "
         #header-search {border-bottom:1px solid #003466}\n ";
    return '';
}
Markup('notopmenu','directives','/\\(:notopmenu:\\)/', "NoTopMenu2");

## Markup (:nofootmenu:)
function nofootmenu() {
  SetTmplDisplay('PageFootMenuFmt', 0);
}
Markup('nofootmenu','directives','/\\(:nofootmenu:\\)/', "nofootmenu");

## Markup (:noaction:)
function NoAction2() {
    global $HTMLStylesFmt;
    SetTmplDisplay('PageFootMenuFmt', 0);
    SetTmplDisplay('PageTopMenuFmt',0);
    $HTMLStylesFmt['noaction'] = "
         #header-search {border-bottom:1px solid #003466}\n ";
    return '';
}
Markup('noaction','directives','/\\(:noaction:\\)/', "NoAction2");

## Markup (:noheader:)
function noheader() {
  SetTmplDisplay('PageHeaderFmt', 0);
}
Markup('noheader','directives','/\\(:noheader:\\)/', "noheader");

## Markup (:fullpage:)
function FullPage() {
  SetTmplDisplay('PageHeaderFmt', 0);
  SetTmplDisplay('PageTopMenuFmt',0);
  SetTmplDisplay('PageFootMenuFmt', 0);
  SetTmplDisplay('PageFooterFmt', 0);
  SetTmplDisplay('PageLeftFmt',0);
  SetTmplDisplay('PageRightFmt',0);
  return '';
}
Markup('fullpage','directives','/\\(:fullpage:\\)/',
  "FullPage");

## Markup (:theme colorname fontname:)
function SetTheme($sc,$sf) {
    global $ColorCss, $PageColorList, $FontCss, $PageFontList, $HTMLStylesFmt, $EnableThemes;
    if (@$PageColorList[$sc]) $ColorCss = $PageColorList[$sc];
    if($sf) {
     if (@$PageFontList[$sf]) $FontCss = $PageFontList[$sf];};
};

if($EnableThemes == 1) {
    Markup('theme', 'fulltext',
      '/\\(:theme\\s+([-\\w]+)\\s*([-\\w]*)\\s*:\\)/',
      "PZZ(SetTheme('$1', '$2'))");
    }
else {
    Markup('theme', 'fulltext',
      '/\\(:theme\\s+([-\\w]+)\\s*([-\\w]*)\\s*:\\)/',
      "");
    };

## add double line horiz rule markup ====
Markup('^====','>^->','/^====+/','<:block,1>
  <hr class="hr-double" />');

## removing header, title for history and uploads windows
global $action;
if ($action=='diff' || $action=='upload') {
            //SetTmplDisplay('PageHeaderFmt', 0);
            SetTmplDisplay('PageTitleFmt', 0);
    };

## alternative Diff (History) form with link in title
global $PageDiffFmt, $PageUploadFmt;
$PageDiffFmt = "<h3 class='wikiaction'>
  <a href='\$PageUrl'> \$FullName</a> $[History]</h3>
  <p>\$DiffMinorFmt - \$DiffSourceFmt - <a href='\$PageUrl'> $[Cancel]</a></p>";

## alternative Uploads form with link in title
$PageUploadFmt = array("
  <div id='wikiupload'>
  <h3 class='wikiaction'>$[Attachments for]
  <a href='\$PageUrl'> {\$FullName}</a></h3>
  <h3>\$UploadResult</h3>
  <form enctype='multipart/form-data' action='{\$PageUrl}' method='post'>
  <input type='hidden' name='n' value='{\$FullName}' />
  <input type='hidden' name='\$TokenName' value='\$TokenValue' />
  <input type='hidden' name='action' value='postupload' />
    <p align='left'>$[File to upload:]
    <input name='uploadfile' type='file' size=50 /></p>
    <p align='left' />$[Name attachment as:]
    <input type='text' name='upname' value='\$UploadName' size=25 />
    <input type='submit' value=' $[Upload] ' /></p>
    </form></div><br clear=all />",
  'wiki:$[{$SiteGroup}/UploadQuickReference]');
######

