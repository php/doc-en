// =============================================================================
// Preference handling

// Handle all the preferences, including online functions custom
// context menus, and skins [this is called from php_manual_prefs.js]
function prefHandler()
{
    // Find out what file stores the skin JS code
    switch (prefs_skin) {
        
        // Internal low skin
        case "Low":
            skin_js_file = '_skin_lo.js';
            break;
        
        // Internal high skin
        case "High":
            skin_js_file = '_skin_hi.js';
            break;
        
        // An external skin with full path (escape it just
        // to make sure that paths with spaces and other
        // unusual chars will work - and turn back : to
        // it's original form)
        default:
            //escaped_skin = escape(prefs_skin);
            //escaped_skin = escaped_skin.replace(/%3A/, ':')
            skin_js_file = "file:///" + prefs_skin;
            break;
    }

    // Load in the skin JS code
    document.write('<script src="' + skin_js_file + '"><'+ '/script>');
    
    // Write out context menu for the first time into it's div
    document.write('<div id="contextMenu">');
    contextMenuRewrite(true);
    document.write('</div>');
    
    // Assign our own event handlers to document events [req. IE5]
    document.oncontextmenu = contextMenu;
    document.onclick       = contextMenuCloseTimeout;
    window.onblur          = contextMenuCloseTimeout;
}

// =============================================================================
// CHM path detection - Thanks to Pete Lees and Jeff Hall for this code

// Get the path and name of the CHM file and assign values
function getCHMFile()
{
    var a, X, Y, Z;
    a = location.href.search(/:/);
    Y = location.href.lastIndexOf("::");
    if (a == 4) {  // file: or http:
        Y = location.href.lastIndexOf("/");
        chmfile_page = unescape(location.href.substring(Y+1));
        if (location.href.substring(0,4) == "file") {
            chmfile_path = unescape(location.href.substring(8, Y+1));
        }
    }
    else {
        if (a == 2) X = 14; // mk:@MSITStore:
        if (a == 7) X = 7;  // ms-its:
        chmfile_fullname = unescape(location.href.substring(X, Y));
        Z = chmfile_fullname.lastIndexOf("\\");
        chmfile_path = unescape(chmfile_fullname.substring(0, Z+1));
        chmfile_name = unescape(chmfile_fullname.substring(Z+1));
        chmfile_page = unescape(location.href.substring(Y+3));
    }

    //alert(location.href+"\n\n"+"fullname="+chmfile_fullname+"\n"+"path="+
    //chmfile_path+"\n"+"name="+chmfile_name+"\n"+"page="+chmfile_page);
}

// =============================================================================
// Page display and online functions code

// Go online to see this page in an external window [called when a user
// clicks on the "this page oline" link]
function thisPageOnline()
{
    if (!prefs_online) {
        alert("You are in offline mode.\nThis functionality is not available");
    } else {
        window.open(prefs_mirror + 'manual/' + this_lang + '/' + this_page_php);
    }
}

// Report bug on this manual page, using GET method URL tricks
// [called when a user clicks on the "report bug on this page" link]
function bugOnPage()
{
    if (!prefs_online) {
        alert("You are in offline mode.\nThis functionality is not available");
    } else {
        window.open(
            "http://bugs.php.net/report.php?" +
            escape("in[php_version]") + "=earlier&" +
            escape("in[bug_type]") + "=Documentation%20problem&" +
            escape("in[php_os]") + "=windows&" +
            escape("in[sdesc]") + "=" + escape("[chm] bug on " + this_page) + "&" +
            escape("in[ldesc]") + "=" + escape("I have found a bug on page " +
                this_page + "\n[chm date: " + this_date + "]...\n")
        );
    }
}

// =============================================================================
// Handle user notes from an external CHM file

