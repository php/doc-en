// phpZ version 1.0 PHP Manual CHM version skin by Gonzalo De la Peña <gnz@pistonbroke.com>
// Based partially on the PHP CHM base skin by Gabor Hojtsy 
// 
// system requirements:
// Microsoft Internet Explorer 5.0 minimum
// 800x600 screen resolution
// 16 bit color depth or more recommended

// Get style sheet file
document.write(
	// Get our style file
	'<link rel="stylesheet" type="text/css" href="' + chmfile_path + 'skins/phpZSR1/style.css">'
);

// sets global variables
function setGlobals() {
	skinpath = chmfile_path + 'skins/phpZ/';
	switcherTabs = new Array('pageText', 'pageNotes');
	switcherTabLabels = new Array('<U>m</U>anual', '<U>u</U>ser contributed notes ('+ getUserNotesCount() +')');
	switcherTabAccessKeys = new Array('m', 'u');
	navIcons = new Array('goToc', 'goToFunctionRef', 'thisPageOnline', 'bugOnPage');
	navIconTitles = new Array('Go to Table Of Contents', 'Go to Function Reference','Navigate to this page online', 'Report a PHP documentation bug');
	if ( document.all('funcPurpose') )
		functionPage = true;
	else
		functionPage = false;
	manualVersion = 'phpZ skin<BR>ver 1.0';
	manualDate = '';
}

// Assembles and displays the page
// this is the function that gets called 'onload' by the document 
function displayPage() {
	setGlobals();
	assembleSkin();
	document.all('pageContent').style.display = 'block';
	//showPageCode();
}

// shows the current page source code (after DHTML) in a separate window
var sourceWin;
function showPageCode() {
	var code = document.body.innerHTML;
	if ( ! sourceWin )
		sourceWin = window.open('', 'sourceWin', 'width=550, height=450, toolbar=no, menubar=no, status=yes, scrollbars=yes, resizable=yes');
	sourceWin.document.write('<HTML><HEAD><TITLE>Page source</TITLE></HEAD><BODY></BODY></HTML>');
	sourceWin.document.body.innerText = code;
	// sourceWin.focus();
}

// assembles the skin
function assembleSkin() {
	var pageContent = document.all('pageContent');
	var page = '';
	
	functionPage ? page += getHeaderFunctionPage() : page += getHeaderNonFunctionPage();

	page += getMain();
	page += getFooter();
	
	pageContent.innerHTML = page;
	updateContentSize();
	showTabPane('pageText');
	//document.all('contextMenu').style.display='none';
	//showPageCode();
}

// navigates to the TOC
function goToc() {
	document.location = 'index.html';
}

// navigates to the function ref
function goToFunctionRef() {
	document.location = 'funcref.html';
}

// gets the number of user contributed notes available
function getUserNotesCount() {
	var count = document.all('pageNotes').childNodes.length - 1;
	if ( count < 0 )
		count = 0;
	return count;
}

