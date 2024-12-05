<?php
###########################################################
## Implement a copy form section button
##
## foxsection works in two modes:
##
## FOR SUBMIT ONLY FORMS: There must be only one version of 
## the section. Copies are inserted dynamically with Javascript.
## Advantage is less PmWiki code and no redondant copies 
## of the base section.
##
## FOR EDITABLE FORMS: All redondant copies of the section
## must be present in the PmWiki Code so Fox can fill the 
## input elements with the editable values.
##   
###########################################################
Markup('foxsectionend','<if','/\(:foxsectionend\\s*:\)/i', Keep("</div>"));
Markup('foxsection','<if','/\(:foxsection\\s+([\\w]+)\\s?(.*?):\)/i', "FoxSection");
Markup('foxsectioncopybutton','<foxsection','/\(:foxsectioncopybutton\\s+([\\w]+)\\s?(.*?):\)/i', "FoxSectionCopyButton");
Markup('foxsectiondeletebutton','<if','/\(:foxsectiondeletebutton\\s?(.*?):\)/i', "FoxSectionDeleteButton");
Markup('foxsectionmoveicon','<if','/\(:foxsectionmoveicon\\s?(.*?):\)/i', "FoxSectionMoveIcon");
Markup('foxsectionnumber','<if','/\(:foxsectionnumber\\s*:\)/i', Keep("<span class='foxsectionnumber'></span>"));