// Copy user notes text from aan invisible IFRAME to a visible
// DIV tag's content area [this is called from displayNotes()
// which is defined in every HTML page to fix an old IE bug]
function _displayNotes()
{
    // Get the element from the "all" collection
    element = document.all['pageNotes'];
    
    // If we have that element, copy notes here
    if (element) {
        element.innerHTML = parent.nbuff.document.body.innerHTML;
    } else {
        alert("Error in user note inclusion");
    }
}

// Load the user notes HTML file [this is called from the HTML
// file directly on every page] Depends on UA version
function loadNotes()
{
    // Workaround for IE6 bug
    if (ie_version_major == 6) {
        document.write('<script src="mk:@MSITStore:' +
        chmfile_path + 'php_manual_notes.chm::/_filelist.js"></script>');
    }
    
    // Load notes in otherwise
    else {
        notesIframe();
    }
}

// Prints out the inline frame for the notes HTML file
function notesIframe()
{
    // Notes buffer for user notes to display on this page
    document.write(
        '<iframe name="nbuff" style="display:none" src="mk:@MSITStore:' +
        chmfile_path + 'php_manual_notes.chm::/' + chmfile_page +
        '"></iframe>'
    );
}

// =============================================================================
// Context menu code

// Rewrite context menu according to the current environment. The only parameter
// indicates that this is the first display or not. [called once from
// prefHandler() and then every time before a context menu needs to be displayed]
function contextMenuRewrite(first)
{
    // Set selection to null value (we will check
    // if there is a current selection now)
    context_selrange = null;
    
    // Our menu layer is invisible initially
    menuHTML = '<table width="150" cellspacing="2" cellpadding="0">';
    
    // Write out a table row for all menu points
    for (i in prefs_context_names) {
    
        // Label and abstract implementation
        label = prefs_context_names[i];
        impl  = prefs_context_values[i];
        
        // Special display items start with an underscore
        if (impl.substring(0,1) == "_") {
    
            // Find the special printout needed
            switch (impl) {
            
                // Menu separator
                case "_Separator_":
                    menuHTML +=
                        '<tr><td bgcolor="#000000"><img src="_pixel.gif" ' +
                        'width="100%" height="1"></td></tr>';
                    break;
                    
                // Google search box (only printed in online mode)
                case "_GoogleSearch_":
                    if (prefs_online) {
                        menuHTML +=
                            '<tr><td style="background-color: #f5f5f5;"><form action="http://www.google.com/search" class="thin"' +
                            'onsubmit="contextMenuClose()" method="GET" target="_blank">' +
                            '<img src="_google.gif" align="absmiddle" alt="Google"><input type="text" name="q" ' +
                            'style="width:120px;"></form></td></tr>';
                    }
                    break;
    
                // Alltheweb search box (only printed in online mode)
                case "_ATWSearch_":
                    if (prefs_online) {
                        menuHTML +=
                            '<tr><td style="background-color: #f5f5f5;"><form action="http://www.alltheweb.com/search" class="thin"' +
                            'onsubmit="contextMenuClose()" method="GET" target="_blank">' +
                            '<img src="_atw.gif" align="absmiddle" alt="AlltheWeb"><input name="cat" type="hidden" value="web">' +
                            '<input type="text" name="query" style="width:120px;"></form></td></tr>';
                    }
                    break;
                    
                // This should not be accessed in any valid situation, but
                // is here to at least print out something in case of error...
                default:
                    menuHTML += contextLink(i, label);
                    break;
    
            }
        }
        
        // Special functions that may need to be printed out conditionally
        else if (impl.substring(impl.length-2) == "()") {
    
            // Do something depending on function name
            switch (impl.substring(0, impl.length-2)) {
    
                // Copy selection
                case "copySelection":
                    // For the first time there is actually no document...
                    if (document.selection && !first) {
                        context_selrange = document.selection.createRange();
                        if (context_selrange.text.length > 0) {
                            menuHTML += contextLink(i, label);
                        }
                    }
                    break;
                    
                // Google or AlltheWeb search for selection (need to be in online mode)
                case "searchSelGoogle":
                case "searchSelATW":
                    // For the first time there is actually no document...
                    if (document.selection && !first) {
                        context_selrange = document.selection.createRange();
                        if (context_selrange.text.length > 0 && prefs_online) {
                            menuHTML += contextLink(i, label);
                        }
                    }
                    break;

                // Misc functions like print() and selectAll()
                default:
                    menuHTML += contextLink(i, label);
                    break;
            }
        }
        
        // Not a special display or function,
        // so simply print out the line for this item
        else { menuHTML += contextLink(i, label); }
    }
    
    // End table
    menuHTML += '</table>';
    
    // We do not have this element if this is the first run, but we
    // need to rewrite the element content if it's not the first time
    if (first) { document.write(menuHTML); }
    else {
        document.all['contextMenu'].innerHTML = menuHTML;
    }
}