// the following functions contain the HTML code for the skin
function getHeaderFunctionPage() {
	var path = document.all('navPath');
	var title = document.all('pageTitle').innerHTML;
	var funcUsage = document.all('funcUsage').innerHTML;
	//alert(funcUsage);
	var funcPurpose = document.all('funcPurpose').innerText;
	var funcAvail = document.all('funcAvail').innerText;
	var html = '';
	
	fixPathLinks();
	
	html += '<TABLE cellpadding=0 cellspacing=0 border=0 width="100%"><TR>';
	html += '<TD><A href="http://php.net/" target="web"><IMG src="'+ skinpath +'logo.gif" width=141 height=82 border=0></A></TD>';
	html += '<TD valign="top" width="100%">';
	html += '<DIV style="background-color:#9999ff; height:24px; width:100%;" class="small text"><DIV style="padding-top:5px; overflow:hidden;" nowrap>'+ path.innerHTML +'</DIV></DIV>';
	html += '<DIV style="background-color:#E0E6FF; background-image:url(\''+ skinpath +'\\header_background.gif\'); height:58px; width:100%; overflow:hidden;" class="big text secondaryType">';
	html += '  <TABLE cellpadding=0 cellspacing=0 style="width:100%;"><TR>';
	html += '    <TD><DIV style="padding-top:14px; line-height:22px;">'+ title +'<SPAN class="small lightBlue text"> &nbsp;<NOBR>'+ funcPurpose +'</NOBR></SPAN></DIV></TD>';
	html += '    <TD align="right" class="small"><DIV style="padding-top:20; margin-right:20;"><SPAN class="lightText">available since:</SPAN> '+ funcAvail+'</DIV></TD>';
	html += '  </TR></TABLE>';
	html += '</DIV>';
	html += '</TD>';
	html += '</TR></TABLE>';
	html += '<TABLE cellpadding=0 cellspacing=0 border=0 width="100%"><TR valign="top">';
	html += '<TD rowspan=2><DIV class="tiny centered text" style="color:#0B9C8D; background-color:#316B58; width:85; height:90; background-image:url(\''+ skinpath +'\\header_green_background.gif\');"><A href="mailto:gnz@amorfo.com?subject=phpZ skin feedback" title="skin feedback" style="color:#0B9C8D;">'+ manualVersion +'</A><BR>'+ manualDate +'</DIV></TD>';
	html += '<TD colspan=2><TABLE cellpadding=0 cellspacing=4 border=0 width="100%"><TR>';
	html += '<TD style="background-color:#9999ff;"><DIV style="height:55; width:4;"></DIV></TD>';
	html += '<TD width="100%" style="background-color:#ffffff;"><DIV style="margin:5;" class="text">';
	
	html += '<DIV class="funcUsage"><SPAN class="small lightText">usage:</SPAN> ' + funcUsage + '</DIV>';
	
	html += '</DIV></TD></TR></TABLE></TD>';
	html += '</TR><TR valign="bottom">';
	html += '<TD><DIV style="margin-left:5;">'+ getSwitcherTabs() +'</DIV></TD>';
	html += '<TD width="100%" align="right">'+ getIconBar() +'</TD>';
	html += '</TR></TABLE>';
	
	return html;
}

function getHeaderNonFunctionPage() {
	var path = document.all('navPath');
	var title = document.all('pageTitle').innerHTML;
	var html = '';
	fixPathLinks();
	
	html += '<TABLE cellpadding=0 cellspacing=0 border=0 width="100%"><TR>';
	html += '<TD><A href="http://php.net/" target="web"><IMG src="'+ skinpath +'logo.gif" width=141 height=82 border=0></A></TD>';
	html += '<TD valign="top" width="100%">';
	html += '<DIV style="background-color:#9999ff; height:24px; width:100%;" class="small text"><DIV style="padding-top:5px; overflow:hidden;" nowrap>'+ path.innerHTML +'</DIV></DIV>';
	html += '<DIV style="background-color:#E0E6FF; background-image:url(\''+ skinpath +'\\header_background.gif\'); height:58px; width:100%; overflow:hidden;" class="big text secondaryType">';
	html += '<DIV style="padding-top:16px;">'+ title +'</DIV>';
	html += '</DIV>';
	html += '</TD>';
	html += '</TR></TABLE>';
	html += '<TABLE cellpadding=0 cellspacing=0 border=0 width="100%"><TR>';
	html += '<TD><DIV class="tiny centered text" style="color:#0B9C8D; background-color:#316B58; width:85; height:30; background-image:url(\''+ skinpath +'\\header_green_background.gif\');"><A href="mailto:gnz@pistonbroke.com?subject=phpZ skin feedback" title="skin feedback" style="color:#0B9C8D;">'+ manualVersion +'</A><BR>'+ manualDate +'</DIV></TD>';
	html += '<TD valign="bottom"><DIV style="margin-left:5;">'+ getSwitcherTabs() +'</DIV></TD>';
	html += '<TD width="100%" align="right">'+ getIconBar() +'</TD>';
	html += '</TR></TABLE>';
	return html;
}

