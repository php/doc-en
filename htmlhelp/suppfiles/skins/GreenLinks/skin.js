// This is a sample skin for the CHM edition of the PHP Manual.
// This skin should be in a "skins/greenlinks" subdir of the folder
// containing the CHM (as assumed by the CSS loader in this file)

// This skin is only here to demonstrate how to modify
// CSS properties for your working pleasure. The style sheet
// presented is the same as the 'low' sheet, except that
// all the link are green, just to prove that you see a
// different style sheet in action.

// Feel free to play with the styles, the commets should help you
// to get started. Please submit any nice skins to the php-doc-chm
// mailing list <php-doc-chm@lists.php.net>. Thanks.

// Get style sheet file
document.write(
    // Get our style file
    '<link rel="stylesheet" type="text/css" href="' + chmfile_path + 'skins/greenlinks/style.css">'
);

// Display the page to the user
function displayPage() { defaultDisplayPage(); }