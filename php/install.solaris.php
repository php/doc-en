<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP ¤â¥U'),
  'prev' => array('install.openbsd.php', 'Unix/OpenBSD installs'),
  'next' => array('install.unix.php', 'Installation on UNIX systems'),
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
manualHeader('Unix/Solaris installs','install.solaris.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="install.solaris"
></A
>Unix/Solaris installs</H1
><P
>&#13;  This section contains notes and hints specific to installing
  PHP on Solaris systems.
 </P
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.solaris.required"
></A
>Required software</H2
><P
>&#13;   Solaris installs often lack C compilers and their related tools.
   The required software is as follows:    
   <P
></P
><UL
><LI
><P
>&#13;      gcc (recommended, other C compilers may work)
     </P
></LI
><LI
><P
>&#13;      make
     </P
></LI
><LI
><P
>&#13;      flex
     </P
></LI
><LI
><P
>&#13;      bison
     </P
></LI
><LI
><P
>&#13;      m4
     </P
></LI
><LI
><P
>&#13;      autoconf
     </P
></LI
><LI
><P
>&#13;      automake
     </P
></LI
><LI
><P
>&#13;      perl
     </P
></LI
><LI
><P
>&#13;      gzip
     </P
></LI
><LI
><P
>&#13;      tar
     </P
></LI
></UL
>
    In addition, you will need to install (and possibly compile) any
    additional software specific to your configuration, such as Oracle
    or MySQL.
  </P
></DIV
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.solaris.packages"
></A
>Using Packages</H2
><P
>&#13;   You can simplify the Solaris install process by using pkgadd to
   install most of your needed components. 
  </P
></DIV
></DIV
><?php manualFooter('Unix/Solaris installs','install.solaris.php');
?>