function fixPathLinks() {
	path = document.all('navPath');
	// assigns new class name to the path links
	for ( i = 0; i < path.childNodes.length; i++ )
		path.childNodes[i].tagName == 'A' ? path.childNodes[i].className = 'black ulEd' : '';
}

function getIconBar() {
	var html = '';
	
	html += '<TABLE cellpadding=0 cellspacing=0 border=0><TR>';
	for ( i = 0; i < navIcons.length; i++ )
		html += '<TD><A href="javascript:'+ navIcons[i] +'();"><IMG style="margin:1 3 1 3;" src="'+ skinpath +'icn_'+ navIcons[i] +'.gif" title="'+ navIconTitles[i] +'" width=25 height=25 border=0></A></TD>';
	html += '</TR></TABLE>';
	return html;
}

function getSwitcherTabs() {
	var html = '';
	html += '<TABLE cellpadding=0 cellspacing=0 border=0><TR>';
	for ( i = 0; i < switcherTabs.length; i++ )
	{
		html += '<TD>';
		html += '<A href="#" accesskey="'+ switcherTabAccessKeys[i] +'" onfocus="this.nextSibling.click(); return false;"></A>';
		html += '<DIV class="switcherTabs" onclick="showTabPane(\''+ switcherTabs[i] +'\')" id="switcherTab_'+ switcherTabs[i] +'">';
		html += '<TABLE cellpadding=0 cellspacing=0 border=0><TR>';
		html += '<TD><IMG src="'+ skinpath +'tab_button_'+ switcherTabs[i] +'_inactive.gif" width=31 height=24></TD>';
		html += '<TD style="background-color:#ffffff; background-image:url(\''+ skinpath +'\\tabs_background_inactive.gif\');"><DIV style="margin:0 4 0 4;" class="small text" nowrap>'+ switcherTabLabels[i] +'</DIV></TD>';
		html += '<TD><IMG src="'+ skinpath +'tabs_corner_inactive.gif" width=9 height=24></TD>';
		html += '</TR></TABLE>';
		html += '</DIV>';
		html += '</TD>';
	}
	html += '</TR></TABLE>';
	return html;
}

var activeTabId;
function showTabPane(sTabId) {
	var tabContent = document.all(sTabId);

	activateTab(sTabId);
	tabContent.style.display = 'block';
	
	if ( ie_version_major >= 5.5 )
		tabContent.setActive();

	if ( activeTabId && ( activeTabId != sTabId ) )
	{
		document.all(activeTabId).style.display = 'none';
		deActivateTab(activeTabId);
	}
	activeTabId = sTabId;
}

function activateTab(sTabId) {
	var oTab = document.all('switcherTab_' + sTabId);
	var tabTable = oTab.childNodes[0];
	var tabIcon = tabTable.rows[0].cells[0].childNodes[0];
	var tabLabelCell = tabTable.rows[0].cells[1];
	var tabCorner = tabTable.rows[0].cells[2].childNodes[0];
	
	tabIcon.src = skinpath + '\\tab_button_' + sTabId + '_active.gif';
	tabLabelCell.style.backgroundImage = '';
	tabCorner.src = skinpath + '\\tabs_corner_active.gif';
}

function deActivateTab(sTabId) {
	var oTab = document.all('switcherTab_' + sTabId);
	var tabTable = oTab.childNodes[0];
	var tabIcon = tabTable.rows[0].cells[0].childNodes[0];
	var tabLabelCell = tabTable.rows[0].cells[1];
	var tabCorner = tabTable.rows[0].cells[2].childNodes[0];
	
	tabIcon.src = skinpath + '\\tab_button_' + sTabId + '_inactive.gif';
	tabLabelCell.style.backgroundImage = 'url(\'' + skinpath +'\\tabs_background_inactive.gif\')';
	tabCorner.src = skinpath + '\\tabs_corner_inactive.gif';
}

