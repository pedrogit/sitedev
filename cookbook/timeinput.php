<?php

###########################################################
## Implement a copy form section button
###########################################################
XLSDV('fr',array(
  'Select Time' => 'Selectionner l\'heure',
  'None' => 'Aucune'
  ));

  $InputTags['time'] = array(
  ':html' => '<select $InputFormLabelArgs $InputFormArgs $TimeAction>$TimeOptions</select>',
  //':args' => array('name', 'label', 'class', 'style'),
  ':attr' => array('id', 'name'),
  ':label-attr' => array('class', 'style'),
  ':content' => array('label'),
  'class' => 'inputbutton',
  'label' => XL('Select time'),
  ':fn' => 'HandleTimeInput');

function HandleTimeInput($pagename, $type, $args)
{
  global $InputTags, $InputAttrs, $FmtV, $KeepToken, $InputFormArgs;
  if (!is_array($args)) $args = ParseArgs($args, '(?>([\\w-]+)[:=])');
  ##  convert any positional arguments to named arguments
  /*
  $posnames = @$InputTags[$type][':args'];
  if (!$posnames) $posnames = array('name', 'value');
  while (count($posnames) > 0 && @$args[''] && count($args['']) > 0) {
    $n = array_shift($posnames);
    if (!isset($args[$n])) $args[$n] = array_shift($args['']);
  }
  */

  //while (isset($args['']) && count($args['']) > 0) {
  //  $args[array_shift($args[''])] = true;
  //}

  ##  merge defaults for input type with arguments
  $args['id'] = $args['name'] ?? "";
  $opt = array_merge($InputTags[$type], $args);
  $opt['for'] = $args['name'] ?? "";

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

  // build the list of time
  $FmtV['$TimeOptions'] = '<option value="">'.XL('Select Time').'</option>';
  $FmtV['$TimeOptions'] .= "<option value=\"\" $InputFormArgs>".XL('None')."</option>";
  for ($h=0; $h < 24; $h++) {
    $hour = str_pad($h, 2, "0", STR_PAD_LEFT);
    for ($m=0; $m < 60; $m = $m + 15) {
        $minute = str_pad($m, 2, "0", STR_PAD_LEFT);
        $FmtV['$TimeOptions'] .= "<option value=\"$hour:$minute\" $InputFormArgs>$hour:$minute</option>";
    }
  }

  // add a script to assign the selected time to another input element
  if (isset($opt['field'])){
    $FmtV['$TimeAction'] = "onchange='var targetField = document.getElementsByName(\"".$opt['field']."\").item(0);if (targetField) targetField.value = this.value;'";
  }

  return Keep(InputToHTML($pagename, $type, $args, $opt));
}
?>
