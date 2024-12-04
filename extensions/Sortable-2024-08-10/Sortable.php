<?php if (!defined('PmWiki')) exit(); # very strongly recommended
/**
  ExtensionName for PmWiki
  Written by (c) Pierre Racine 2023-2024  https://www.cef-cfr.ca/pmwiki.php?n=Membres.PierreRacine
  License: MIT, see file LICENSE
*/
$RecipeInfo['ExtensionName']['Version'] = '2024-02-24';

Markup('sortableval', 'directives', '/\\(:sortableval\\s+([a-z0-9-]+)\\s+\\"(.*?)\\"(?:\\s+(show))?\\s*:\\)/i', "sortableVal");
function sortableVal($m) {
  $out = "<span".(($m[3] ?? "") !== "show" ? " style='display:none;'" : "")." data-sortfield=$m[1]>$m[2]</span>";
  return Keep($out);
}

// (:sortable sectionid field1-="Label for 1st sort option" field2-="Label for 2nd sort option":)
Markup('sortable', '<input', '/\\(:sortable\\s+(\\S.*?):\\)/i', "sortable");
// main function for csv display
function sortable ($m) {
  global $HTMLFooterFmt;
  extract($GLOBALS['MarkupToHTML']);
  $args = ParseArgs($m[1], '(?>([\\w-]+)[:=])');
  $sectionId = $args[''][0];
  $markup = "";
  $noneFound = FALSE;
  if (count($args) > 2) {
    // create a SELECT OPTION for each sort option
    foreach ($args as $field => $label) {
      if (!is_array($label)){
        $noneFound = $noneFound || ($field === 'none');
        $markup .= "
<option value='$field'>$label</option>
";
      }
    }

    if (!$noneFound) {
      $markup = "
<option value='none'>".XL("Default order")."</option>
".$markup;
    }
    $markup = "
<select class='inputbox noprint' name='sortable-$sectionId'>
".$markup."
</select>
";
    // add a js script that attach an even listener on all SELECT input element
    $HTMLFooterFmt['sortables'] = "<script type='text/javascript' language='JavaScript1.2'>
      var selectElems = document.querySelectorAll('select[name|=\"sortable\"]');
      for (let i = 0; i < selectElems.length; i++) {
        selectElems[i].addEventListener('change', (e) => {
          sortableSort(e.currentTarget.name, e.currentTarget.value);
        });
      }
    </script>";
    
  }
  return Keep($markup);
}

function MyExtensionInit($pagename) {
  extAddHeaderResource('sortable.js');
}

MyExtensionInit($pagename);