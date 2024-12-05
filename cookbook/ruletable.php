<?php if (!defined('PmWiki')) exit();
/*
  See https://www.pmwiki.org/wiki/Cookbook/MarkupRulesetDebugging
  Copyright (c) 2005-2024, Joachim Durchholz, Chuck Goldstein, Petko Yotov

  Redistribution and use in source and binary forms, with or without modification, 
  are permitted provided that the following conditions are met:

  1. Redistributions of source code must retain the above copyright notice, this 
  list of conditions and the following disclaimer.
  2. Redistributions in binary form must reproduce the above copyright notice, 
  this list of conditions and the following disclaimer in the documentation and/or 
  other materials provided with the distribution.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE 
  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR 
  ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON 
  ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT 
  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS 
  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

# Version date
$RecipeInfo['MarkupRulesetDebugging']['Version'] = '2024-02-26';

define('RULETABLE_VERSION', $RecipeInfo['MarkupRulesetDebugging']['Version']);

function RuleTableHtmlDump($what) {
  /* A printable, HTML-ized representation of $what.
   * Uses the following CSS classes in <span>s:
   *   .type_unset        Unset PHP variables
   *   .type_null         NULL values from databases
   *   .type_resource     PHP resources (file handles etc.)
   *   .type_syntax       parentheses, commas etc.
   *   .type_bool         boolean values
   *   .type_scalar       numbers and strings
   *   .type_controlcode  nonprintable characters in strings
   *   .type_unkown       other type (probably the effect of a bug)
   */
  if(!isset($what)) {
    return '<span class="type_unset">Unset</span>';
  } elseif(is_null($what)) {
    return '<span class="type_null">NULL</span>';
  } elseif(is_resource($what)) {
    return
      '<span class="type_resource">'
      . htmlentities(get_resource_type($what), ENT_QUOTES)
      . '</span>';
  } elseif(is_array($what) || is_object($what)) {
    $a = array();
    foreach($what as $id=>$v) {
      $a[] =
        RuleTableHtmlDump($id)
        . ' <span class="type_syntax">=></span> '
        . RuleTableHtmlDump($v);
    }
    return implode('<span class="type_syntax">,</span><br> ', $a);
  } elseif(is_bool($what)) {
    return
      '<span class="type_bool">'
      . ($what ? 'True' : 'False')
      . '</span>';
  } elseif(is_scalar($what)) {
    global $Charset, $KeepToken;
    $ktchar = $KeepToken[0]; # add $KeepToken character to list for old pmwiki versions - cgg
    $ktrepl = '\\x' . bin2hex($ktchar);
    return
      '<span class="type_scalar">'
      . str_replace(
          array(
            "\x00", "\x01", "\x02", "\x03",
            "\x04", "\x05", "\x06", "\x07",
            "\x08", "\x09", "\x0a", "\x0b",
            "\x0c", "\x0d", "\x0e", "\x0f",
            "\x10", "\x11", "\x12", "\x13",
            "\x14", "\x15", "\x16", "\x17",
            "\x18", "\x19", "\x1a", "\x1b",
            "\x1c", "\x1d", "\x1e", "\x1f",
            $ktchar),
          array(
          '<span class="type_controlcode">\\x00</span>',
          '<span class="type_controlcode">\\x01</span>',
          '<span class="type_controlcode">\\x02</span>',
          '<span class="type_controlcode">\\x03</span>',
          '<span class="type_controlcode">\\x04</span>',
          '<span class="type_controlcode">\\x05</span>',
          '<span class="type_controlcode">\\x06</span>',
          '<span class="type_controlcode">\\x07</span>',
          '<span class="type_controlcode">\\x08</span>',
          '<span class="type_controlcode">\\t</span>',
          '<span class="type_controlcode">\\n</span>',
          '<span class="type_controlcode">\\x0b</span>',
          '<span class="type_controlcode">\\x0c</span>',
          '<span class="type_controlcode">\\r</span>',
          '<span class="type_controlcode">\\x0e</span>',
          '<span class="type_controlcode">\\x0f</span>',
          '<span class="type_controlcode">\\x10</span>',
          '<span class="type_controlcode">\\x11</span>',
          '<span class="type_controlcode">\\x12</span>',
          '<span class="type_controlcode">\\x13</span>',
          '<span class="type_controlcode">\\x14</span>',
          '<span class="type_controlcode">\\x15</span>',
          '<span class="type_controlcode">\\x16</span>',
          '<span class="type_controlcode">\\x17</span>',
          '<span class="type_controlcode">\\x18</span>',
          '<span class="type_controlcode">\\x19</span>',
          '<span class="type_controlcode">\\x1a</span>',
          '<span class="type_controlcode">\\x1b</span>',
          '<span class="type_controlcode">\\x1c</span>',
          '<span class="type_controlcode">\\x1d</span>',
          '<span class="type_controlcode">\\x1e</span>',
          '<span class="type_controlcode">\\x1f</span>',
          '<span class="type_controlcode">'.$ktrepl.'</span>'),
          htmlentities($what, ENT_QUOTES, $Charset)) # add char set for new php versions - cgg
      . '</span>';
  } else {
    return '<span class="type_unknown">Unknown datatype</span>';
  }
}

