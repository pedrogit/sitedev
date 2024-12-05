<?php if (!defined('PmWiki')) exit();
/**
  Lowercase - Lowercase selected text in edit form
  Written by (c) Pierre Racine 2024 

  This text is written for PmWiki; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version. See pmwiki.php for full details
  and lack of warranty.
*/
# Version date
$RecipeInfo['Lowercase']['Version'] = '20240904';

XLSDV('fr',[
  'Lowercase selected text' => 'Mettre selection en minuscule',
  ]);

SDVA($GUIButtons, [
  'lowercase'=>[
  2000, '', '', '', 
  '<img src=\'$GUIButtonDirUrlFmt/lc.gif\' '
  .'alt=\'$[Lowercase]\' title=\'$[Lowercase selected text]\' '
  .'onclick=\'insMarkup(LowercaseText);\'/>'
]]);
$HTMLHeaderFmt['lowercase']= <<<EOF
<script type='text/javascript'><!--
// LowercaseText() by Pierre Racine
function LowercaseText(str) {
  return str.toLowerCase();
}
//--></script>
  
EOF;
