String.prototype.replaceAt = function (str, repl, idx) {
  var firstPart = this.substring(0, idx);
  var remainingPart = this.substring(idx, this.length);
  var pos = remainingPart.indexOf(str);
  var result = firstPart;
  if (pos > -1) {
    result = result + remainingPart.substring(0, pos) + repl;
  }
  result = result + remainingPart.substring(pos + str.length, remainingPart.length);
  return result;
}

getSisterColors = () => {
  var hue = 360 * Math.random();
  var sat = 25 + 70 * Math.random();
  var col1 = "hsl(" + hue + ',' + sat + '%,' +
    (85 + 10 * Math.random()) + '%)';
  var col2 = "hsl(" + hue + ',' + sat + '%,' +
    (65 + 10 * Math.random()) + '%)';
  return [col1, col2]
}

// search for a string in normal or regex mode
// returns an object
//   str: the found string
//   index: the position of the found string
//   lastIndex: the position of the end of the found string
String.prototype.findFirstAt = function (search, pos = 0, regex = false) {
  var result = { str: null, index: 0, lastIndex: 0 };
  pos = (pos < 0 ? 0 : pos);
  if (regex && search && search != '') {
    try {
      const startRE = new RegExp(search, 'g');
      foundArr = startRE.exec(this.substring(pos));
      if (foundArr) {
        result.str = foundArr[0];
        result.index = pos + foundArr.index;
        result.lastIndex = pos + startRE.lastIndex;
      }
    } catch (e) {
    }
  }
  else if (search && search != '') {
    var idx = this.substring(pos).indexOf(search);
    if (idx > -1) {
      result.str = search;
      result.index = pos + idx;
      result.lastIndex = pos + idx + search.length;
    }
  }
  return result;
}

function isEmpty(obj) {
  if (!obj || (obj &&
    Object.keys(obj).length === 0 &&
    Object.getPrototypeOf(obj) === Object.prototype)) {
    return true;
  }
  return false;
}

var getLineNbAndEndPos = (source, startPos, endPos) => {
  var lineStr = source.substring(startPos, endPos);
  var nbNL = (lineStr.match(/\n/g) || []).length;
  var lastNLIdx = lineStr.lastIndexOf('\n');
  lastNLIdx = lastNLIdx < 0 ? 0 : lastNLIdx + 1;
  var markPos = endPos - startPos - lastNLIdx;
  return {line: nbNL, ch: markPos};
}

var generateMarkObj = (source, currentPos, markLine, markCh, currentFind, start = true) => {
  var newMarkText = {};
  var found = (start ? currentFind.start : currentFind.end);

  mark = getLineNbAndEndPos(source, currentPos, found.index)
  markLine += mark.line;
  markCh = (mark.line > 0 ? 0 : markCh) + mark.ch;
  newMarkText.start = {line: markLine, ch: markCh};

  mark = getLineNbAndEndPos(source, found.index, found.lastIndex)
  markLine += mark.line;
  markCh = (mark.line > 0 ? 0 : markCh) + mark.ch;
  newMarkText.end = {line: markLine, ch: markCh};

  newMarkText.class = {className: "cm-" + currentFind.fieldName + (start ? "_start" : "_end")}; // .cm-field1_start

  return {mark: newMarkText, line: markLine, ch: markCh};
}

