<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP ¤â¥U'),
  'prev' => array('install.solaris.php', 'Unix/Solaris installs'),
  'next' => array('install.windows.php', 'Installation on Windows systems'),
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
manualHeader('Installation on UNIX systems','install.unix.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="install.unix"
></A
>Installation on UNIX systems</H1
><P
>&#13;    This section will guide you through the general configuration and
    installation of PHP on Unix systems. Be sure to investigate any
    sections specific to your platform or web server before you begin
    the process.
   </P
><P
>&#13;    Prerequisite knowledge and software:
    <P
></P
><UL
><LI
><P
>&#13;       Basic UNIX skills (being able to operate "make" and a C
       compiler, if compiling)
      </P
></LI
><LI
><P
>&#13;       An ANSI C compiler (if compiling)
      </P
></LI
><LI
><P
>&#13;       flex (for compiling)
      </P
></LI
><LI
><P
>&#13;       bison (for compiling)
      </P
></LI
><LI
><P
>&#13;       A web server
      </P
></LI
><LI
><P
>&#13;       Any module specific components (such as gd, pdf libs, etc.)
      </P
></LI
></UL
>
   </P
><P
>&#13;    There are several ways to install PHP for the Unix platform, either
    with a compile and configure process, or through various
    pre-packaged methods. This documentation is mainly focused around
    the process of compiling and configuring PHP.
   </P
><P
>&#13;    The initial PHP setup and configuration process is controlled by the
    use of the commandline options of the <TT
CLASS="filename"
>configure</TT
>
    script. This page outlines the usage of the most common options,
    but there are many others to play with. Check out the <A
HREF="install.configure.php"
>Complete list of configure
     options</A
> for an exhaustive rundown. There are several ways
     to install PHP:
    <P
></P
><UL
><LI
><P
>&#13;       As an <A
HREF="install.apache.php"
>Apache module</A
>
      </P
></LI
><LI
><P
>&#13;       As an <A
HREF="install.fhttpd.php"
>fhttpd module</A
>
      </P
></LI
><LI
><P
>&#13;       For use with <A
HREF="install.otherhttpd.php"
>AOLServer, NSAPI,
       phttpd, Pi3Web, Roxen, thttpd, or Zeus.</A
>
      </P
></LI
><LI
><P
>&#13;       As a <A
HREF="install.commandline.php"
>CGI executable</A
>
      </P
></LI
></UL
>
   </P
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.unix.apache-module"
></A
>Apache Module Quick Reference</H2
><P
>&#13;     PHP can be compiled in a number of different ways, but one of
     the most popular is as an Apache module. The following is a quick
     installation overview.
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
NAME="install.unix.apache-module.quick"
></A
><P
><B
>&#31684;&#20363; 3-4. 
      Quick Installation Instructions for PHP 4 (Apache Module Version)
     </B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="shell"
>1.  gunzip apache_1.3.x.tar.gz
2.  tar xvf apache_1.3.x.tar
3.  gunzip php-x.x.x.tar.gz
4.  tar xvf php-x.x.x.tar
5.  cd apache_1.3.x
6.  ./configure --prefix=/www
7.  cd ../php-x.x.x
8.  ./configure --with-mysql --with-apache=../apache_1.3.x --enable-track-vars
9.  make
10. make install
11. cd ../apache_1.3.x
12. ./configure --activate-module=src/modules/php4/libphp4.a
13. make
14. make install
15. cd ../php-x.x.x
16. cp php.ini-dist /usr/local/lib/php.ini
17. Edit your httpd.conf or srm.conf file and add: 
      AddType application/x-httpd-php .php

18. Use your normal procedure for restarting the Apache server. (You must
    stop and restart the server, not just cause the server to reload by
    use a HUP or USR1 signal.)</PRE
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
NAME="install.building"
></A
>Building</H2
><P
>&#13;     When PHP is configured, you are ready to build the CGI executable.
     The command <B
CLASS="command"
>make</B
> should
     take care of this.  If it fails and you can't figure out why, see
     the <A
HREF="install.problems.php"
>Problems section</A
>.
    </P
></DIV
></DIV
><?php manualFooter('Installation on UNIX systems','install.unix.php');
?>