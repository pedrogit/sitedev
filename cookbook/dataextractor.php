<?php

###########################################################
## Implement a copy form section button
###########################################################

Markup('cmeditor','directives','/\(:cmeditor\\s+([\\w]+)\\s?(.*?):\)/i', "LoadCMEditor");
function LoadCMEditor($m)
{
  global $HTMLHeaderFmt, $HTMLFooterFmt;
  $id = $m[1];
  $args = ParseArgs($m[2]);
  $class = isset($args['class']) ? $args['class'] : "";
  $style = isset($args['style']) ? $args['style'] : "";
  $cmstyle = isset($args['cmstyle']) ? $args['cmstyle'] : "";
  $mode = isset($args['mode']) ? '"'.$args['mode'].'"' : 'null';

  $HTMLHeaderFmt['cmeditor'] = '
    <script src="$FarmPubDirUrl/codemirror-5.65.16/lib/codemirror.js"></script>
    <script src="$FarmPubDirUrl/codemirror-5.65.16/addon/scroll/annotatescrollbar.js"></script>

    <script>var gCM = [];</script>
    <link rel="stylesheet" href="$FarmPubDirUrl/codemirror-5.65.16/lib/codemirror.css"/>
  ';

  $HTMLHeaderFmt['cmeditor-'.$id] = '
    <style type="text/css"><!--
    #'.$id.' > .CodeMirror {
      '.$cmstyle.'
    }
    #center-content-wrapper {
      background-color: #cfd4c8;
    }
    --></style>
  ';

  $HTMLFooterFmt['cmeditor-'.$id] = '<script type="text/javascript">
    gCM["'.$id.'"] = CodeMirror(document.getElementById("'.$id.'"), {
      mode:  '.$mode.',
      lineWrapping: true
    });
  </script>';
  
  return '<div id="'.$id.'" class="cmeditor '.$class.'" style="'.$style.'"></div>';
}

Markup('dataextractorbinder','directives','/\(:dataextractorbinder\\s?(.*?):\)/i', "DataExtractorBinder");
function DataExtractorBinder($m)
{
  global $HTMLHeaderFmt, $HTMLFooterFmt;
  $args = ParseArgs($m[1]);
  $inputsource = isset($args['inputsource']) ? $args['inputsource'] : "";
  $highlightedsource = isset($args['highlightedsource']) ? $args['highlightedsource'] : "";
  $resultingcsv = isset($args['resultingcsv']) ? $args['resultingcsv'] : "";

  SDV($HTMLFooterFmt['dataextractor'], 
  "<script type='text/javascript'>
    //gSource = '';
    var gHighlightedSourceID = '$highlightedsource';
    var gResultingCSVInputID = '$resultingcsv';
    var gMarkers = [];
    var gURL = window.location.href;

    document.getElementById('".$inputsource."').addEventListener('change', loadSourceInput); // when a new source is loaded
    document.getElementById('fieldDefsRowcopybutton').addEventListener('click', formatNewRow); // when a new field is added
    formatNewRow(); // colorize and name the first default field
    setFieldsFromURL(); // set field set from URL parameters if they exist

    // update markers and CSV as sson as the fiel set is modified
    document.getElementById('section-list').addEventListener('sectionchange', handleFieldChange);
    document.getElementById('save-csv-button').addEventListener('click', saveCSV);
    document.getElementById('reset-all-button').addEventListener('click', resetAll);

    // update URL when name or description are edited
    document.getElementById('set-name').addEventListener('input', handleFieldChange); 
    document.getElementById('set-description').addEventListener('input', handleFieldChange);

    gCM[gHighlightedSourceID].on('change', updateCSV); // when the highlighted source is edited
    var gHLContainer = document.getElementById(gHighlightedSourceID); // to refer to the right identified CodeMirror container
    new ResizeObserver(delayedUpdateMarkers).observe(gHLContainer.firstChild); // update scrollbar when resizing CodeMirror container
  </script>");
  
  SDV($HTMLHeaderFmt['dataextractor'], "
  <script type='text/javascript' src='pub/dataextractor/dataextractor.js'></script>
  <script type='text/javascript' src='pub/dataextractor/dataextractor_tests.js'></script>
  <link rel='stylesheet' type='text/css' href='pub/dataextractor/dataextractor.css'>");
  
  return "";
}
?>
