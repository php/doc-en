// This is a sample skin for the CHM edition of the PHP Manual.
// This skin should be in a "skins/headernostalgia" subdir of the folder
// containing the CHM (as assumed by the CSS and image loaders in this file)

// This skin is only here to demonstrate how to modify the layout
// and CSS properties for your working pleasure. This skin is named
// "headernostalgia" because it presents the header used in the early
// days of this edition.

// Feel free to play with this skin, the commets should help you
// to get started. Please submit any nice skins to the php-doc-chm
// mailing list <php-doc-chm@lists.php.net>. Thanks.

// Get style sheet file
document.write(
    // Get our style file
    '<link rel="stylesheet" type="text/css" href="' + chmfile_path + 'skins/headernostalgia/style.css">'
);

// Display the page
function displayPage() {
    
    // Find out if this is a function page or not
    if (document.all['funcPurpose']) { funcpage = true; }
    else { funcpage = false; }
    
    // This is the path where files should be searched
    skinpath = chmfile_path + 'skins/headernostalgia/';
    
    // Build header (depends on function info)
    skinBuildHeader(funcpage, skinpath);
    
    // Remove navigational table completely
    document.all['pageNav'].innerHTML = '';

    // Show page container
    document.all['pageContent'].style.display = 'block';

}

// Build the header of the skin
function skinBuildHeader(funcpage, skinpath)
{
    // Get page elements
    title  = document.all['pageTitle'].innerHTML;
    path   = document.all['navPath'].innerHTML;
    thisonline = document.all['navThisOnline'].innerHTML;
    reportbug = document.all['navReportBug'].innerHTML;
    
    // Do some reformatting to the path
    path = path.replace(/:/g, "&raquo;");
    
    // Decide what to print into online functions space
    if (prefs_online) {
        online_funcs = thisonline + '&nbsp;|&nbsp;' + reportbug;
    } else {
        online_funcs = "Online functions are disabled in your preferences";
    }
    
    // General table to the top of the page
    pageHeader = 

        '<div id="navLinkPath" style="position: absolute; top: 50px; left: 154px; z-index: 2; height: 20px; visibility: visible;">' +
        '<div class="navmenu"><nobr>' + path + '</nobr></div>' +
        '</div>' +
        '<div id="navOnlineMenu" style="position: absolute; top: 50px; left: 154px; z-index: 2; height: 20px; visibility: hidden;">' +
        '<div class="navmenu"><nobr>' + online_funcs + '</nobr></div>' +
        '</div>' + 
    
        '<table border="0" cellpadding="0" cellspacing="0" width="100%">' +

        '<tr><td><img src="' + skinpath + 'phpdoc_php.png" width="148" height="46" border="0" usemap="#phpdoc_php"></td>' +
        '<td background="' + skinpath + 'phpdoc_upback.png" height="46" width="100%" valign="middle"><nobr><h1>' + title + '</h1></nobr></td></tr>' +

        '<tr><td><img src="' + skinpath + 'phpdoc_menu.png" width="148" height="23" border="0" usemap="#phpdoc_menu"></td>' +
        '<td background="' + skinpath + 'phpdoc_midback.png" height="23" width="100%">&nbsp;</td></tr>' +
        '<tr><td><img src="' + skinpath + 'phpdoc_bellowmenu.png" width="148" height="16" border="0"></td>' +
        '<td background="' + skinpath + 'phpdoc_bottomback.png" height="16" width="100%">&nbsp;</td></tr></table>' +

        '<map name="phpdoc_php">' +
        '<area shape="rect" coords="5,1,91,44" href="index.html" alt="Manual TOC">' +
        '</map>' +
         
        '<map name="phpdoc_menu">' +
        '<area shape="poly" coords="3,4,41,4,41,20,3,20,3,4" href="#" alt="Path to this page" onclick="skinShowMenu(navLinkPath, this)">' +
        '<area shape="poly" coords="44,4,92,4,92,20,44,20,44,4" href="#" alt="Online functions" onclick="skinShowMenu(navOnlineMenu, this)">' +
        '<area shape="poly" coords="96,4,141,4,141,20,96,20,96,4" href="#_user_notes" alt="User notes">' +
        '</map>';
        
    // If this is a function page, show those headers too
    if (funcpage) {
    
        usage   = document.all['funcUsage'].innerHTML;
        purpose = document.all['funcPurpose'].innerHTML;
        avail   = document.all['funcAvail'].innerHTML;
        
        pageHeader +=
            '<div class="funcinfo"><table class="functable">' +
            '<tr><td class="funchead">Usage:</td><td>' + usage + '</td></tr>' +
            '<tr><td class="funchead">Purpose:</td><td>' + purpose + '</td></tr>' +
            '<tr><td class="funchead">Availability:</td><td>' + avail + '</td></tr></table></div>';
    }

    document.all['pageHeaders'].innerHTML = pageHeader;
    actualmenu = document.all['navLinkPath'];

}

// Show one menu, and hide the actual one, if possible
function skinShowMenu(menuobj, link) {
  actualmenu.style.visibility = "hidden";
  actualmenu = menuobj;
  actualmenu.style.visibility = "visible";
  link.blur();
}