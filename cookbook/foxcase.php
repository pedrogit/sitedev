<?php if (!defined('PmWiki')) exit();
Markup('foxcase','<if','/\(:foxcase\\s+([\\w]+)\\s?(.*?):\)/i', "FoxCase");

function FoxCase($m)
{
  global $HTMLHeaderFmt;

  $args = ParseArgs($m[2]);
  $size = isset($args['size']) ? ("width: ".$args['size']."px;") : "";
  $text = isset($args['text']) ? $args['text'] : "Lowercase";
  $class = isset($args['class']) ? $args['class'] : "inputbutton";
//error_log("FoxCase 1111 size=".$size."\n", 3, "c:/temp/my-errors.log");

  $HTMLHeaderFmt['foxcase'] =
    "<script type='text/javascript'>
      function changeCase(name) {
        var inputs = document.querySelectorAll('input[name$=\"' + name + '\"], textarea[name$=\"' + name + '\"]');
        for (let i = 0; i < inputs.length; i++) {
          var start = inputs[i].selectionStart;
          var end = inputs[i].selectionEnd;
          var str = inputs[i].value;
          if (start !== undefined && end !== undefined && end !== 0 && start !== end) {
            str = str.substring(0, start) + str.substring(start, end).toLowerCase() + str.substring(end);
          }
          else str = str.toLowerCase();
          inputs[i].value = str;
        }
      }
    </script>";
  $ret = '<button type=button'.(!empty($class) ? ' class="'.$class.'"' : "").(!empty($size) ? ' style="'.$size.'"' : "").' onclick="changeCase(\''.$m[1].'\');"><span>'.$text.'</span></button>';
  return Keep($ret);
}