function RuleTableColsCB($columns, $rule) {
 #SDV ($columns, array());
 #SDV ($rule, array());
  return array_merge($columns, $rule);
}

function RuleTableEPatCB($m) {
  return '^' . preg_quote($m[1],'/') . '(.*)' . preg_quote($m[2],'/') . '$';
}

function RuleTableHandler($pagename) {
  global $ScriptUrl;
  global $MarkupTable;
  BuildMarkupRules();
  $out = array();
  # Collect available columns
  $RuleTableColumns =
    array_keys(
      array_reduce(
        $MarkupTable, 'RuleTableColsCB', array()
      )
    );
  # Further restrict to those listed in ?columns=...
  if(isset($_REQUEST['columns'])) {
    $RuleTableColumns =
      array_intersect(
        explode(',', $_REQUEST['columns']),
        $RuleTableColumns);
  }
  $out[] = '<!DOCTYPE HTML '
             . 'PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"'
             . '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
  $out[] = '<html>';
  $out[] = '<head>';
  $out[] = '  <meta http-equiv="content-type" '
                . 'content="text/html; charset=ISO-8859-1">';
  $out[] = '  <title>Rule Table</title>';
  $out[] = '  <style type="text/css">';
  $out[] = '  <!--';
  $out[] = '    table {';
  $out[] = '      border-collapse:collapse;';
  $out[] = '      border-spacing:0;';
  $out[] = '      border:1;';
  $out[] = '      empty-cells:show;';
  if(isset($_REQUEST['columnwidth'])
    && preg_match('/^([0-9]+[a-zA-z]*)(?:,(\\w*))?$/', $_REQUEST['columnwidth'], $m))
  {
    $out[] = '      table-layout:fixed;';
  }
  $out[] = '    }';
  $out[] = '    td, th {';
  $out[] = '      padding:1px 2px;';
  $out[] = '      white-space:nowrap;';
  $out[] = '    }';
  if(isset($_REQUEST['columnwidth'])
    && preg_match('/^([0-9]+[a-zA-z]*)(?:,(\\w*))?/', $_REQUEST['columnwidth'], $m))
  {
    $out[] = '    td div.wide {';
    $out[] = '      width:' . $m[1] . ';';
    if (preg_match('/^((?:no)?)wrap$/i', $m[2], $m2) && $m2[1] === '') {
      $out[] = '      white-space:pre-wrap;';
    } else {
      $out[] = '      white-space:pre;';
      $out[] = '      overflow:auto;';
    }
    $out[] = '    }';
  }
  $out[] = '    }';
  $out[] = '    .type_unset,';
  $out[] = '    .type_null,';
  $out[] = '    .type_syntax {';
  $out[] = '      background-color:#ffffff;';
  $out[] = '    }';
  $out[] = '    .type_resource,';
  $out[] = '    .type_bool,';
  $out[] = '    .type_scalar {';
  $out[] = '      background-color:#e8e8e8;';
  $out[] = '    }';
  $out[] = '    .type_controlcode {';
  $out[] = '      background-color:#d0d0d0;';
  $out[] = '    }';
  $out[] = '    .type_unknown {';
  $out[] = '      background-color:#ff0000;';
  $out[] = '    }';
  $out[] = '    .patbad {';
  $out[] = '      color:#ff0000;';
  $out[] = '    }';
  $out[] = '  --></style>';
  $out[] = '</head>';
  $out[] = '<body>';
  $out[] = '  <!-- ' . basename(__FILE__) . ' version ' . RULETABLE_VERSION . ' -->';
 #$out[] = '  <!-- PHP Version: ' . phpversion() . ' -->';
 #$out[] = '  <div style="font-size:120%;">Markup Ruleset Debugging recipe is <a href="http://codingmaniac.com/pmwikitest/apcu/index.php?n=Cookbook.MarkupRulesetDebugging" target="_blank">here</a>.</div>';
  $out[] = '  <div style="font-size:120%;">Markup Ruleset Debugging recipe is <a href="https://www.pmwiki.org/Cookbook/MarkupRulesetDebugging" target="_blank">here</a>.</div>';
  $out[] = '  <p>Markup rules, in order of application (duplicate patterns and patterns using the deprecated /e flag are <span class="patbad">highlighted</span>):</p>';
  $out[] = '  <table border="1">';
  $out[] = '    <tr">';
  $out[] = '      <th align="left">id</th>';
  foreach($RuleTableColumns as $m) {
    $out[] = "      <th align='left'>$m</th>";
  }
  $out[] = '    </tr>';

  # Setup to retrieve replacement code from callback function cache -cgg
  global $CallbackFunctions, $CallbackFnTemplates;
  global $RuleTableBaseFilePat;
  $patterns = array();
  $lambdas = array();
  $trfunc = function ($m) {
    return sprintf('\\x%02x', ord($m[1]));
  };
  if ($CallbackFunctions) {
    foreach ($CallbackFunctions as $k=>$v) {
     #if (preg_match('/^.(lambda_\\d+)$/', (string)$v, $matches)) {
     #  $lambdas[$matches[1]] = $k;
      if (preg_match('/^(.*?lambda_\\d+)$/', (string)$v, $matches)) {
        $lambdas[preg_replace_callback('/([\x00-\x2f\x7f-\xff])/',$trfunc,$matches[1])] = $k;
      }
    }
  }
  $markupEPat = preg_replace_callback(
    '/^(.*?)%s(.*)$/',
    'RuleTableEPatCB',
    $CallbackFnTemplates["markup_e"]
  );

  foreach($MarkupTable as $id=>$m) {
    $out[] = '    <tr>';
    $out[] = '      <td>' . htmlentities($id, ENT_QUOTES) . '</td>';
    foreach($RuleTableColumns as $n) {
      $cellattr = ''; #cgg
      $prefix  = ''; #cgg
    # $comment = ''; #cgg
      $comments = array(); #cgg
      $val = @$m[@$n];
      if ($n == 'rep') {
        if (is_callable($val)) {
          if (is_a($val, 'Closure')) {
            $val = '';
            $prefix = '<span style="font-size:small; font-style:italic; color:blue;">Closure Object</span>';
          }
          elseif (preg_match('/^((.*?)lambda_\\d+)$/', (string)$val, $matches)) {
            # Retrieve replacement code from callback function cache -cgg
            $lambda = preg_replace_callback('/([\x00-\x2f\x7f-\xff])/',$trfunc,$matches[1]);
            if (isset($lambdas[$lambda])) {
              $val = $lambdas[$lambda];
              $val2 = preg_replace("/$markupEPat/s", '$1', $val, 1);
              if ($val2 !== $val) {
                $val = $val2;
                $color = ($matches[2] != "\x00") ? '#f00' : 'blue';
                $prefix = '<span style="font-size:small; font-style:italic; color:'.$color.';">Markup_e: </span>';
              }
            } else {
              $val = $lambda;
            }
          }

        }
      } 
      elseif ($n == 'pat') {
        # Highlight pattern if /e flag present -cgg
        if (preg_match('!^/(?>.*/)(?=[a-z]*$).*?e!is', strval($val))) {
          $cellattr = ' class="patbad"';
        }
        if (@$patterns[$val] && $val != '' && $val != 'Unset') {
          $comments[] = '        <div>Warning: Duplicate pattern will not work as expected.</div>';
          $cellattr = ' class="patbad"';
          ++$patterns[$val];
        } else {
          $patterns[$val] = 1;
        }
        if (isset($m['dbg'])) {
          $dbg = $m['dbg'];
          if (preg_match('/^(.*?file:\\s*)(.*?)(,\\s*line:.*?),/i', $dbg, $dbgmatches)) {
            $file = $dbgmatches[2];
            if (isset($RuleTableBaseFilePat)) {
              $file = preg_replace($RuleTableBaseFilePat, '$1', $file);
            }
            $dbg = $dbgmatches[1] . $file . $dbgmatches[3];
          }
        # $comment = '<div>' . htmlentities($dbg, ENT_QUOTES) . '</div>';
          $comments[] = '        <div>' . htmlentities($dbg, ENT_QUOTES) . '</div>';
        }
      }
      $out[] = "      <td{$cellattr}>";
      $divclass = (preg_match('/^(?:seq|dep|cmd)$/', $n) ? '' : ' class="wide"');
      $out[] = "        <div$divclass>" . $prefix . RuleTableHtmlDump($val) . '</div>';
    # if ($comment !== '') {
    #   $out[] = "        " . $comment;
    # }
      if ($comments) {
        $out = array_merge($out, $comments);
      }
      $out[] = '      </td>';
    }
    $out[] = '    </tr>';
  }

  $out[] = '  </table>';
  $out[] = '</body>';
  $out[] = '</html>';

  print implode("\n",$out);
}

SDV($HandleActions['ruletable'], 'RuleTableHandler');

