<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP ¤â¥U'),
  'prev' => array('install.hpux.php', 'Unix/HP-UX installs'),
  'next' => array('install.macosx.php', 'Unix/Mac OS X installs'),
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
manualHeader('Unix/Linux installs','install.linux.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="install.linux"
></A
>Unix/Linux installs</H1
><P
>&#13;    This section contains notes and hints specific to installing
    PHP on Linux distributions.
   </P
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.linux.packages"
></A
>Using Packages</H2
><P
>&#13;     Many Linux distributions have some sort of package installation
     system, such as RPM. This can assist in setting up a standard
     configuration, but if you need to have a different set of features
     (such as a secure server, or a different database driver), you may
     need to build PHP and/or your webserver. If you are unfamiliar
     with building and compiling your own software, it is worth
     checking to see whether somebody has already built a packaged
     version of PHP with the features you need.
    </P
></DIV
></DIV
><?php manualFooter('Unix/Linux installs','install.linux.php');
?>