#!/usr/bin/php -q
<?php

	/*******************************************************
	 * checkent.php
	 *
	 * this little script checks if entities in global.ent
	 * are ok
   *
	 * PHP configuration options
	 *  -enable-ftp
	 *******************************************************
	 * Authors:
	 * Georg Richter <georg@php.net>
	 *******************************************************/

	// like a good wine, this script needs some time
	set_time_limit(0);

	// schemes we had to check
	$schemes = array("http", "https", "ftp");

	// Start this script only from script.dir
	$filename = "../global.ent";


	function errormsg ($entity, $desc){
		printf ("%30s: %s\n", $entity, $desc);
		return;
	}

	if (!$fp = fopen($filename, "r")){
		printf ("Error: can't open $filename\n");
		exit(-1);
	}

	printf ("checkent.php\nPlease wait, this could take some time\n\n");

	while (!feof($fp)){
		// read line and remove unnecessary spaces
		$line = ltrim(ereg_replace("  ", " ", fgets($fp, 255)));

		// we only need entity lines
		if (substr($line,0,8) == "<!ENTITY"){
			$tmp = explode (" ", $line);
			$entity = $tmp[1];
			$link = substr($tmp[2], 1, strlen($tmp[2]) - 4);

			// get parts of url
			$url = parse_url($link);

			// valid scheme ?!
			if (in_array($url["scheme"], $schemes)){
				$ip = gethostbyname($url["host"]);
				if ($ip == $url["host"]){
					errormsg ($entity, "unknown host: " .$url["host"]);
				} else
				switch ($url["scheme"]){
					case "http":
						if ($fpurl = @fopen($link, "r")){
							fclose ($fpurl);
						}
						else
							errormsg ($entity, "Could not open document: " . $link);
					break;

					case "ftp":
						if ($ftp = @ftp_connect($url["host"])){
							if (@ftp_login($ftp, "anonymous", "georg@php.net")){
								$flist = ftp_nlist($ftp, $url["path"]);
								if (!count($flist))
									errormsg($entity, "unknown path: " . $url["path"]);
							} else
								errormsg ($entity, "could not login as anonymous to " . $url["host"]);
							ftp_quit($ftp);
						} else
							errormsg ($entity, "could not connect to " . $url["host"]);

					break;

				}
			}
		}
	}
	fclose($fp);
?>