function FoxSection($m) {
  global $HTMLHeaderFmt, $HTMLFooterFmt, $HTMLStylesFmt;;
  $sClassName = $m[1];
  $args = ParseArgs($m[2]);
  $dep = $args['dep'] ?? null;
  $value = $args['value'] ?? null;
  $sMaxCopy = (int)(isset($args['maxcopy']) ? $args['maxcopy'] : "0");


  SDV($HTMLHeaderFmt['foxsection'], 
  "<script type='text/javascript'>
    var gSectionNb = [];
    var gInsertAfterSection = [];
    var gSectionToCopy = [];
    var gButtonStyles = [];

    function resetSection(section) {
      var inputs = section.querySelectorAll('input');
      inputs.forEach(input => {
        if (input.type === 'checkbox' || input.type === 'radio') {
          input.checked = false;
        } else if (input.type !== 'hidden' && !input.disabled) {
          input.value = '';
        }
      });
    }
    
    function sectionCopyInit(sClassName, sMaxCopy) {
      sections = document.getElementsByClassName('foxsection ' + sClassName);
      // hide sections where no values are set
      for (let i = 0; i < sections.length; i++) {
        let inputs = sections[i].getElementsByTagName('input');
        let display = false;
        for (let j = 0; j < inputs.length; j++) {
          display = display || (inputs[j].value != '');
        }
        if (display || i == 0) {
          sections[i].style.opacity = '1';
          sections[i].style.maxHeight = '10000px';
          sections[i].style.display = 'block';
        }
        else {
          sections[i].style.opacity = '0';
          sections[i].style.maxHeight = '0px';
          sections[i].style.display = 'none';
        }
        sections[i].style.transition = 'max-height 1s, opacity 1s';
      }
      // save a clone of the 1s section and init the max number of copies
      if (sections && sections.length > 0) {
        gSectionToCopy[sClassName] = sections[0].cloneNode(true);
        gSectionToCopy[sClassName].style.opacity = 0;
        gSectionToCopy[sClassName].style.maxHeight = '0px';
        resetSection(gSectionToCopy[sClassName]);
        // init maxcopy from the tag (for not editable versions) or from the actual 
        // number of sections (for editable versions)
        gSectionNb[sClassName] = [1, sections.length];
        if (sMaxCopy && sMaxCopy > 0) {
          gSectionNb[sClassName] = [1, sMaxCopy];
        }
        
        reNumberAllSections(sClassName);
      }
    }
    
    function getSectionName(el){
      let section = el.closest('.foxsection');
      return section.className.split(' ')[1];
    }

    function copySection(sClassName, maxCopy) {
      if (gSectionNb[sClassName][1] == 0 || gSectionNb[sClassName][0] < gSectionNb[sClassName][1]) {
        let sections = document.getElementsByClassName('foxsection ' + sClassName);

        // search for the first hidden section. If one is found, show it. No need to add one.
        let section = null;
        for (let i = 0; i < sections.length && !section; i++) {
          if (window.getComputedStyle(sections[i], null).display == 'none') {
            section = sections[i];
          }
        }

        // if a section was found simply display it
        if (section) {
          section.style.display = 'block';
        }
        // otherwise add one by cloning the generic copy
        else
        {
          if (gInsertAfterSection[sClassName] === undefined) {
            gInsertAfterSection[sClassName] = sections[0];
          }
          section = gSectionToCopy[sClassName].cloneNode(true);
          gInsertAfterSection[sClassName].parentNode.insertBefore(section, gInsertAfterSection[sClassName].nextSibling);
        }

        // init the section
        if (section) {
          window.setTimeout(function() {
            section.style.maxHeight = '10000px';
            section.style.opacity = 1;
          }, 50);

          // show or hide showable and hidable sections inside the new copied section
          showHideSectionByElement(section);
          reNumberAllSections(sClassName);
        }
      }
    }
  
    function deleteSection(el) {
      sClassName = getSectionName(el);

      // don't delete if only one section is remaining
      if (gSectionNb[sClassName][0] > 1) {
        var toDelete = el.closest('.' + sClassName);
        window.setTimeout(function() {
          toDelete.style.maxHeight = '0px';
          toDelete.style.opacity = 0;
        }, 50); 
        window.setTimeout(function() {
          // now that the animation os over, we can remove the section
          var parent = toDelete.parentNode;
          toDelete.remove();
          toDelete.style.display = 'none';
          reNumberAllSections(sClassName);
        }, 1000); 
      }
    }
    
    function reNumberAllSections(sClassName) {
      sections = document.getElementsByClassName('foxsection ' + sClassName);

      // update the number of section displayed
      const selector = ':not([style*=\"display: none\"])';
      gSectionNb[sClassName][0] = Array.from(sections).filter(el => el.matches(selector)).length;

      // renumber each visible section
      for (let i = 0; i < sections.length; i++) {
        reNumberSection(sections[i], i + 1, sClassName);
        gInsertAfterSection[sClassName] = sections[i];
      }
      copyButton = document.getElementById(sClassName + 'copybutton');
      if (copyButton) {
        copyButton.disabled = (gSectionNb[sClassName][0] == gSectionNb[sClassName][1]);
        styleButton(copyButton, sClassName);
      }
    }
    
    function styleButton(button, sClassName) {
      button.style.display = 'inline-block';
      var style = JSON.parse(JSON.stringify(window.getComputedStyle(button, null)));
      if (gButtonStyles[sClassName] && gButtonStyles[sClassName][button.className]) {
        style = gButtonStyles[sClassName][button.className];
      }
      else {
        if (!gButtonStyles[sClassName]){
          gButtonStyles[sClassName] = [];
        }
        gButtonStyles[sClassName][button.className] = style;
      }
      var svg = button.getElementsByTagName('svg')[0];
      svg.style.width = style.fontSize;
      svg.style.height = style.fontSize;
      svg.style.verticalAlign = 'text-bottom';
      var g = svg.children.item('g');
      g.style.fill = (button.disabled ? window.getComputedStyle(button, null).color : style.color);
    }
    
    function styleInSectionButtons(section, sClassName, buttonClass) {
      buttons = section.getElementsByClassName(buttonClass);
      for (let i = 0; i < buttons.length; i++) {
        if (gSectionNb[sClassName][0] > 1) {
          styleButton(buttons[i], sClassName);
        }
        else {
          buttons[i].style.display = 'none';
        }
      }
    }
    
    function reNumberSection(section, number, sClassName) {
      numberSpans = section.getElementsByClassName('foxsectionnumber');
      for (let i = 0; i < numberSpans.length; i++) {
        numberSpans[i].textContent = number.toString();
      }
      renameInputNames(section, /_[0-9]+$/, '_' + number.toString());    
      renameInputNames(section, /_fsnumber_/, '_fsn' + number.toString());
      renameInputNames(section, /_fsn[0-9]+/, '_fsn' + number.toString());    
      styleInSectionButtons(section, sClassName, 'deletesectionbutton');
      styleInSectionButtons(section, sClassName, 'movesectionicon');
    }
    
    function renameInputNames(el, from, to) {
      var elementsClassNameToRename = ['input', 'select', 'textarea'];
      for (name of elementsClassNameToRename) {
        inputElems = el.getElementsByTagName(name);
        for (let i = 0; i < inputElems.length; i++) {
          var name = inputElems[i].getAttribute('name');
          if (name != null) {
            inputElems[i].name = name.replace(from, to);
          }
        }
      }
    }

    function showHideSectionByElement(el) {
      var inputElems = el.querySelectorAll('input[value=\"foxshowhide\"]');
      for (let i = 0; i < inputElems.length; i++) {
        let thisEl = inputElems[i];
        let depName = thisEl.name.substring(0, thisEl.name.indexOf('='));
        if (depName != null) {
          let depValues = thisEl.name.substring(thisEl.name.indexOf('=') + 1);
          depValues = depValues.split(',');
          let depElem = document.querySelectorAll('select[name=\"' + depName + '\"]');
          thisEl.parentNode.style.transition = 'max-height 0.5s, opacity 0.5s';
          depElem[0].addEventListener('change', (e) => {
            let show = depValues.includes(e.currentTarget.value);
            window.setTimeout(function() {
              thisEl.parentNode.style.opacity = (show ? 1 : 0);
            }, show ? 500 : 50);
            window.setTimeout(function() {
              let height = thisEl.parentNode.trueHeight;
              thisEl.parentNode.style.maxHeight = (show ? height+'px' : '0px');
            }, show ? 50 : 500);
            window.setTimeout(function() {
              // unset section maxHeight so its height can grow by adding new inside sections
              thisEl.parentNode.style.maxHeight = '10000px';
            }, 1000);
            window.setTimeout(function() {
              let newHeight = thisEl.parentNode.getBoundingClientRect().height;
              if (!show && (thisEl.parentNode.trueHeight === undefined || newHeight > thisEl.parentNode.trueHeight)) {
                thisEl.parentNode.trueHeight = newHeight;
              }
              thisEl.parentNode.style.display = (show ? 'block' : 'none');
            }, show || e.init ? 0 : 1000);
          });

          // trigger the change event when the form is reset
          depElem[0].closest('form').addEventListener('reset', (e) => {
            window.setTimeout(function() {
              let event = new Event('change');
              event.init = true;
              depElem[0].dispatchEvent(event);
            }, 10);
          });

          // trigger the event after creation to set the proper value
          let event = new Event('change');
          event.init = true;
          depElem[0].dispatchEvent(event);
        }
      }
    }

    function showHideSectionBySectionName(sClassName) {
      sections = document.getElementsByClassName('foxsection ' + sClassName);
      if (sections && sections.length > 0) {
        showHideSectionByElement(sections[0]);
      }
    }

    /***********************************************
     * Drag functions
     ***********************************************/
    function getSiblings(section, includeDraggable=true) {
      const sClassName = section.className.split(' ')[1];
      const form = section.closest('form');
      const selector = ':scope div.foxsection.' + sClassName + ':not([style*=\"display: none\"])' + (includeDraggable ? '' : ':not(.dragging)');
      let siblings = [...form.querySelectorAll(selector)];
      return siblings;
    }
    
    function addRemoveDragEvents(section, remove=false, touch=false){
      var eventFct = remove ? 'removeEventListener' : 'addEventListener';
      section.setAttribute('draggable', remove ? 'false' : 'true');
      section[eventFct]('dragstart', dragStartHandler);
      section[eventFct]('dragend', dragEndHandler);

      var siblings = getSiblings(section);
      for (const sibling of siblings){
        sibling[eventFct](touch ? 'touchmove' : 'dragover', dragOverHandler);
      }
    }
    
    function dragMouseDown(el, touch=false) {
      var section = el.closest('.foxsection');
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
      const overSection = ev.target.closest('.foxsection');
      const draggingSection = overSection.parentElement.querySelector('.dragging');
//console.log('draggingSection=' + draggingSection.className);
      var siblings = getSiblings(draggingSection, false);

      var snb = 0;
      let precedingSibling = siblings.find(sibling => {
        let rect = sibling.getBoundingClientRect();
        let center = (rect.top + rect.bottom) / 2;
        const cursorY = ev.clientY || ev.targetTouches[0].clientY;
        if (cursorY <= center) {
          var x = 0;
        }
//console.log('snb=' + snb++ + ' cY=' + cursorY + ' center=' + center);
        return cursorY <= center;
      });

      // insert before the found preceding sibling or after the last one
      draggingSection.parentElement.insertBefore(draggingSection, precedingSibling ? precedingSibling : siblings[siblings.length - 1].nextSibling);
    }
    //debugger;
    </script>");

  $HTMLStylesFmt['draggable'] = "
  .dragging * {opacity:0 !important;}
  .movesectionicon {cursor: grab; touch-action: none;}
  .movesectionicon svg, .deletesectionbutton svg, .copysectionbutton svg { width:0px; height:0px;}
  .deletesectionbutton, .movesectionicon, .copysectionbutton {min-width:unset;display:none;}
  ";
  //$ret = "<div id=\"section-list\" class=\"section-list-".$sClassName."\"><div class=\"foxsection ".$sClassName."\">";
  $ret = "<div class=\"foxsection ".$sClassName."\" style=\"opacity:0;\">";
  if ($dep && $value)
  {
    // section must be shown or hidden when:
    // 1) the page display the section is copied (by reNumber which set the section display based on the value of the source input element)
    // 2) the source input element is changed
    $ret .= "<input type=hidden name=\"".$dep."=".$value."\" value=\"foxshowhide\">";
    $HTMLFooterFmt[$sClassName] = "<script type='text/javascript' language='JavaScript1.2'>showHideSectionBySectionName('".$sClassName."')</script>";  }
  else {
    $HTMLFooterFmt[$sClassName] = "<script type='text/javascript' language='JavaScript1.2'>sectionCopyInit('".$sClassName."', '".$sMaxCopy."')</script>";
  }
  return Keep($ret);
}

function FoxSectionCopyButton($m) {
  $args = ParseArgs($m[2]);
  $size = isset($args['size']) ? ("width: ".$args['size']."px;") : "";
  $text = isset($args['text']) ? $args['text'] : "";
  $class = isset($args['class']) ? $args['class'] : "inputbutton";

  $ret = '<button type=button id="'.$m[1].'copybutton" class="'.$class.' copysectionbutton" style="'.$size.'" onclick="copySection(\''.$m[1].'\')">
  <svg viewBox="0,0,100,100" preserveAspectRatio="none">
    <g fill="grey">
      <rect x="38%" y="10%" width="15%" height="69%" />
      <rect x="15%" y="37%" width="60%" height="15%" />
    </g>
  </svg><span>'.$text.'</span></button>';
  return Keep($ret);
}

function FoxSectionDeleteButton($m) {
  $args = ParseArgs($m[1]);
  $size = isset($args['size']) ? ("width: ".$args['size']."px;") : "";
  $class = isset($args['class']) ? $args['class'] : "inputbutton";
  $text = isset($args['text']) ? $args['text'] : "";

  $ret = '<button type=button class="'.$class.' deletesectionbutton" style="'.$size.'" onclick="deleteSection(this)">
    <svg viewBox="0 0 100 100" preserveAspectRatio="none">
      <g fill="grey">
        <polygon points="30, 10 80, 70 70, 79 20, 20"/>
        <polygon points="70, 10 20, 70 30, 79 80, 20"/>
      </g>
    </svg><span>'.$text.'</span></button>';
  return Keep($ret);
}

function FoxSectionMoveIcon($m) {
  $args = ParseArgs($m[1]);
  $size = isset($args['size']) ? ("width: ".$args['size']."px;") : "";
  $class = isset($args['class']) ? $args['class'] : "inputbutton";
  $text = isset($args['text']) ? $args["text"] : "";
    
  $ret = '<button type=button class="'.$class.' movesectionicon" style="'.$size.'" onmousedown="dragMouseDown(this)" ontouchstart="dragMouseDown(this, true)" onmouseup="dragMouseUp(this)" ontouchend="dragMouseUp(this, true)">
    <svg viewBox="0,0,100,100" preserveAspectRatio="none">
      <g fill="grey">
        <rect x="25%" y="10%" width="20%" height="15"/>
        <rect x="25%" y="37%" width="20%" height="15%"/>
        <rect x="25%" y="64%" width="20%" height="15%"/>
        <rect x="55%" y="10%" width="20%" height="15%"/>
        <rect x="55%" y="37%" width="20%" height="15%"/>
        <rect x="55%" y="64%" width="20%" height="15%"/>
      </g>
    </svg><span>'.$text.'</span></button>';
  return Keep($ret);
}

?>