var generateCSV = (source = '', fieldDefsArr = []) => {
  var someThingFound;
  var currentPos = 0;
  var lastRow = {};
  var fieldNames = [];
  
  //var highLightedSource = source;
  /*
  var highLightedSource = source ? source.replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('\n', '<br>') : '';
  */
    
  var selectedPos = 0;
  var lastFieldName = '';
  var firstWasFound = false;

  // create an array of all field names
  fieldDefsArr.forEach((fieldDef) => {
    fieldNames.push(fieldDef.fieldName);
  });

  // begin writing the CSV file
  var resultCSV = fieldNames.length > 0 ? fieldNames.join(";") + ";\n" : "";

  // initialise the markText array of objects
  markTextArr = [];
  markLine = 0;
  markCh = 0;

  /* logic goes like this:

   - find the first start delimiter and the corresponding end delimiter
   - set the current position to the end of this find
   - extract the value
   - remember the value till the next find in case it gets repeated
   - if the found field is not the same as the previous one add the last one to the current row
   - if the found fied is a start field, save the last row (and reset the currnt one)
  */

  var fieldIdx = 0;
  do {
    someThingFound = false;
    var bestFoundPos = source ? source.length : 0;
    var currentFind = {};
    var start = {};

    // search for next closest delimiter starting with the lst found field (to allow for repeatitions).
    // stop after finding one.
    // i.e. the following sequence: <1>a</1><2>b</2><2>b</2><3>c</3><2>b</2><1>e</1>
    //      will result in 'f1;f2;f3;\na;b, b;c;\ne;;;\n'
    //      not 'f1;f2;f3;\na;b, b, b;c;\ne;;;\n'
    // have a look at test below for more examples
    if (fieldDefsArr.length > 0) {
      currentFind = {};
      var notLastFound = false;
      var searchIdx = fieldIdx % fieldDefsArr.length;
      do {
        var currentField = fieldDefsArr[searchIdx % fieldDefsArr.length];
        start = source.findFirstAt(currentField.start, currentPos, true);
        if (start.str && start.index < bestFoundPos) {
          someThingFound = true;
          bestFoundPos = start.index;

          currentFind = {};
          currentFind.fieldName = currentField.fieldName;
          currentFind.start = start;
          currentFind.start.col = currentField.startCol;

          var end = source.findFirstAt(currentField.end, start.lastIndex, true);
          if (end.str !== null) {
            currentFind.end = end;
            currentFind.end.col = currentField.endCol;
          }

          if (searchIdx > fieldIdx) {
            notLastFound = true;
          }

          fieldIdx = searchIdx;
        }
        searchIdx++;
      }
      while (searchIdx <= fieldDefsArr.length && !notLastFound)
    }

    if (!isEmpty(currentFind)) {
      // highlight the find
      if (!isEmpty(currentFind.start) && currentFind.start.str) {
        // create markText objects for CodeMirror
        newMarkObj = generateMarkObj(source, currentPos, markLine, markCh, currentFind, true);
        markTextArr.push(newMarkObj.mark);
        markLine = newMarkObj.line;
        markCh = newMarkObj.ch;

        currentPos = currentFind.start.lastIndex;

        if (!isEmpty(currentFind.end)) {
          // if the current field is a starter field, push the last row in the data array
          // and reset the lastRow object
          if (currentFind.fieldName == fieldDefsArr[0].fieldName) {
            firstWasFound = true;
            if (!isEmpty(lastRow)) {
              fieldNames.forEach((name) => {
                resultCSV += lastRow[name] ? lastRow[name] + ';' : ";"
              });
              resultCSV += '\n';
              lastRow = {};
            }
          }

          newMarkObj = generateMarkObj(source, currentPos, markLine, markCh, currentFind, false);
          markTextArr.push(newMarkObj.mark);
          // if the field does not repeat itself just add it
          if (firstWasFound) {
            // create markText objects for CodeMirror
            markLine = newMarkObj.line;
            markCh = newMarkObj.ch;

           // extract the value
            var value = source.substring(currentFind.start.lastIndex, currentFind.end.index);
            // if a separator or a newline char is found in the value, escape double quotes and double quote the value
            if (value.indexOf(';') > -1 || value.indexOf('\n') > -1) {
              value = '"' + value.replaceAll('"', '""') + '"';
            }
            
            // add it as a repeated value or not
            if (lastRow[currentFind.fieldName] == undefined) {
              lastRow[currentFind.fieldName] = value;
              lastFieldName = currentFind.fieldName;
            }
            else if (currentFind.fieldName == lastFieldName) {
              if (lastRow[currentFind.fieldName] == '') {
                lastRow[currentFind.fieldName] = value;
              }
              else if (value != '') {
                lastRow[currentFind.fieldName] = lastRow[currentFind.fieldName] + ', ' + value;
              }
            }
            else {
              firstWasFound = false;
            }
            // update the pointer
            currentPos = currentFind.end.lastIndex;
          }
          else {
            fieldIdx = 0;
          }
        }
      }
    }
  } while (someThingFound);

  // add the remaining values
  if (!isEmpty(lastRow)) {
    fieldNames.forEach((name) => {
      resultCSV += lastRow[name] ? lastRow[name] + ';' : ";"
    });
    resultCSV += '\n';
  }

  return {
    csv: resultCSV,
    markers: markTextArr
  };
}

