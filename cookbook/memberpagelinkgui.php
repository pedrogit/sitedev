<?php if (!defined('PmWiki')) exit();
/**
  MemberPageLink - Make a link to member pages
  Written by (c) Pierre Racine 2024 

  This text is written for PmWiki; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version. See pmwiki.php for full details
  and lack of warranty.
*/
# Version date
$RecipeInfo['MemberPageLink']['Version'] = '20241119';

XLSDV('fr',[
  'Link to member page' => 'Lien vers page de membre',
  ]);

SDVA($GUIButtons, [
  'memberpagelink'=>[
  212, '', '', '', 
  '<img src=\'$GUIButtonDirUrlFmt/member.gif\' '
  .'alt=\'$[Link to member page]\' title=\'$[Link to member page]\' '
  .'onclick=\'insMarkup(MemberPageLink);\'/>'
]]);
$HTMLHeaderFmt['memberpagelink']= <<<EOF
<script type='text/javascript'><!--
// MemberPageLink() by Pierre Racine
function MemberPageLink(str) {
  var regEx = /[\(\)\/\-\.\,]/g;
  var newStr = str.replace(regEx, '');
  newStr = newStr.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  var res =  "[[Membres/" + (str == newStr ? str : newStr.replaceAll(" ", "") + "|" + str) + "]]";
  return res;
}
//--></script>
  
EOF;