// Write out one context menu link table row [called from contextMenuRewrite()]
function contextLink (number, label)
{
    return '<tr><td nowrap onmouseover="this.style.backgroundColor=\'#c0c0c0\';" ' +
           'onmouseout="this.style.backgroundColor=\'#f5f5f5\';" ' +
           'onclick="contextParse(' + number + ')" ' +
           'style="cursor:hand; background-color: #f5f5f5;"><p class="context">' +
           label + '</p></td></tr>';
}

// Parse the jump string, and do appropriate action [called when the user
// clicks on a table row in the context menu]
function contextParse(i)
{
    var control = prefs_context_values[i];
    
    // If the control string ends in (), then it is
    // a function, not a file name
    if (control.substring(control.length-2) == "()") {
       
        // Switch by function name
        switch (control.substring(0, control.length-2)) {
           
            // Print out this page
            case "print":
                print();
                break;
                
            // Copy the selection from the current document
            case "copySelection":
                if (context_selrange != null) {                
                    context_selrange.execCommand("Copy");
                }
                break;
            
            // Search for the selected text on Google
            case "searchSelGoogle":
                sel = contextGetSelection();
                if (sel != '') { window.open('http://www.google.com/search?q=' + escape(sel));  }
                break;
            
            // Search for the selected text on AlltheWeb
            case "searchSelATW":
                sel = contextGetSelection();
                if (sel != '') { window.open('http://www.alltheweb.com/search?cat=web&query=' + escape(sel));  }
                break;
                
            // Go back and forward in history
            case "back": history.back(); break;
            case "forward": history.forward(); break;

            // Refresh page dispplay
            case "refresh": window.location.reload(true); break;
            
            // Select all text in the document
            case "selectAll": document.body.createTextRange().select(); break;

            // View source of current page
            case "viewSource": window.location = "view-source:" + window.location.href; break;

            // This should never be accessed, but here to alert if there is an error
            default:
                alert('This context menu function is not supported');
                break;
        }
    }
    
    // If this is not a "function call", then jump
    // to that page in the CHM or outside of it
    else {
        // Link with protocol
        if (control.indexOf(":") > 0) {
            window.open(control);
        }
        
        // Link without protocol
        else {
            location = control;
        }
    }
}

// Returns the current selection [called when a context menu
// item is clicked involving an operation on a selection]
function contextGetSelection()
{
    // Test if anything is selected
    if (context_selrange != null && context_selrange.text.length > 0) {
        return context_selrange.text;
    } else {
        alert("Nothing is selected");
        return "";
    }
}

