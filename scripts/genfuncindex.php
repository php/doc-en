<?php 
/*
# 
# +----------------------------------------------------------------------+
# | PHP HTML Embedded Scripting Language Version 3.0                     |
# +----------------------------------------------------------------------+
# | Copyright (c) 1997,1998 PHP Development Team (See Credits file)      |
# +----------------------------------------------------------------------+
# | This program is free software; you can redistribute it and/or modify |
# | it under the terms of one of the following licenses:                 |
# |                                                                      |
# |  A) the GNU General Public License as published by the Free Software |
# |     Foundation; either version 2 of the License, or (at your option) |
# |     any later version.                                               |
# |                                                                      |
# |  B) the PHP License as published by the PHP Development Team and     |
# |     included in the distribution in the file: LICENSE                |
# |                                                                      |
# | This program is distributed in the hope that it will be useful,      |
# | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
# | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
# | GNU General Public License for more details.                         |
# |                                                                      |
# | You should have received a copy of both licenses referred to here.   |
# | If you did not, or have any questions about PHP licensing, please    |
# | contact core@php.net.                                                |
# +----------------------------------------------------------------------+
# | Authors:    Hartmut Holzgraefe <hartmut@six.de>                      |
# +----------------------------------------------------------------------+
# 
# $Id$
*/
?>
 <index id='index.functions'>
  <title>Function Index</title>
<?php
$letter=" ";
$functions = file($_SERVER['argv'][1]);
usort($functions,"strcasecmp");
foreach ( $functions as $funcentry ) {
	list($function,$description) = explode("-",$funcentry);
	$function=strtolower(trim($function));
	if(!ereg("^[[:alnum:]]",$function)) continue;
	if($function{0}!=$letter) {
		if($letter!=" ") {
			echo "  </indexdiv>\n";
		}
		$letter=$function{0};
		echo "  <indexdiv>\n";
		echo "   <title>".strtoupper($letter)."</title>\n";
	}
	echo "   <indexentry><primaryie><function>$function</function></primaryie></indexentry>\n";
}
?>
  </indexdiv>
 </index>
