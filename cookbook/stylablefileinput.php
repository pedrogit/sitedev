<?php

###########################################################
## Implement a copy form section button
###########################################################
XLSDV('fr', [
  'No file selected' => 'Aucun fichier sélectionné',
  'Select' => 'Sélectionner'
  ]);

  $InputTags['stylablefile'] = [
	  ':html' => '<label $InputFormLabelArgs>$InputFormContent</label>&nbsp;<span>'.XL('No file selected').'</span><input type=file $InputFormArgs style="display:none;" onchange="this.previousSibling.innerHTML=this.files.item(0).name;"/>',
	  ':args' => array('name', 'label', 'class'),
	  ':attr' => array('id', 'name'),
	  ':label-attr' => array('for', 'class'),
	  ':content' => array('label'),
	  'class' => 'inputbutton',
	  'label' => XL('Select'),
	  ':fn' => 'HandleStylableFileInput'
  ];

function HandleStylableFileInput($pagename, $type, $args)
{
  global $InputTags, $InputAttrs, $FmtV, $KeepToken;
  if (!is_array($args)) $args = ParseArgs($args, '(?>([\\w-]+)[:=])');
  ##  convert any positional arguments to named arguments
  $posnames = @$InputTags[$type][':args'];
  if (!$posnames) $posnames = array('name', 'value');
  while (count($posnames) > 0 && @$args[''] && count($args['']) > 0) {
    $n = array_shift($posnames);
    if (!isset($args[$n])) $args[$n] = array_shift($args['']);
  }

  ##  merge defaults for input type with arguments
  $opt = array_merge($InputTags[$type], $args);
  $args['id'] = $args['name'];
  $opt['for'] = $args['name'];
  
  ##  build $InputFormLabelArgs from $opt
  $attrlist = (isset($opt[':label-attr'])) ? $opt[':label-attr'] : $InputAttrs;
  $attr = array();
  
  foreach ($attrlist as $a) {
    if (!isset($opt[$a]) || $opt[$a]===false) continue;
    if (is_array($opt[$a])) $opt[$a] = $opt[$a][0];
    if (strpos($opt[$a], $KeepToken)!== false) # multiline textarea/hidden fields
      $opt[$a] = Keep(str_replace("'", '&#39;', MarkupRestore($opt[$a]) ));
    $attr[] = "$a='".str_replace("'", '&#39;', $opt[$a])."'";
  }
  $FmtV['$InputFormLabelArgs'] = implode(' ', $attr);

  return Keep(InputToHTML($pagename, $type, $args, $opt));
}
?>
