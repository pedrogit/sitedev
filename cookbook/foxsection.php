<?php

###########################################################
## Implement a copy form section button
###########################################################
XLSDV('fr',array(
  'Add' => 'Ajouter',
  'Delete' => 'Supprimer'
  ));
  
Markup('foxsectionend','<if','/\(:foxsectionend\\s*:\)/i', Keep("</div></div>"));
Markup('foxsection','<if','/\(:foxsection\\s+([\\w]+)\\s?(.*?):\)/i', "FoxSection");
Markup('foxsectioncopybutton','<if','/\(:foxsectioncopybutton\\s+([\\w]+)\\s?(.*?):\)/i', "FoxSectionCopyButton");
Markup('foxsectiondeletebutton','<if','/\(:foxsectiondeletebutton\\s?(.*?):\)/i', "FoxSectionDeleteButton");
Markup('foxsectionmoveicon','<if','/\(:foxsectionmoveicon\\s?(.*?):\)/i', "FoxSectionMoveIcon");
Markup('foxsectionnumber','<if','/\(:foxsectionnumber\\s*:\)/i', Keep("<span class='foxsectionnumber'></span>"));

function FoxSection($m) {
  global $HTMLHeaderFmt, $HTMLFooterFmt, $HTMLStylesFmt;;
  $sClassName = $m[1];
  $args = ParseArgs($m[2]);
  $dep = $args['dep'] ?? null;
  $value = $args['value'] ?? null;

  SDV($HTMLHeaderFmt['foxsection'], 
  "<script  type='text/javascript'>
    var gSectionNb = [];
    var gInsertAfterSection = [];
    var gSectionToCopy = [];
    
    function sectionCopyInit(sClassName) {
      sections = document.getElementsByClassName(sClassName);
      if (sections && sections.length > 0) {
        sections[0].style.maxHeight = '10000px';
        sections[0].style.transition = 'max-height 1s, opacity 1s';

        gSectionToCopy[sClassName] = sections[0].cloneNode(true);
        gSectionToCopy[sClassName].style.opacity = 0;
        gSectionToCopy[sClassName].style.maxHeight = '0px';
        gSectionNb[sClassName] = [1, 1];
        reNumberSection(sections[0], gSectionNb[sClassName][0], sClassName);
      }
    }
    
    function getSectionName(el){
      let section = el.closest('.foxsection');
      return section.className.split(' ')[1];
    }

    function copySection(sClassName, sMaxCopy) {
      gSectionNb[sClassName] = [gSectionNb[sClassName][0], sMaxCopy]
      if (gSectionNb[sClassName][0] < gSectionNb[sClassName][1]) {
        gSectionNb[sClassName][0]++;
        if (gInsertAfterSection[sClassName] === undefined) {
          gInsertAfterSection[sClassName] = document.getElementsByClassName(sClassName)[0];
        }
        newSection = gSectionToCopy[sClassName].cloneNode(true);
        gInsertAfterSection[sClassName].parentNode.insertBefore(newSection, gInsertAfterSection[sClassName].nextSibling);
        window.setTimeout(function() {
          newSection.style.maxHeight = '10000px';
          newSection.style.opacity = 1;
        }, 50); 

        reNumberAllSections(sClassName);
        showHideSectionByElement(newSection);
      }
    }
    
    function deleteSection(el) {
      sClassName = getSectionName(el);
      if (gSectionNb[sClassName][0] > 1) {
        toDelete = el.closest('.' + sClassName);
        window.setTimeout(function() {
          toDelete.style.maxHeight = '0px';
          toDelete.style.opacity = 0;
        }, 50); 
       window.setTimeout(function() {
          toDelete.remove();
          gSectionNb[sClassName][0]--;
          reNumberAllSections(sClassName);
        }, 1000); 
      }
    }
    
    function reNumberAllSections(sClassName) {
      sections = document.getElementsByClassName(sClassName);
      for (let i = 0; i < sections.length; i++) {
        reNumberSection(sections[i], i + 1, sClassName);
        gInsertAfterSection[sClassName] = sections[i];
      }
      copyButton = document.getElementById(sClassName + 'copybutton');
      if (copyButton) {
        copyButton.disabled = (gSectionNb[sClassName][0] == gSectionNb[sClassName][1]);
      }
    }
    
    function reNumberSection(section, number, sClassName) {
      numberSpans = section.getElementsByClassName('foxsectionnumber');
      for (let i = 0; i < numberSpans.length; i++) {
        numberSpans[i].textContent = number.toString();
      }
      renameInputNames(section, '_fsnumber_', '_fsn' + number.toString());
      renameInputNames(section, /_fsn[0-9]+/g, '_fsn' + number.toString());
      deleteButtons = section.getElementsByClassName('deletesectionbutton');
      for (let i = 0; i < deleteButtons.length; i++) {
        deleteButtons[i].style.display = (gSectionNb[sClassName][0] > 1 ? 'inline-block' : 'none')
      }
      moveIcons = section.getElementsByClassName('movesectionicon');
      for (let i = 0; i < moveIcons.length; i++) {
        if (gSectionNb[sClassName][0] > 1) {
          moveIcons[i].style.display = 'inline-block';
          var style = window.getComputedStyle(moveIcons[i], null);
          var fontSize = parseFloat(style.getPropertyValue('font-size')); 
          var svg = moveIcons[i].getElementsByTagName('svg')[0];
          svg.style.width = (fontSize)  + 'px';
          svg.style.height = (fontSize*0.8)  + 'px';
          var color = style.getPropertyValue('color');
          var g = svg.children.item('g');
          g.style.fill = color;
        }
        else {
          moveIcons[i].style.display = 'none';
        }
      }
    }
    
    function renameInputNames(el, from, to) {
      var elementsClassNameToRename = ['input', 'select', 'textarea'];
      for (name of elementsClassNameToRename) {
        inputElems = el.getElementsByTagName(name);
        for (let i = 0; i < inputElems.length; i++) {
          var name = inputElems[i].getAttribute('name');
          if (name != null) {
            inputElems[i].name = name.replaceAll(from, to);
          }
        }
      }
    }
    
    function showHideSectionByElement(el) {
      var inputElems = el.querySelectorAll('input[value=\"foxshowhide\"]');
      for (let i = 0; i < inputElems.length; i++) {
        var thisEl = inputElems[i];
        var depName = thisEl.name.substring(0, thisEl.name.indexOf('='));
        if (depName != null) {
          var depValue = thisEl.name.substring(thisEl.name.indexOf('=') + 1);
          var depElem = document.querySelectorAll('select[name=\"' + depName + '\"]');
          var height = thisEl.parentNode.getBoundingClientRect().height;
          thisEl.parentNode.style.transition = 'max-height 0.5s, opacity 0.5s';
          depElem[0].addEventListener('change', (e) => {
            var show = e.currentTarget.value == depValue;
            window.setTimeout(function() {
              thisEl.parentNode.style.opacity = (show ? 1 : 0);
            }, show ? 500 : 50);
            window.setTimeout(function() {
              thisEl.parentNode.style.maxHeight = (show ? height+'px' : '0px');
            }, show ? 50 : 500);
            window.setTimeout(function() {
              thisEl.parentNode.style.display = (show ? 'block' : 'none');
            }, show || e.init ? 0 : 1000);
          });
          var event = new Event('change');
          event.init = true;
          depElem[0].dispatchEvent(event);
        }
      }
    }

    function showHideSectionBySectionName(sClassName) {
      sections = document.getElementsByClassName(sClassName);
      if (sections && sections.length > 0) {
        showHideSectionByElement(sections[0]);
      }
    }
    
    function addRemoveDragEvents(section, remove=false, touch=false){
      var eventFct = remove ? 'removeEventListener' : 'addEventListener';
      section.setAttribute('draggable', remove ? 'false' : 'true');
      section[eventFct]('dragstart', dragStartHandler);
      section[eventFct]('dragend', dragEndHandler);

      const sectionList = section.closest('div#section-list');
      let siblings = [...sectionList.querySelectorAll(':scope > div.foxsection')];
      for (const sibling of siblings){
        sibling[eventFct](touch ? 'touchmove' : 'dragover', dragOverHandler);
      }
    }
    
    function dragMouseDown(el, touch=false) {
      section = el.closest('.foxsection');
      addRemoveDragEvents(section, false, touch);
      if (touch) {
        setTimeout(() => section.classList.add('dragging'), 0);
      }
     }
    
    function dragMouseUp(el, touch=false) {
      section = el.closest('.foxsection');
      addRemoveDragEvents(section, true, touch);      
      if (touch) {
        finishDrag(section);
      }
    }
    
    function dragStartHandler(ev) {
      setTimeout(() => this.classList.add('dragging'), 0);
    };

    function dragEndHandler(ev) {
      finishDrag(ev.currentTarget);
    };
    
    function finishDrag(section) {
      section.classList.remove('dragging'); 
      addRemoveDragEvents(section, true);
      const sClassName = section.className.split(' ')[1];
      reNumberAllSections(sClassName);
    };
    
    function dragOverHandler(ev) {
      const sectionList = ev.currentTarget.closest('div#section-list');
      const draggingItem = sectionList.querySelector('.dragging');
      let siblings = [...sectionList.querySelectorAll(':scope > div:not(.dragging)')];
      let nextSibling = siblings.find(sibling => {
        const rect = sibling.getBoundingClientRect();
        const cursorY = ev.clientY || ev.targetTouches[0].clientY;
        return cursorY <= (rect.top + rect.bottom) / 2;
      });
      sectionList.insertBefore(draggingItem, nextSibling);
    }

    </script>");

  $HTMLStylesFmt['draggable'] = "
  .dragging * {opacity:0 !important;}
  .movesectionicon {min-width:unset; cursor: grab; display:none; touch-action: none;}
  .movesectionicon svg {width:0px; height:0px;}
  .deletesectionbutton {display:none;}
  ";
  $ret = "<div id=\"section-list\" class=\"section-list-".$sClassName."\"><div class=\"foxsection ".$sClassName."\">";
  if ($dep && $value)
  {
    // section must be shown or hidden when:
    // 1) the page display the section is copied (by reNumber which set the section display based on the value of the source input element)
    // 2) the source input element is changed
    $ret .= "<input type=hidden name=\"".$dep."=".$value."\" value=\"foxshowhide\">";
    $HTMLFooterFmt[$sClassName] = "<script type='text/javascript' language='JavaScript1.2'>showHideSectionBySectionName('".$sClassName."')</script>";  }
  else {
    $HTMLFooterFmt[$sClassName] = "<script type='text/javascript' language='JavaScript1.2'>sectionCopyInit('".$sClassName."')</script>";
  }
  return Keep($ret);
}

function FoxSectionCopyButton($m) {
  $args = ParseArgs($m[2]);
  $text = isset($args['text']) ? $args['text'] : XL("Add");
  $class = isset($args['class']) ? $args['class'] : "";
  $sMaxCopy = isset($args['maxcopy']) ? $args['maxcopy'] : "";
  $ret = "<input id=\"$m[1]copybutton\" type=button value=\"$text\" class=\"$class\" onclick=\"copySection('$m[1]', $sMaxCopy)\">";
  return Keep($ret);
}

function FoxSectionDeleteButton($m) {
  $args = ParseArgs($m[1]);
  $text = isset($args['text']) ? $args['text'] : XL("Delete");
  $class = isset($args['class']) ? $args['class'] : "";
  $ret = "<input type=button value=\"$text\" class=\"$class deletesectionbutton\" style=\"display: none;\" onclick=\"deleteSection(this)\">";
  return Keep($ret);
}

function FoxSectionMoveIcon($m) {
  $args = ParseArgs($m[1]);
  $size = isset($args['size']) ? ("width: ".$args['size']."px;") : "";
  $class = isset($args['class']) ? $args['class'] : "";
  $text = isset($args['text']) ? $args["text"] : "";
    
  $ret = "<button type=button class=\"$class movesectionicon\" style=\"$size\" onmousedown=\"dragMouseDown(this)\" ontouchstart=\"dragMouseDown(this, true)\" onmouseup=\"dragMouseUp(this)\" ontouchend=\"dragMouseUp(this, true)\">
    <svg viewBox=\"0,0,100,100\" preserveAspectRatio=\"none\">
      <g fill=\"grey\">
        <rect x=\"20%\" y=\"10%\" width=\"20%\" height=\"20%\"/>
      <rect x=\"20%\" y=\"45%\" width=\"20%\" height=\"20%\"/>
      <rect x=\"20%\" y=\"80%\" width=\"20%\" height=\"20%\"/>
      <rect x=\"50%\" y=\"10%\" width=\"20%\" height=\"20%\"/>
      <rect x=\"50%\" y=\"45%\" width=\"20%\" height=\"20%\"/>
      <rect x=\"50%\" y=\"80%\" width=\"20%\" height=\"20%\"/>
    </g>
  </svg><span>$text</span></button>";
  return Keep($ret);
}

?>