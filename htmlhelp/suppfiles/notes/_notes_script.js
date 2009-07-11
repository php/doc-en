// RAQ : Tuesday, 15 March 2005 03:20 pm : Allow notes from searches to call the original manual page and get the notes skinned.
// Get the path and name of the CHM file and assign values
function loadWithNotes() {
	var a, X, Y, Z;
	a = location.href.search(/:/);
	Y = location.href.lastIndexOf("::");

	if (a == 4) {  // file: or http:
		Y = location.href.lastIndexOf("/");
		chmfile_page = unescape(location.href.substring(Y+1));
		if (location.href.substring(0,4) == "file")
			chmfile_path = unescape(location.href.substring(8, Y+1));
	} else {
		if (a == 2)
			X = 14; // mk:@MSITStore:
		if (a == 7)
			X = 7;  // ms-its:

		chmfile_fullname = unescape(location.href.substring(X, Y));
		Z = chmfile_fullname.lastIndexOf("\\");
		chmfile_path = unescape(chmfile_fullname.substring(0, Z+1));
		chmfile_name = unescape(chmfile_fullname.substring(Z+1));
		chmfile_page = unescape(location.href.substring(Y+3));
	}
 
 //alert(
 // "_notes_script\n\n" +
 // "location\n"+
 // "location.hash = '" + location.hash + "'\n" +
 // "location.host = '" + location.host + "'\n" +
 // "location.hostname = '" + location.hostname + "'\n" +
 // "location.href = '" + location.href + "'\n" +
 // "location.pathname = '" + location.pathname + "'\n" +
 // "location.port = '" + location.port + "'\n" +
 // "location.protocol = '" + location.protocol + "'\n" +
 // "location.search = '" + location.search + "'\n\n" +
 // "chmfile\n"+
 // "chmfile_name = '" + chmfile_name + "'\n" +
 // "chmfile_path = '" + chmfile_path + "'\n" +
 // "chmfile_page = '" + chmfile_page + "'\n"
 // );
 
	location = location.protocol + '@MSITStore:' + chmfile_path + 'php_manual_en.chm::/' + chmfile_page + '#userNotes';
}