var getFieldDef = () => {
  var fields = [];
  var allRows = document.getElementsByClassName('foxsection fieldDefsRow');
  Array.from(allRows).forEach(function (row) {
    var newFieldDefObj = {};

    newFieldDefObj['fieldName'] = row.querySelector("input[name='fieldname']").value;

    var input = row.querySelector("input[name='start']");
    newFieldDefObj['start'] = input.value;
    newFieldDefObj['startCol'] = window.getComputedStyle(input).getPropertyValue('background-color');

    var input = row.querySelector("input[name='end']");

    newFieldDefObj['end'] = input.value;
    newFieldDefObj['endCol'] = window.getComputedStyle(input).getPropertyValue('background-color');

    fields.push(newFieldDefObj);
  });

  var name = document.getElementById('set-name').value;
  var description = document.getElementById('set-description').value;

  return {
    name: name, 
    description: description, 
    fields: fields
  };
}

var addColors = (source, fieldDefs) => {
  fieldDefs.forEach((field) => {
    if (field.start != '') {
      const matches = source.matchAll(field.start);
      var pos = 0;
      for (const match of matches) {
        const repl = '<span style="background-color: ' + field.startCol + '">' + match[0] + '</span>';
        pos = pos + match.index;
        source = source.replaceAt(match[0], repl, pos);
        pos = pos + repl.length - match[0].length;
      }
    }
  });
  return source;
}

var handleFieldChange = () => {
  updateCSV();
  updateURL();
}

var updateStyle = () => {
  var fieldDef = getFieldDef();
  // add the stylesheet if it does not exist
  var style = document.getElementById(gHighlightedSourceID + '-style');
  if (!style){
    style = document.createElement('style');
    style.id = gHighlightedSourceID + '-style'; //.cm-field1_start
    style.type = 'text/css';
    head = document.head || document.getElementsByTagName('head')[0];
    head.appendChild(style);
  }
  css = '';
  fieldDef.fields.forEach(field => {
    if (field.start && field.start != "")
    {
      css += '.cm-' + field.fieldName + '_start {background-color:' + field.startCol + '}\n';
      css += '.cm-' + field.fieldName + '_start_scroll {background-color:' + field.startCol + '; opacity: 0.5;}\n';
    }
    if (field.end && field.end != "")
    {
      css += '.cm-' + field.fieldName + '_end {background-color:' + field.endCol + '}\n';
      css += '.cm-' + field.fieldName + '_end_scroll {background-color:' + field.startCol + '; opacity: 0.5;}\n';
    }
  });
  style.innerHTML = css;
}

const updateMarkers = () => {
  // clear current markers
  if (gCM[gHighlightedSourceID].markers)
  {
    gCM[gHighlightedSourceID].markers.forEach(function (mark) {
      mark.clear();
    });      
  }

  // clear current annotations
  if (gCM[gHighlightedSourceID].annotations)
  {
    gCM[gHighlightedSourceID].annotations.forEach(function (ann) {
      ann.clear();
    });      
  }

  // apply new markers and create the arrays of scrollbar annotation
  gCM[gHighlightedSourceID].markers = [];
  var annotations = {};
  gMarkers.forEach(function (mark) {
    gCM[gHighlightedSourceID].markers.push(gCM[gHighlightedSourceID].markText(mark.start, mark.end, mark.class));
    if (annotations[mark.class.className] == undefined) {
      annotations[mark.class.className] = [];
    }
    annotations[mark.class.className].push({from: mark.start, to: mark.end});

  });

  gCM[gHighlightedSourceID].annotations = [];
  for (let key in annotations) {
    var annotation =  gCM[gHighlightedSourceID].annotateScrollbar({className: key + '_scroll'});
    annotation.update(annotations[key]);
    gCM[gHighlightedSourceID].annotations.push(annotation);
  }

  return true;
}