// Handle mousedown events in a document [called when a mouse click
// is issued on a document]
function contextMenu(e)
{
    // Close context menu, if it was open before
    contextMenuClose();
    
    // We enable the original context menu with the CTRL key if
    // then user selected to override it
    if (event.ctrlKey && prefs_context_override) {
        return true;
    }
    
    // We also enable the original one, if it is not overriden
    // and the user have not pressed the CTRL key
    else if (!event.ctrlKey && !prefs_context_override) {
        return true;
    }
    
    // Otherwise (overriden or overriden+CTRL) we display our menu
    else {
        
        // Rewrite contents of context menu
        contextMenuRewrite(false);

        // IE6+ is in standard compliant mode because of our !DOCTYPE
        // [ http://msdn.microsoft.com/workshop/author/dhtml/reference/objects/doctype.asp ]
        // so we need to use the "html" tag instead of the "body" to measure scrolled distance.
        // For older browsers, we need to use the body.
        if (ie_version_major >= 6) { delement = document.body.parentElement; }
        else { delement = document.body; }         

        // Find out if menu would be outside the window
        rightedge  = delement.clientWidth - event.clientX;
        bottomedge = delement.clientHeight - event.clientY;
        
        // Shortcut for the context menu
        cmenu = document.all['contextMenu'];

        // Place menu to the right position, not to extend the window size
        if (rightedge < cmenu.offsetWidth) {
            cmenu.style.left = delement.scrollLeft + event.clientX - cmenu.offsetWidth;
        } else {
            cmenu.style.left = delement.scrollLeft + event.clientX;
        }

        if (bottomedge < cmenu.offsetHeight) {
            cmenu.style.top = delement.scrollTop + event.clientY - cmenu.offsetHeight;
        } else {
            cmenu.style.top = delement.scrollTop + event.clientY;
        }
        
        // Show menu
        cmenu.style.visibility = "visible";
        return false;
    }
}


// Closes the context menu [called when the time is out]
function contextMenuClose()
{
    // Hide context menu
    document.all['contextMenu'].style.visibility = "hidden";
}

// Sets timeout for menu close [called by document onclick event]
function contextMenuCloseTimeout()
{
    // Support for form focus event (form in context menu)
    if (document.activeElement.tagName != 'INPUT') {
        setTimeout('contextMenuClose()', 100);
    }
}

// =============================================================================
// Example clipboard copy links

// Copy an example from a HTML container with id "example_" + number [called
// when a user clicks on that copy link]
function copyExample(number)
{
    // moveToElementText is only supported in IE5 and up
    if (ie_version_major < 5) {
        alert("Example copy is not supported in this browser");
    } else {
        BodyRange = document.body.createTextRange();
        BodyRange.moveToElementText(document.all['example_' + number]);
        BodyRange.execCommand("Copy");
    }
}

// =============================================================================
// Default skin functions

// Display the page without online functions if they are disabled
function defaultDisplayPage() {

    // Do not display online functions links if they are disabled
    if (!prefs_online) {
        document.all['navOnline'].style.display = 'none';
    }

    // Show page container
    document.all['pageContent'].style.display = 'block';
}

// =============================================================================
// Main JS program flow

// Default vars for all the pages [generated]
this_lang = 'LANGUAGE_HERE';
this_date = 'DATE_HERE';

// Vars for online menu of this particular page
this_page     = location.pathname.substring(location.pathname.lastIndexOf("/")+1);
this_page_php = this_page.substring(0, this_page.lastIndexOf(".")) + '.php';

// Create the chmfile vars for this HTML page
chmfile_fullname = "";
chmfile_name     = "";
chmfile_path     = "";
chmfile_page     = "";
getCHMFile();

// Default values for preferences (no online functions, no
// special context menu, low skin)
prefs_online           = false;
prefs_context_override = false;
prefs_skin             = "Low";

// Support for selection sensitive context menus
context_selrange = null;

// Find out major and minor IE version number
var ie_version_start = navigator.appVersion.indexOf("MSIE");
var ie_version_end = navigator.appVersion.indexOf(".", ie_version_start);
ie_version_major = parseInt(navigator.appVersion.substring(ie_version_start+5, ie_version_end));
ie_version_minor = parseInt(navigator.appMinorVersion);

// Get the preferences file from outside
document.write('<script src="' + chmfile_path + 'php_manual_prefs.js"><'+ '/script>');