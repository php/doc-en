<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP ¤â¥U'),
  'prev' => array('install.linux.php', 'Unix/Linux installs'),
  'next' => array('install.openbsd.php', 'Unix/OpenBSD installs'),
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
manualHeader('Unix/Mac OS X installs','install.macosx.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="install.macosx"
></A
>Unix/Mac OS X installs</H1
><P
>&#13;  This section contains notes and hints specific to installing
  PHP on Mac OS X Server.
 </P
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.macosx.packages"
></A
>Using Packages</H2
><P
>&#13;   There are a few pre-packaged and pre-compiled versions of PHP for
   Mac OS X. This can help in setting up a standard
   configuration, but if you need to have a different set of features
   (such as a secure server, or a different database driver), you may
   need to build PHP and/or your web server yourself. If you are unfamiliar
   with building and compiling your own software, it's worth
   checking whether somebody has already built a packaged
   version of PHP with the features you need.
  </P
></DIV
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.macosx.compile"
></A
>Compiling for OS X server</H2
><P
>&#13;   There are two slightly different versions of Mac OS X, client and
   server. The following is for OS X Server.
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
NAME="install.macosx.compile.example"
></A
><P
><B
>&#31684;&#20363; 3-2. Mac OS X server install</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="screen"
>1. Get the latest distributions of Apache and PHP
2. Untar them, and run the configure program on Apache like so.
    ./configure --exec-prefix=/usr \ 
    --localstatedir=/var \ 
    --mandir=/usr/share/man \ 
    --libexecdir=/System/Library/Apache/Modules \ 
    --iconsdir=/System/Library/Apache/Icons \ 
    --includedir=/System/Library/Frameworks/Apache.framework/Versions/1.3/Headers \ 
    --enable-shared=max \ 
    --enable-module=most \ 
    --target=apache 

4. You may also want to add this line: 
    setenv OPTIM=-O2 
    If you want the compiler to do some optimization. 
    
5. Next, go to the PHP 4 source directory and configure it. 
    ./configure --prefix=/usr \ 
    --sysconfdir=/etc \ 
    --localstatedir=/var \ 
    --mandir=/usr/share/man \ 
    --with-xml \ 
    --with-apache=/src/apache_1.3.12 

    If you have any other additions (MySQL, GD, etc.), be sure to add
    them here. For the --with-apache string, put in the path to your 
    apache source directory, for example "/src/apache_1.3.12". 
6. make
7. make install    
    This will add a directory to your Apache source directory under
    src/modules/php4.
    
8. Now, reconfigure Apache to build in PHP 4.
    ./configure --exec-prefix=/usr \ 
    --localstatedir=/var \ 
    --mandir=/usr/share/man \ 
    --libexecdir=/System/Library/Apache/Modules \ 
    --iconsdir=/System/Library/Apache/Icons \ 
    --includedir=/System/Library/Frameworks/Apache.framework/Versions/1.3/Headers \ 
    --enable-shared=max \ 
    --enable-module=most \ 
    --target=apache \ 
    --activate-module=src/modules/php4/libphp4.a 

    You may get a message telling you that libmodphp4.a is out of date.
    If so, go to the src/modules/php4 directory inside your apache
    source directory and run this command: 

    ranlib libmodphp4.a 

    Then go back to the root of the apache source directory and run the
    above configure command again. That'll bring the link table up to
    date. 

9. make

10. make install

11. copy and rename the php.ini-dist file to your "bin" directory from your
    PHP 4 source directory:
    cp php.ini-dist /usr/local/bin/php.ini 

    or (if your don't have a local directory) 

    cp php.ini-dist /usr/bin/php.ini</PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
><P
>&#13;   Other examples for
   <A
HREF="http://www.stepwise.com/Articles/Workbench/Apache-1.3.14-MacOSX.html"
TARGET="_top"
>Mac OS X client</A
>
   and
   <A
HREF="http://www.stepwise.com/Articles/Workbench/Apache-1.3.14-MacOSX.html"
TARGET="_top"
>Mac OS X server</A
>
   are available at <A
HREF="http://www.stepwise.com/"
TARGET="_top"
>Stepwise</A
>.
  </P
></DIV
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.macosx.client"
></A
>Compiling for MacOS X client</H2
><P
>&#13;   Those tips are graciously provided by <A
HREF="http://www.entropy.ch/software/macosx/"
TARGET="_top"
>Marc Liyanage</A
>.
  </P
><P
>&#13;   The PHP module for the Apache web server included in Mac OS X.
   This version includes support for the MySQL and PostgreSQL databases.
  </P
><P
>&#13;   NOTE: Be careful when you do this, you could screw up your Apache web server!
  </P
><P
>&#13;   Do this to install:
   <P
></P
><UL
><LI
><P
>&#13;      1.	Open a terminal window 
     </P
></LI
><LI
><P
>&#13;      2.	Type "wget http://www.diax.ch/users/liyanage/software/macosx/libphp4.so.gz",
      wait for download to finish 
     </P
></LI
><LI
><P
>&#13;      3.	Type "gunzip libphp4.so.gz" 
     </P
></LI
><LI
><P
>&#13;      4.	Type "sudo apxs -i -a -n php4 libphp4.so" 
     </P
></LI
></UL
>
   Now type "<TT
CLASS="literal"
>sudo open -a TextEdit /etc/httpd/httpd.conf</TT
>"
   TextEdit will open with the web server configuration file. Locate these 
   two lines towards the end of the file: (Use the Find command)
   <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="apache"
>#AddType application/x-httpd-php .php 
   #AddType application/x-httpd-php-source .phps</PRE
></TD
></TR
></TABLE
>
   Remove the two hash marks (<TT
CLASS="literal"
>#</TT
>), then save the file and quit TextEdit.
  </P
><P
>&#13;   Finally, type "<TT
CLASS="literal"
>sudo apachectl graceful</TT
>" to restart the web server.
  </P
><P
>&#13;   PHP should now be up and running. You can test it by dropping a file into 
   your "Sites" folder which is called "test.php". Into that file, write this
   line: "<TT
CLASS="literal"
>&#60;?php phpinfo() ?&#62;</TT
>".
  </P
><P
>&#13;   Now open up <TT
CLASS="literal"
>127.0.0.1/~your_username/test.php</TT
> in your web browser.
   You should see a status table with information about the PHP module.
  </P
></DIV
></DIV
><?php manualFooter('Unix/Mac OS X installs','install.macosx.php');
?>