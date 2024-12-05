//javascript to toggle right and left bar
//toggle button
//rshow="1"; // global to save state
function toggleRight() {
	if (!isHiddenRight()) {
        if (toggleCookies== "1") document.cookie=rcookie+"=0; path=/";
        rshow="0";
        hideRight();
    }
    else if(isHiddenRight()){
        if (toggleCookies== "1") document.cookie=rcookie+"=1; path=/";
        rshow="1";
        showRight();
    }
}

//hide and show rightbar
function hideRight() {
	if(document.getElementById) {
		document.getElementById('right-panel').style.width = "0px";
		document.getElementById('right-panel').style.padding = "0px";
		document.getElementById('center-content-wrapper').style.marginRight = "0px";
	}
	return "";
};

function showRight() {
	if(document.getElementById) {
        document.getElementById('right-panel').style.width = "220px";
        document.getElementById('right-panel').style.padding = "0px 5px";
        if (document.body.clientWidth < 800) {
		    document.getElementById('center-content-wrapper').style.marginRight = "-230px";
	    }
	    if (document.body.clientWidth < 700) {
		    hideLeft();
	    }
	}
	return "";
};

//=============================================================//

//toggle button
//lshow="1"; // global to save state
function toggleLeft() {
    if (!isHiddenLeft()) {
        if (toggleCookies== "1") document.cookie=lcookie+"=0; path=/";
        lshow="0";
        hideLeft();
    }
    else if(isHiddenLeft()){
        if (toggleCookies== "1") document.cookie=lcookie+"=1; path=/";
        lshow="1";
        showLeft();
    }
}

//hide and show left bar
function hideLeft() {
  if(document.getElementById) {
    document.getElementById('left-panel').style.width = "0px";
    document.getElementById("main-wrapper").style.marginLeft = "0px";
  }
  return "";
};

function showLeft() {
  if(document.getElementById) {
    document.getElementById('left-panel').style.width = "186px";
    if (document.body.clientWidth > 700) {
	    document.getElementById("main-wrapper").style.marginLeft = "186px";
    }
    else {
		hideRight();
	}
  }
  return "";
};

function toggleMenu() {
	if (!isHiddenMenu()) {
        hideMenu();
    }
    else {
        showMenu();
    }
}

//hide and show rightbar
function hideMenu() {
	if(document.querySelector) {
		document.querySelector('#header-menu .rnav').style.maxHeight = "0px";
	}
	return "";
};

function showMenu() {
	if(document.querySelector) {
		document.querySelector('#header-menu .rnav').style.maxHeight = "300px";
	}
	return "";
};

function isHiddenLeft() {
    if(document.getElementById) {
        return document.getElementById('left-panel') == null || window.getComputedStyle(document.getElementById('left-panel')).width == "0px";
    }
    return false;
}

function isHiddenRight() {
    if(document.getElementById) {
        return document.getElementById('right-panel') == null || window.getComputedStyle(document.getElementById('right-panel')).width == "0px";
    }
    return false;
}

function isHiddenMenu() {
    if(document.querySelector) {
        return document.querySelector('#header-menu .rnav') == null || window.getComputedStyle(document.querySelector('#header-menu .rnav')).maxHeight == "0px";
    }
    return false;
}

window.onload = function() {
    // For clicks elsewhere on the page
    document.onclick = function(e) {
		e = e || window.event;
		var target = e.target || e.srcElement;
        if (target.id != "togglemenu" && target.id != "togglemenubutton" && !isHiddenMenu() && document.body.clientWidth < 700) {
			hideMenu();
		}

        if (document.getElementById('toggleright') != null && !document.getElementById('toggleright').contains(target) && document.getElementById('toggleleft') != null && !document.getElementById('toggleleft').contains(target) && !isHiddenRight() && document.body.clientWidth < 800) {
			hideRight();
		}
        if (document.getElementById('toggleright') != null && !document.getElementById('toggleright').contains(target) && document.getElementById('toggleleft') != null && !document.getElementById('toggleleft').contains(target) && !isHiddenLeft() && document.body.clientWidth < 700) {
			hideLeft();
		}
    };
};