// ensure we update scrollbar annotations only when the resizing of the box is finished
var gDelayedUpdateMarkers;
var delayedUpdateMarkers = () => {
  clearTimeout(gDelayedUpdateMarkers);
  gDelayedUpdateMarkers = setTimeout(() => {
    gCM[gHighlightedSourceID].operation(updateMarkers);
}, 300);
}

var gDelayedGenerateCSV;
const updateCSV = () => {
  gCM[gResultingCSVInputID].setValue('');
  [...document.getElementsByClassName("de-processing-wheel")].forEach((element, index, array) => {
    element.style.display = 'inline';
  });
  
  clearTimeout(gDelayedGenerateCSV);
  gDelayedGenerateCSV = setTimeout(() => {
    updateStyle();
    gMarkers = [];
    var result = generateCSV(gCM[gHighlightedSourceID].getValue(''), getFieldDef().fields);
    gCM[gResultingCSVInputID].setValue(result.csv);
    gMarkers = result.markers;
    gCM[gHighlightedSourceID].operation(updateMarkers);

    [...document.getElementsByClassName("de-processing-wheel")].forEach((element, index, array) => {
      element.style.display = 'none';
    });
  }, 50);
};

// borrowed from https://stackoverflow.com/questions/1293147/how-to-parse-csv-data
function parseCSV(str, sep = ',') {
  var arr = [];
  var quote = false;  // 'true' means we're inside a quoted field
  var newcol = false;
  // Iterate over each character, keep track of current row and column (of the returned array)
  for (var row = 0, col = 0, c = 0; str && c < str.length; c++) {
    var cc = str[c], nc = str[c + 1];        // Current character, next character

    // If the current character is a not a newline (LF or CR) and we are not in a quoted field create a new column
    if ((cc != '\r' && cc != '\n' && !quote) || newcol) {
      arr[row] = arr[row] || [];             // Create a new row if necessary
      arr[row][col] = arr[row][col] || '';   // Create a new column (start with empty string) if necessary
      newcol = false;
    }

    // If the current character is a quotation mark, and we're inside a
    // quoted field, and the next character is also a quotation mark,
    // add a quotation mark to the current column and skip the next character
    if (cc == '"' && quote && nc == '"') { arr[row][col] += cc; ++c; continue; }

    // If it's just one quotation mark, begin/end quoted field
    //if (cc == '"') { quote = !quote; continue; }
    //if (cc == sep && nc == '"' && !quote) { quote = true; ++col; ++c; continue; }
    if (cc == sep && nc == '"' && !quote) { quote = true; newcol = true; ++col; ++c; continue; }

    if (cc == '"' && (nc == '\n' || nc == '\r') && quote) { quote = false; ++row; col = 0; ++c; continue; }
    if (cc == '"' && (nc == sep || nc == undefined) && quote) { quote = false; ++col; ++c; continue; }

    // If it's a comma and we're not in a quoted field, move on to the next column
    if (cc == sep && !quote) { ++col; continue; }

    // If it's a newline (CRLF) and we're not in a quoted field, skip the next character
    // and move on to the next row and move to column 0 of that new row
    if (cc == '\r' && nc == '\n' && !quote) { ++row; col = 0; ++c; continue; }

    // If it's a newline (LF or CR) and we're not in a quoted field,
    // move on to the next row and move to column 0 of that new row
    if (cc == '\n' && !quote) { ++row; col = 0; continue; }
    if (cc == '\r' && !quote) { ++row; col = 0; continue; }

    // Otherwise, append the current character to the current column
    arr[row][col] += cc;
  }
  return arr;
}

