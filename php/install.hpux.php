<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP 手冊'),
  'prev' => array('installation.php', '安裝'),
  'next' => array('install.linux.php', 'Unix/Linux installs'),
  'up'   => array('installation.php', '安裝'),
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
manualHeader('Unix/HP-UX installs','install.hpux.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="install.hpux"
></A
>Unix/HP-UX installs</H1
><P
>&#13;    This section contains notes and hints specific to installing PHP
    on HP-UX systems.
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
NAME="install.hpux.example"
></A
><P
><B
>&#31684;&#20363; 3-1. 
     Installation Instructions for HP-UX 10
    </B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="screen"
>From: paul_mckay@clearwater-it.co.uk
04-Jan-2001 09:49
(These tips are for PHP 4.0.4 and Apache v1.3.9) 

So you want to install PHP and Apache on a HP-UX 10.20 box? 

1. You need gzip, download a binary distribution from
http://hpux.connect.org.uk/ftp/hpux/Gnu/gzip-1.2.4a/gzip-1.2.4a-sd-10.20.depot.Z
uncompress the file and install using swinstall 

2. You need gcc, download a binary distribution from 
http://gatekeep.cs.utah.edu/ftp/hpux/Gnu/gcc-2.95.2/gcc-2.95.2-sd-10.20.depot.gz 
gunzip this file and install gcc using swinstall. 

3. You need the GNU binutils, you can download a binary distribution from 
http://hpux.connect.org.uk/ftp/hpux/Gnu/binutils-2.9.1/binutils-2.9.1-sd-10.20.depot.gz 
gunzip and install using swinstall. 

4. You now need bison, you can download a binary distribution from 
http://hpux.connect.org.uk/ftp/hpux/Gnu/bison-1.28/bison-1.28-sd-10.20.depot.gz 
install as above. 

5. You now need flex, you need to download the source from one of the
http://www.gnu.org mirrors. It is in the &#60;filename&#62;non-gnu&#60;/filename&#62; directory of the ftp site. 
Download the file, gunzip, then tar -xvf it. Go into the newly created flex
directory and do a ./configure, then a make, and then a make install 

If you have errors here, it's probably because gcc etc. are not in your
PATH so add them to your PATH. 

Right, now into the hard stuff. 

6. Download the PHP and apache sources. 

7. gunzip and tar -xvf them. 

We need to hack a couple of files so that they can compile ok. 

8. Firstly the configure file needs to be hacked because it seems to lose
track of the fact that you are a hpux machine, there will be a
better way of doing this but a cheap and cheerful hack is to put 
    lt_target=hpux10.20 
on line 47286 of the configure script. 

9. Next, the Apache GuessOS file needs to be hacked. Under
apache_1.3.9/src/helpers change line 89 from 
    "echo "hp${HPUXMACH}-hpux${HPUXVER}"; exit 0" 
to: 
    "echo "hp${HPUXMACH}-hp-hpux${HPUXVER}"; exit 0" 
    
10. You cannot install PHP as a shared object under HP-UX so you must compile
it as a static, just follow the instructions at the Apache page. 

11. PHP and apache should have compiled OK, but Apache won't start. you need
to create a new user for Apache, eg www, or apache. You then change lines 252
and 253 of the conf/httpd.conf in Apache so that instead of 
    User nobody 
    Group nogroup 
you have something like 
    User www 
    Group sys 

This is because you can't run Apache as nobody under hp-ux. 
Apache and PHP should then work. 

Hope this helps somebody,
Paul Mckay.</PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
></DIV
><?php manualFooter('Unix/HP-UX installs','install.hpux.php');
?>