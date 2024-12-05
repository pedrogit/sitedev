<?php if (!defined('PmWiki')) exit();

/* alternative searchbox function & markup, with onfocus and onblur events
   fully capable of pmwiki's advanced pagelist and search results functions.
*/
$RecipeInfo['SearchBox2']['Version'] =  '2023-02-24';

## redefine searchbox format:
Markup('searchbox', '>links', '/\\(:searchbox(\\s.*?)?:\\)/', "SearchBox2");
function SearchBox2($m) {
  global $SearchBoxOpt, $SearchQuery, $EnablePathInfo;
  extract($GLOBALS['MarkupToHTML']);
  SDVA($SearchBoxOpt, array(
    'size'   => '20', 
    'label'  => FmtPageName('$[Search]', $pagename),
    'value'  => str_replace("'", "&#039;", $SearchQuery)));
   $opt = array_merge((array)$SearchBoxOpt, ParseArgs($m[1] ?? []));
   $focus = $opt['focus'] ?? false;
   $opt['action'] = 'search';
   if(isset($opt['target'])) $target = MakePageName($pagename, $opt['target']); 
   else $target = $pagename;
   $out = FmtPageName(" id='search-box' class='wikisearch' action='\$PageUrl' method='get'>", $target);
   $opt['n'] = IsEnabled($EnablePathInfo, 0) ? '' : $target;
   $class = $opt['class'] ?? "";
   $value = $opt['value'] ?? "";
   $size  = $opt['size'] ?? "";
   $label = $opt['label'] ?? "";
   $out .= "<input type='text' name='q' value='{$value}' class='{$class}' searchbox wikisearchbox' size='{$size}' ";
   if ($focus) $out .= " onfocus=\"if(this.value=='{$opt['value']}') this.value=''\" onblur=\"if(this.value=='') this.value='{$value}'\" ";
   $out .= " /><input type='submit' class='{$class} searchbutton' value='{$label}' />";
   foreach($opt as $k => $v) {
      if ($v == '' || is_array($v)) continue;
      if ($k=='q' || $k=='label' || $k=='value' || $k=='size') continue;
      $k = str_replace("'", "&#039;", $k);
      $v = str_replace("'", "&#039;", $v);
      $out .= "<input type='hidden' name='$k' value='$v' />";
   }
   return "<form ".Keep($out)." </form>";
}
