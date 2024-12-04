<?php

/* Copyright (C) 2023 PierreRacine.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

Description

Dynamically display the number of characters or words entered by a user 
in an input form element.

Usage

(:foxremainingchars inputname maxchars:)
(:foxremainingwords inputname maxwords:)
(:foxcharcount inputname:)
(:foxwordcount inputname:)

inputname is the value of the name attribute of the input element or textarea to refer to.

maxwords is the maximum number of words the input element should display. The number of remaining words is simply (maxwords - count of words). Note that the maximum number of words is not enforced on the input or textarea element.

maxchars is the maximum number of characters the input element should display. The number of remaining characters is simply (maxchars - count of characters). Note that the maximum number of characters is not enforced on the input or textarea element.

maxchars can also automatically be deduced from the maxlength element attribute (see https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/maxlength). In this case the maximum number of characters is enforced on the input or textarea element.

*/

Markup('foxremainingchars','directives','/\(:foxremainingchars\\s+(.*?):\)/i', "FoxRemainingChars");
Markup('foxremainingwords','directives','/\(:foxremainingwords\\s+(.*?):\)/i', "FoxRemainingWords");
Markup('foxcharcount','directives','/\(:foxcharcount\\s+(\\S*?)\\s*:\)/i', "FoxCharCount");
Markup('foxwordcount','directives','/\(:foxwordcount\\s+(\\S*?)\\s*:\)/i', "FoxWordCount");
function FoxRemainingChars($m) {
  $m = explode(" ", trim($m[1]));
  return FoxCount($m[0], false, true, $m[1]);
}

function FoxRemainingWords($m) {
  $m = explode(" ", trim($m[1]));
  if (count($m) != 2)
  {
    return Keep('<span class="'.$m[0].$suffix.'">No maximum of word provided in (:FoxRemainingWords:) markup...</span>');
  }
  return FoxCount($m[0], true, true, $m[1]);
}

function FoxCharCount($m) {
  return FoxCount($m[1], false);
}

function FoxWordCount($m) {
  return FoxCount($m[1], true);
}

function FoxCount($sourceName, $word=false, $remaining=false, $max=0) {
  global $HTMLFooterFmt;
  $suffix = "-foxcount";
  $HTMLFooterFmt["fox".$suffix] = "<script type='text/javascript'>
  function getCount(name, word=false) {
    elements = document.getElementsByName(name);
    if (elements.length > 0) {
      if (word) {
        var a = elements[0].value.trim().split(/\s+/).filter(function(x){return (x != '')});
        return a.length;
      }
      return elements[0].value.length;
    }
    return 'No input field named \"' + name + '\" was found...';
  }

  function getMaxLength(name) {
    var elements = document.getElementsByName(name);
    return elements[0].maxLength;
  }
 
  function updateCount() {
    var suffix = \"-foxcount\";
    var inputs = document.querySelectorAll('input[name$=\"' + suffix + '\"]');
    for (let i = 0; i < inputs.length; i++) {
      var sourceName = inputs[i].name.slice(0, -(suffix).length);
      var word = (inputs[i].value.substring(0, 4) == \"word\" ? true : false);
      var remaining = (inputs[i].value.substring(5, 14) == \"remaining\" ? true : false);
      var length = getCount(sourceName, word);
      if (!isNaN(length) && remaining) {
        var max = -1;
         // max is either set by the input element or as a parameter
         if (!word) {
           max = getMaxLength(sourceName);
         }
         if (max < 0) {
           max = parseInt(inputs[i].value.substring(15));
         }
         if (isNaN(max)) {
           if (word) {
             length = 'No maximum of word provided in the (:FoxRemainingWords:) markup...';
           }
           else {
             length = 'No maxlength defined in (:input:) field markup or maximum of char provided in the (:FoxRemainingChars:) markup...';
           }
         }
         else {
           length = Math.max(max - length, 0);
         }
      }
      inputs[i].nextSibling.innerHTML = length;   
    }
  }

  // for all input elements corresponding to the markup, try to find at 
  // least one source input element with the right name
  var inputs = document.querySelectorAll('input[name$=\"$suffix\"]');
  var atLeastOneSourceFound = false;
  for (let i = 0; i < inputs.length; i++) {
    // get the name of the input from which to get the length
    var name = inputs[i].name.slice(0, -('$suffix'.length));
    var sources = document.getElementsByName(name);
    if (sources.length > 0) {
      atLeastOneSourceFound = true;
      break;
    }
  }
  // set the interval function only if at least one source was found
  if (atLeastOneSourceFound) {
    setInterval(function() {
      updateCount();
    }, 300);
  }
  // otherwise just call the function once to display the error message
  else {
     updateCount();
  }
</script>";
  return Keep('<input type=hidden name="'.$sourceName.$suffix.'" value="'.($word ? "word" : "char").($remaining ? "-remaining-".$max : "").'"><span></span>');
}

?>