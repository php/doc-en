<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP ¤â¥U'),
  'prev' => array('install.macosx.php', 'Unix/Mac OS X installs'),
  'next' => array('install.solaris.php', 'Unix/Solaris installs'),
  'up'   => array('installation.php', '¦w¸Ë'),
  'toc'  => array(
      array('installation.php#install.general', 'General Installation Considerations'),
      array('install.hpux.php', 'Unix/HP-UX installs'),
      array('install.linux.php', 'Unix/Linux installs'),
      array('install.macosx.php', 'Unix/Mac OS X installs'),
      array('install.openbsd.php', 'Unix/OpenBSD installs'),
      array('install.solaris.php', 'Unix/Solaris installs'),
      array('install.unix.php', 'Installation on UNIX systems'),
      array('install.windows.php', 'Installation on Windows systems'),
      array('install.commandline.php', 'Servers-CGI/Commandline'),
      array('install.apache.php', 'Servers-Apache'),
      array('install.caudium.php', 'Servers-Caudium'),
      array('install.fhttpd.php', 'Servers-fhttpd'),
      array('install.iis.php', 'Servers-IIS/PWS'),
      array('install.netscape-enterprise.php', 'Servers-Netscape and iPlanet'),
      array('install.omnihttpd.php', 'Servers-OmniHTTPd Server'),
      array('install.oreilly.php', 'Servers-Oreilly Website Pro'),
      array('install.sambar.php', 'Servers-Sambar'),
      array('install.xitami.php', 'Servers-Xitami'),
      array('install.otherhttpd.php', 'Servers-Other web servers'),
      array('install.problems.php', 'Problems?'),
      array('install.configure.php', 'Complete list of configure options'),
)));
manualHeader('Unix/OpenBSD installs','install.openbsd.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="install.openbsd"
></A
>Unix/OpenBSD installs</H1
><P
>&#13; This section contains notes and hints specific to installing
 PHP on <A
HREF="http://www.openbsd.org/"
TARGET="_top"
>OpenBSD</A
>.
 </P
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.openbsd.ports"
></A
>Using Ports</H2
><P
>&#13;    This is the recommended method of installing PHP on OpenBSD, as it will have 
    the latest patches and security fixes applied to it by the maintainers.  To
    use this method, ensure that you have a <A
HREF="http://www.openbsd.org/ports.html"
TARGET="_top"
>&#13;    recent ports tree</A
>.  Then simply find out which flavors you wish
    to install, and issue the <B
CLASS="command"
>make install</B
> command.  Below
    is an example of how to do this.
   </P
><TABLE
WIDTH="100%"
BORDER="0"
CELLPADDING="0"
CELLSPACING="0"
CLASS="EXAMPLE"
><TR
><TD
><DIV
CLASS="example"
><A
NAME="install.openbsd.ports.example"
></A
><P
><B
>&#31684;&#20363; 3-3. OpenBSD Ports Install Example</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="programlisting"
>$ cd /usr/ports/www/php4
$ make show VARNAME=FLAVORS
 (choose which flavors you want from the list)
$ env FLAVOR="imap gettext ldap mysql gd" make install
$ /usr/local/sbin/php4-enable</PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
></DIV
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.openbsd.packages"
></A
>Using Packages</H2
><P
>&#13;   There are pre-compiled packages available for your release
   of <A
HREF="http://www.openbsd.org/"
TARGET="_top"
>OpenBSD</A
>.  These integrate 
   automatically with the version of Apache installed with the OS.
   However, since there are a large number of options (called 
   <SPAN
CLASS="emphasis"
><I
CLASS="emphasis"
>flavors</I
></SPAN
>) available for this port,
   you may find it easier to compile it from source using the ports tree.
   Read the <A
HREF="http://www.openbsd.org/cgi-bin/man.cgi?query=packages"
TARGET="_top"
>packages(7)</A
>
   manual page for more information in what packages are available.
  </P
></DIV
></DIV
><?php manualFooter('Unix/OpenBSD installs','install.openbsd.php');
?>