var updateURL = () => {
  var fieldDef = getFieldDef();
  var parsedURL = new URL(gURL);
  var newURL = parsedURL.pathname + "?n=" + parsedURL.searchParams.get("n");
  if (fieldDef.name && fieldDef.name != "") {
    newURL += "&name=" + encodeURIComponent(fieldDef.name);
    document.getElementById('set-name-show').innerHTML = fieldDef.name;
    document.title = 'CEF RegEx Data Extractor - ' + fieldDef.name;
  }
  if (fieldDef.description && fieldDef.description != "") {
    newURL += "&description=" + encodeURIComponent(fieldDef.description);
    document.getElementById('set-description-show').innerHTML = fieldDef.description.replaceAll(/\n/g, '<br>');
  }
  fieldDef.fields.forEach((field, idx) => {
    if (field.fieldName && field.fieldName != "") {
      newURL += "&f" + idx + "n=" + encodeURIComponent(field.fieldName);
    }
    if (field.start && field.start != "") {
      newURL += "&f" + idx + "s=" + encodeURIComponent(field.start);
    }
    if (field.end && field.end != "") {
      newURL += "&f" + idx + "e=" + encodeURIComponent(field.end);
    }
  });
  window.history.pushState("object or string", "Title", newURL);
}

var getAppLanguage = () => {
  return document.getElementById('pagelanguage').innerText;
}

var setFieldsFromURL = () => {
  var parsedURL = new URL(gURL);
  var fieldNames = [];
  var fieldStarts = [];
  var fieldEnds = [];
  var idxs = [];
  
  var name = parsedURL.searchParams.get('name');
  var description = parsedURL.searchParams.get('description');

  document.getElementById('set-name').value = name ? decodeURIComponent(name) : '';
  var defaultName = getAppLanguage() == 'FR' ? 'Aucun nom' : 'No name'
  document.getElementById('set-name-show').innerHTML = name ? decodeURIComponent(name) : defaultName;

  var defaultDescription = getAppLanguage() == 'FR' ? 'Aucune description' : 'No description'
  document.getElementById('set-description').value = description ? decodeURIComponent(description) : '';
  document.getElementById('set-description-show').innerHTML = description ? decodeURIComponent(description).replaceAll(/\n/g, '<br>') : defaultDescription;

  parsedURL.searchParams.forEach((value, key) => {
    var match = key.match(/^f([0-9]+)(n|s|e)$/);
    if (match) {
      var idx = Number(match[1]);
      //idxs.push(idx);
      idxs[idx] = idx;
      switch (match[2]) {
        case "n":
          fieldNames[idx] = decodeURIComponent(value);
          break;
        case "s":
          fieldStarts[idx] = decodeURIComponent(value);
          break;
        default:
          fieldEnds[idx] = decodeURIComponent(value);
      }
    }
  });
  var csv = "fieldNames,starts,startColors,ends,endsColors,\n";
  idxs.forEach((value, key) => {
    csv += "\"" + (fieldNames[value] ? fieldNames[value] : "") + "\"," + 
           "\"" + (fieldStarts[value] ? fieldStarts[value] : "") + "\"," + 
           "\"#900000\"," + 
           "\"" + (fieldEnds[value] ? fieldEnds[value] : "") +  "\"," + 
           "\"#900000\",\n";
  });
  setFieldsFromCSV(parseCSV(csv));
}

var trimEnclosing = (str, ch) => {
  var newStr = str;
  var rxs = new RegExp('^'+ ch);
  var rxe = new RegExp(ch +'$');
  if (rxs.test(newStr) && rxe.test(newStr))
  {
    newStr = newStr.replace(rxs, '').replace(rxe, '');
  }
  return newStr;
}

/*
x = trimEnclosing("atotob", "a");
x = trimEnclosing("atotoa", "a");
x = trimEnclosing("aatotoaa", "a");
x = trimEnclosing("xxtotoax", "x");
x = trimEnclosing("xxtotoax", "xx");
x = trimEnclosing("xxtotoaxx", "xx");
*/