function getMain() {
	var html = '';
	window.attachEvent('onresize', updateContentSize);
	html += '<DIV style="border-left:4px solid #9999FF; margin-left:4; margin-right:4;">';
	
	// this is the code for the white fader
	html += '<DIV style="position:relative;">';
	html += '<DIV id="whiteFade" style="background-image:url(\''+ skinpath +'white_fade.gif\'); background-repeat:repeat-x; position:absolute; top:0; left:0; height:15px; font-size:1px; z-index:10;">&nbsp;</DIV>'
	html += '</DIV>';
	
	for ( i = 0; i < switcherTabs.length; i++ ) // adds each content div
		html += '<DIV id="'+ switcherTabs[i] +'" style="display:none; background-color:#ffffff; padding:15 0 5 0; overflow:scroll;" class="text">' + document.all(switcherTabs[i]).innerHTML + '</DIV>';
	
	html += '</DIV>';
	
	return html;
}

function updateContentSize() {
	var whiteFade = document.all('whiteFade');
	var sizeElement = getBodySizeElement();
	var contentDivIds = switcherTabs;
	var sizer;
	
	var verticalSizeCorrection;
	
	if ( ie_version_major >= 6 )
		verticalSizeCorrection = 180;
	else
		verticalSizeCorrection = 158;
		
	for ( i = 0; i < contentDivIds.length; i++ )
	{
		sizer = document.all(contentDivIds[i]);
		functionPage ? sizer.style.height = sizeElement.clientHeight - 240 : sizer.style.height = sizeElement.clientHeight - verticalSizeCorrection;
		sizer.style.width = sizeElement.clientWidth - 12;
	}
	whiteFade.style.width = sizeElement.clientWidth - 40;
}

function getFooter() {
	var prev = document.all('navPrev');
	var next = document.all('navNext');
	var html = '';
	var prevText, nextText;
	
	if ( ! prev || ! next )
	{
		next = document.createElement('DIV');
		next.innerHTML = '&nbsp;';
		prev = document.createElement('DIV');
		prev.innerHTML = '&nbsp;';
	}
	else
		if ( ie_version_major >= 6 )
		{
			prevText = prev.childNodes[0].childNodes[0];
			prevText.data = prevText.substringData(3, prevText.length - 3);
			nextText = next.childNodes[0].childNodes[0];
			nextText.data = nextText.substringData(0, nextText.length - 3);
		}
	
	html += '<DIV style="background-color:#BABFD4; border-top:2px solid #000000; margin-top:4; height:42; overflow:hidden;">';
	html += '<TABLE cellpadding=0 cellspacing=0 border=0 width="100%"><TR>';
	
	html += '<TD><DIV style="margin:3 10 0 10;"><TABLE cellpadding=0 cellspacing=4 border=0><TR>';
	html += '<TD><IMG src="'+ skinpath +'icn_prev.gif" width=24 height=24></TD>';
	html += '<TD class="small text"><U>p</U>rev: '+ prev.innerHTML +'</TD>';
	html += '</TR></TABLE></DIV></TD>';
	
	html += '<TD align="right"><DIV style="margin:3 10 0 10;"><TABLE cellpadding=0 cellspacing=4 border=0><TR>';
	html += '<TD class="small text"><U>n</U>ext: '+ next.innerHTML +'</TD>';
	html += '<TD><IMG src="'+ skinpath +'icn_next.gif" width=24 height=24></TD>';
	html += '</TR></TABLE></DIV></TD>';
	
	html += '</TR></TABLE>';
	html += '</DIV>';
	return html;
}

function getBodySizeElement() {
	var delement;
	// Thanks to Gabor Hojtsy for this piece of code
	if ( ie_version_major >= 6 )
		delement = document.body.parentElement;
	else
		delement = document.body;         
	
	return delement;
}
