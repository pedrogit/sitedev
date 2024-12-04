var gOriginalOrder = [];

function sortableSort(section, fieldName) {
  var sectionId = section.split("-")[1];
  var sectionElem = document.getElementById(sectionId);
  if (sectionElem) {
    let inc = true;
    if (fieldName.slice(-1) == "-") {
      inc = false;
      fieldName = fieldName.slice(0, -1);
    }

    let itemsElem = [];
    if (typeof gOriginalOrder[sectionId] === "undefined") {
      itemsElem = sectionElem.querySelectorAll('[data-sortitem]');
      // if no item were identified with data-sortitem=1, select all the div, li or tr children
      if (itemsElem.length === 0) {
        itemsElem = sectionElem.querySelectorAll(':scope > div,:scope > li,:scope > tr');
      }
      gOriginalOrder[sectionId] = itemsElem;
    }
    else {
      itemsElem = gOriginalOrder[sectionId];
    }

    let sortableItems = [];
    if (fieldName === "none") {
      sortableItems = gOriginalOrder[sectionId];
    }
    else {
      let minIdxPad = Math.ceil(Math.log10(itemsElem.length + 1));
      let i = 0;
      for (const elem of itemsElem) {
      itemField = elem.querySelectorAll('[data-sortfield=' + fieldName + ']');
        // add a unique sortable identifier to the key to be sorted
        let idx = (itemField[0] ? itemField[0].outerText : " ");
        // trim and remove accents from the identifier
        idx = idx.trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        idx = idx + "_" + "0".repeat(minIdxPad - Math.ceil(Math.log10(i++ + 2))) + i.toString();
        sortableItems[idx] = elem;
      }
    }

    sortedItemKeys = Object.keys(sortableItems);
    // if an order is requested, sort the keys
    if (fieldName !== "none") {
      sortedItemKeys.sort();
      if (!inc) sortedItemKeys=sortedItemKeys.reverse();
    }

    if (sortedItemKeys.length > 1) {
      // replace element with sorted ones
      sectionElem.replaceChildren();
      for (const key of sortedItemKeys) {
        sectionElem.appendChild(sortableItems[key]);
      }      
    }
  }
  else alert("No " + sectionId + " section found...");
}