var setFieldsFromCSV = (csv) => {
  if (csv.length > 1)
  {
    // validate the csv
    if (JSON.stringify(csv[0]) !== JSON.stringify(["fieldNames", "starts", "startColors", "ends", "endsColors"])) {
      alert('Invalid CSV');
      return;
    }
    // make sure the number of field in the interface is ok
    var fieldRowsCnt = document.getElementsByClassName('foxsection fieldDefsRow').length;

    while (fieldRowsCnt != csv.length - 1) {
      if (fieldRowsCnt < csv.length - 1) {
        // add a row
        document.getElementById("fieldDefsRowcopybutton").click();
        fieldRowsCnt++;
      }
      else {
        // delete a row
        document.getElementById("fieldDefsRowdeletebutton").click();
        fieldRowsCnt--;
      }
    }
    
    var allFieldRows = document.getElementsByClassName('foxsection fieldDefsRow');

    // distribute the values
    var arrIdx = 1;
    Array.from(allFieldRows).forEach(function (deleteButton) {
      var parent = deleteButton.closest(".fieldDefsRow");
      parent.querySelector("input[name='fieldname']").value = trimEnclosing(csv[arrIdx][0], '"');
      parent.querySelector("input[name='start']").value = trimEnclosing(csv[arrIdx][1], '"');
      //parent.querySelector("input[name='start']").style.cssText = 'background-color:' + trimEnclosing(csv[arrIdx][2], '"');
      parent.querySelector("input[name='end']").value = trimEnclosing(csv[arrIdx][3], '"');
      //parent.querySelector("input[name='end']").style.cssText = 'background-color:' + trimEnclosing(csv[arrIdx][4], '"');
      arrIdx++;
    });

    updateCSV();
  }
}

//////////////////////////////////////////////////////////////////////////////
// formatNewRow
// format the last added row
var formatNewRow = (addInputEvent = true, fieldName = undefined) => {
  var allRows = document.getElementsByClassName("foxsection fieldDefsRow");
  var newRow = allRows[allRows.length - 1];
  if (addInputEvent){
    // add the oninput event listener
    var changingfields = newRow.getElementsByClassName("changingfield");
    Array.from(changingfields).forEach(function (element) {
      element.addEventListener('input', handleFieldChange);
    });
  }
  
  var parent = document.querySelector('#section-list.section-list-fieldDefsRow');

  // assign background color to expression inputs
  var cols = getSisterColors();
  newRow.querySelector("input[name='start']").style.cssText = 'background-color:' + cols[1];
  newRow.querySelector("input[name='end']").style.cssText = 'background-color:' + cols[0];

  // reset delimiters
  newRow.querySelector("input[name='start']").value = '';
  newRow.querySelector("input[name='end']").value = '';

  // increment the field name
  var finalName = 'field' + (fieldName ? fieldName : parent.childElementCount);
  var fieldDef = getFieldDef();
  fieldDef.fields.forEach((field) => {
    if (finalName == field.fieldName)
    {
      finalName += '_' + parent.childElementCount;
    }
  });
  newRow.querySelector("input[name='fieldname']").value = finalName;
}

//////////////////////////////////////////////////////////////////////////////
// Load Source File
var loadSourceInput = (e) => {
  var file = e.target.files[0];
  if (!file) {
    return;
  }
  var reader = new FileReader();
  reader.onload = function (input) {
    gCM[gHighlightedSourceID].setValue(input.target.result);
    e.target.value = '';
  };
  reader.readAsText(file);
}

//////////////////////////////////////////////////////////////////////////////
// Save CSV Result
var saveCSV = () => {
  var source = gCM[gResultingCSVInputID].getValue();

  var csvblob = new Blob([new Uint8Array([0xEF, 0xBB, 0xBF]), source], { type: 'text/excel;charset=utf8' });
  var a = document.createElement('a');
  a.download = 'resultingCSV.csv';
  a.href = window.URL.createObjectURL(csvblob);
  a.click();
}

//////////////////////////////////////////////////////////////////////////////
// Reset all
var resetAll = () => {
  var deleteButtons = document.querySelectorAll(".foxsection.fieldDefsRow .deletesectionbutton");
  for (var i = deleteButtons.length - 2; i >= 0 ; i--) {
      deleteButtons[i].click();
  }
  formatNewRow(false, '1');
}
