<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP ¤â¥U'),
  'prev' => array('install.unix.php', 'Installation on UNIX systems'),
  'next' => array('install.commandline.php', 'Servers-CGI/Commandline'),
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
manualHeader('Installation on Windows systems','install.windows.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="install.windows"
></A
>Installation on Windows systems</H1
><P
>&#13;    This section applies to Windows 95/98/Me and
    Windows NT/2000/XP. Do not expect PHP to work on
    16 bit platforms such as Windows 3.1. Sometimes
    we refer to the supported Windows platforms as Win32.
   </P
><P
>&#13;    There are two main ways to install PHP for Windows: either
    <A
HREF="install.windows.php#install.windows.manual"
>manually</A
>
    or by using the <A
HREF="install.windows.php#install.windows.installer"
>InstallShield</A
>
    installer.
   </P
><P
>&#13;    If you have Microsoft Visual Studio, you can also 
    <A
HREF="install.windows.php#install.windows.build"
>build</A
>
    PHP from the original source code.
   </P
><P
>&#13;    Once you have PHP installed on your Windows system, you may also
    want to <A
HREF="install.windows.php#install.windows.extensions"
>load various extensions</A
>
    for added functionality.
   </P
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.windows.installer"
></A
>Windows InstallShield</H2
><P
>&#13;     The Windows PHP installer available from the downloads page at 
     <A
HREF="http://www.php.net/"
TARGET="_top"
>http://www.php.net/</A
>, this installs the CGI version
     of PHP and, for IIS, PWS, and Xitami, configures the web server as
     well. Also note, that while the InstallShield installer is an
     easy way to make PHP work, it is restricted in many aspects, as
     automatic setup of extensions for example is not supported.
    </P
><P
>&#13;     Install your selected <SPAN
CLASS="acronym"
>HTTP</SPAN
> server on your system
     and make sure that it works.
    </P
><P
>&#13;     Run the executable installer and follow the instructions provided by
     the installation wizard. Two types of installation are supported -
     standard, which provides sensible defaults for all the settings it
     can, and advanced, which asks questions as it goes along.
    </P
><P
>&#13;     The installation wizard gathers enough information to set up the 
     <TT
CLASS="filename"
>php.ini</TT
> file and configure the web server to
     use PHP. For IIS and also PWS on NT Workstation, a list of all the
     nodes on the server with script map settings is displayed, and you
     can choose those nodes to which you wish to add the PHP script
     mappings.
    </P
><P
>&#13;     Once the installation has completed the installer will inform you
     if you need to restart your system, restart the server, or just
     start using PHP.
    </P
><DIV
CLASS="warning"
><P
></P
><TABLE
CLASS="warning"
BORDER="1"
WIDTH="100%"
><TR
><TD
ALIGN="CENTER"
><B
>&#35686;&#21578;</B
></TD
></TR
><TR
><TD
ALIGN="LEFT"
><P
>&#13;      Be aware, that this setup of PHP is not secure. If you would
      like to have a secure PHP setup, you'd better go on the manual
      way, and set every option carefully. This automatically working
      setup gives you an instantly working PHP installation, but it is
      not meant to be used on online servers.
     </P
></TD
></TR
></TABLE
></DIV
></DIV
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.windows.manual"
></A
>Manual Installation Steps</H2
><P
>&#13;     This install guide will help you manually install and configure
     PHP on your Windows webserver. You need to download the
     zip binary distribution from the downloads page at 
     <A
HREF="http://www.php.net/"
TARGET="_top"
>http://www.php.net/</A
>. The original version
     of this guide was compiled by <A
HREF="mailto:bob_silva@mail.umesd.k12.or.us"
TARGET="_top"
>Bob Silva</A
>, and can be found at <A
HREF="http://www.umesd.k12.or.us/php/win32install.html"
TARGET="_top"
>http://www.umesd.k12.or.us/php/win32install.html</A
>.
    </P
><P
>&#13;     This guide provides manual installation support for:
     <P
></P
><UL
><LI
><P
>&#13;        Personal Web Server 3 and 4 or newer
       </P
></LI
><LI
><P
>&#13;        Internet Information Server 3 and 4 or newer
       </P
></LI
><LI
><P
>&#13;        Apache 1.3.x
       </P
></LI
><LI
><P
>&#13;        OmniHTTPd 2.0b1 and up
       </P
></LI
><LI
><P
>&#13;        Oreilly Website Pro
       </P
></LI
><LI
><P
>&#13;        Xitami
       </P
></LI
><LI
><P
>&#13;        Netscape Enterprise Server, iPlanet
       </P
></LI
></UL
>
    </P
><P
>&#13;     PHP 4 for Windows comes in two flavours - a CGI executable (php.exe),
     and several SAPI modules (for example: php4isapi.dll). The latter form
     is new to PHP 4, and provides significantly improved performance and
     some new functionality.
    </P
><DIV
CLASS="warning"
><P
></P
><TABLE
CLASS="warning"
BORDER="1"
WIDTH="100%"
><TR
><TD
ALIGN="CENTER"
><B
>&#35686;&#21578;</B
></TD
></TR
><TR
><TD
ALIGN="LEFT"
><P
>&#13;      The SAPI modules have been significantly improved in the 4.1 release, 
      however, you may find that you encounter possible server errors or 
      other server modules such as ASP failing, in older systems.
     </P
></TD
></TR
></TABLE
></DIV
><P
>&#13;     If you choose one of the SAPI modules and use Windows 95, be sure
     to download the DCOM update from the <A
HREF="http://download.microsoft.com/msdownload/dcom/95/x86/en/dcom95.exe"
TARGET="_top"
>Microsoft DCOM pages</A
>. For the
     ISAPI module, an ISAPI 4.0 compliant Web server is required
     (tested on IIS 4.0, PWS 4.0 and IIS 5.0). IIS 3.0 is
     <SPAN
CLASS="emphasis"
><I
CLASS="emphasis"
>NOT</I
></SPAN
> supported. You should download and
     install the Windows NT 4.0 Option Pack with IIS 4.0 if you
     want native PHP support.
    </P
><P
>&#13;     The following steps should be performed on all installations
     before the server specific instructions.
     <P
></P
><UL
><LI
><P
>&#13;        Extract the distribution file to a directory of your choice.
        <TT
CLASS="filename"
>c:\php\</TT
> is a good start. You probably
        do not want to use a path in which spaces are included (for
        example: c:\program files\php is not a good idea). Some
        web servers will crash if you do.
       </P
></LI
><LI
><P
>&#13;        You need to ensure that the DLLs which PHP uses can be found.
        The precise DLLs involved depend on which web server you use
        and whether you want to run PHP as a CGI or as a server module.
        <TT
CLASS="filename"
>php4ts.dll</TT
> is always used. If you are
        using a server module (e.g. ISAPI or Apache) then you will
        need the relevant DLL from the <TT
CLASS="filename"
>sapi</TT
>
        folder. If you are using any PHP extension DLLs then you
        will need those as well. To make sure that the DLLs can be
        found, you can either copy them to the system directory
        (e.g. <TT
CLASS="filename"
>winnt/system32</TT
> or
        <TT
CLASS="filename"
>windows/system</TT
>) or you can make sure
        that they live in the same directory as the main PHP
        executable or DLL your web server will use (e.g.
        <TT
CLASS="filename"
>php.exe</TT
>, <TT
CLASS="filename"
>php4apache.dll</TT
>).
       </P
><P
>&#13;        The PHP binary, the SAPI modules, and some extensions rely on
        external DLLs for execution. Make sure that these DLLs in the 
        distribution exist in a directory that is in the Windows PATH.
        The best bet to do it is to copy the files below into your
        system directory, which is typically:
        <P
></P
><TABLE
BORDER="0"
><TBODY
><TR
><TD
>&#13;          <TT
CLASS="filename"
>c:\windows\system</TT
> for Windows 9x/ME
         </TD
></TR
><TR
><TD
>&#13;          <TT
CLASS="filename"
>c:\winnt\system32</TT
> for Windows NT/2000
         </TD
></TR
><TR
><TD
>&#13;          <TT
CLASS="filename"
>c:\windows\system32</TT
> for Windows XP
         </TD
></TR
></TBODY
></TABLE
><P
></P
>
        The files to copy are:
        <P
></P
><TABLE
BORDER="0"
><TBODY
><TR
><TD
>&#13;          <TT
CLASS="filename"
>php4ts.dll</TT
>, if it already exists there,
          overwrite it
         </TD
></TR
><TR
><TD
>&#13;          The files in your distribution's 'dlls' directory.
          If you have them already installed on your system, overwrite them 
          only if something doesn't work correctly (Before overwriting them, 
          it is a good idea to make a backup of them, or move them to
          another folder - just in case something goes wrong).
         </TD
></TR
></TBODY
></TABLE
><P
></P
>
       </P
><P
>&#13;        Download the latest version of the Microsoft Data Access Components
        (MDAC) for your platform, especially if you use Microsoft Windows
        9x/NT4. MDAC is available at <A
HREF="http://www.microsoft.com/data/"
TARGET="_top"
>http://www.microsoft.com/data/</A
>.
       </P
></LI
><LI
><P
>&#13;        Copy your chosen ini file (see below) to your 
        '%WINDOWS%' directory on Windows 9x/Me or to your 
        '%SYSTEMROOT%' directory under Windows NT/2000/XP
        and rename it to <TT
CLASS="filename"
>php.ini</TT
>. Your 
        '%WINDOWS%' or '%SYSTEMROOT%' directory is
        typically:
        <P
></P
><TABLE
BORDER="0"
><TBODY
><TR
><TD
><TT
CLASS="filename"
>c:\windows</TT
> for Windows 9x/ME/XP</TD
></TR
><TR
><TD
><TT
CLASS="filename"
>c:\winnt</TT
> or <TT
CLASS="filename"
>c:\winnt40</TT
> for NT/2000 servers</TD
></TR
></TBODY
></TABLE
><P
></P
>
       </P
><P
>&#13;        There are two ini files distributed in the zip file,
        <TT
CLASS="filename"
>php.ini-dist</TT
> and
        <TT
CLASS="filename"
>php.ini-optimized</TT
>. We advise
        you to use <TT
CLASS="filename"
>php.ini-optimized</TT
>,
        because we optimized the default settings in this
        file for performance, and security. The best is to
        study all the <A
HREF="configuration.php#configuration.file"
>ini
        settings</A
> and set every element manually yourself.
        If you would like to achieve the best security, then this
        is the way for you, although PHP works fine with these
        default ini files.
       </P
></LI
><LI
><P
>&#13;        Edit your new <TT
CLASS="filename"
>php.ini</TT
> file:
        <P
></P
><UL
><LI
><P
>&#13;           You will need to change the 'extension_dir' setting to
           point to your php-install-dir, or where you have placed
           your <TT
CLASS="filename"
>php_*.dll</TT
> files. Please do not
           forget the last backslash. ex:
           <TT
CLASS="filename"
>c:\php\extensions\</TT
>
          </P
></LI
><LI
><P
>&#13;           If you are using OmniHTTPd, do not follow the next step.
           Set the 'doc_root' to point to your webservers
           document_root. For example: <TT
CLASS="filename"
>c:\apache\htdocs</TT
>
           or <TT
CLASS="filename"
>c:\webroot</TT
>
          </P
></LI
><LI
><P
>&#13;           Choose which extensions you would like to load when PHP
           starts. See the section about
           <A
HREF="install.windows.php#install.windows.extensions"
>Windows
           extensions</A
>, about how to set up one, and what
           is already built in. Note that on a new installation
           it is advisable to first get PHP working and tested
           without any extensions before enabling them in
           <TT
CLASS="filename"
>php.ini</TT
>.
          </P
></LI
><LI
><P
>&#13;           On PWS and IIS, you can set the <TT
CLASS="filename"
>browscap.ini</TT
>
           to point to:
           <TT
CLASS="filename"
>c:\windows\system\inetsrv\browscap.ini</TT
> on
           Windows 9x/Me,
           <TT
CLASS="filename"
>c:\winnt\system32\inetsrv\browscap.ini</TT
> on
           NT/2000, and
           <TT
CLASS="filename"
>c:\windows\system32\inetsrv\browscap.ini</TT
>
           on XP.
          </P
></LI
><LI
><P
>&#13;           Note that the <TT
CLASS="filename"
>mibs</TT
> directory supplied
           with the Windows distribution contains support files for
           SNMP. This directory should be moved to
           <TT
CLASS="filename"
>DRIVE:\usr\mibs</TT
> (<TT
CLASS="filename"
>DRIVE</TT
>
           being the drive where PHP is installed.)
          </P
></LI
><LI
><P
>&#13;           If you're using NTFS on Windows NT, 2000 or XP, make sure that
           the user running the webserver has read permissions to your
           <TT
CLASS="filename"
>php.ini</TT
> (e.g. make it readable by Everyone).
          </P
></LI
></UL
>
       </P
></LI
><LI
><P
>&#13;        For PWS give execution permission to the webroot:
        <P
></P
><UL
><LI
><P
>&#13;           Start PWS Web Manager
          </P
></LI
><LI
><P
>&#13;           Edit Properties of the "Home"-Directory
          </P
></LI
><LI
><P
>&#13;           Select the "execute"-Checkbox
          </P
></LI
></UL
>
       </P
></LI
></UL
>
    </P
></DIV
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.windows.build"
></A
>Building from source</H2
><P
>&#13;     Before getting started, it is worthwhile answering the question:
     "Why is building on Windows so hard?" Two reasons come to mind:
    </P
><P
></P
><OL
TYPE="1"
><LI
><P
>&#13;      Windows does not (yet) enjoy a large community of developers
      who are willing to freely share their source. As a direct
      result, the necessary investment in infrastructure required
      to support such development hasn't been made.  By and large,
      what is available has been made possible by the porting of
      necessary utilities from Unix. Don't be surprised if some of
      this heritage shows through from time to time.
     </P
></LI
><LI
><P
>&#13;      Pretty much all of the instructions that follow are of the
      "set and forget" variety. So sit back and try follow the
      instructions below as faithfully as you can.
     </P
></LI
></OL
><DIV
CLASS="sect3"
><H3
CLASS="sect3"
><A
NAME="install.windows.build.prepare"
></A
>Preparations</H3
><P
>&#13;      Before you get started, you have a lot to download...
     </P
><P
></P
><UL
><LI
><P
>&#13;       For starters, get the Cygwin toolkit from the closest <A
HREF="http://sources.redhat.com/cygwin/download.html"
TARGET="_top"
>cygwin</A
>
       mirror site.  This will provide you most of the popular GNU 
       utilities used by the build process.
      </P
></LI
><LI
><P
>&#13;       Download the rest of the build tools you will need from the PHP
       site at <A
HREF="http://www.php.net/extra/win32build.zip"
TARGET="_top"
>http://www.php.net/extra/win32build.zip</A
>.
      </P
></LI
><LI
><P
>&#13;       Get the source code for the DNS name resolver used by PHP
       at <A
HREF="http://www.php.net/extra/bindlib_w32.zip"
TARGET="_top"
>http://www.php.net/extra/bindlib_w32.zip</A
>. This
       is a replacement for the <TT
CLASS="filename"
>resolv.lib</TT
>
       library included in <TT
CLASS="filename"
>win32build.zip</TT
>.
      </P
></LI
><LI
><P
>&#13;       If you don't already have an unzip utility, you will
       need one.  A free version is available from <A
HREF="http://www.cdrom.com/pub/infozip/UnZip.html"
TARGET="_top"
>InfoZip</A
>.
      </P
></LI
><LI
><P
>If you plan to compile PHP as a static Apache
      module you will also need the 
      <A
HREF="http://httpd.apache.org/dist/httpd/"
TARGET="_top"
>Apache sources</A
> 
      of your version of Apache.
      </P
></LI
></UL
><P
>&#13;      Finally, you are going to need the source to PHP 4 itself.
      You can get the latest development version using <A
HREF="http://www.php.net/anoncvs.php"
TARGET="_top"
>anonymous CVS</A
>. If you get
      a <A
HREF="http://snaps.php.net/"
TARGET="_top"
>snapshot</A
> or a <A
HREF="http://www.php.net/downloads.php"
TARGET="_top"
>source</A
> tarball, you
      not only will have to untar and ungzip it, but you will have to
      convert the bare linefeeds to crlf's in the <TT
CLASS="filename"
>*.dsp</TT
>
      and <TT
CLASS="filename"
>*.dsw</TT
> files before Microsoft Visual C++
      will have anything to do with them.
     </P
><DIV
CLASS="note"
><BLOCKQUOTE
CLASS="note"
><P
><B
>&#27880;: </B
>
       Place the <TT
CLASS="filename"
>Zend</TT
> and
       <TT
CLASS="filename"
>TSRM</TT
> directories inside the
       <TT
CLASS="filename"
>php4</TT
> directory in order for the projects
       to be found during the build process.
      </P
></BLOCKQUOTE
></DIV
></DIV
><DIV
CLASS="sect3"
><H3
CLASS="sect3"
><A
NAME="install.windows.build.install"
></A
>Putting it all together</H3
><P
></P
><UL
><LI
><P
>&#13;       Follow the instructions for installing the unzip utility of
       your choosing.
      </P
></LI
><LI
><P
>&#13;        Execute <TT
CLASS="filename"
>setup.exe</TT
> and follow the installation
        instructions.  If you choose to install to a path other than
        <TT
CLASS="filename"
>c:\cygnus</TT
>, let the build process know by setting
        the Cygwin environment variable. On Windows 95/98 setting
        an environment variable can be done by placing a line in
        your <TT
CLASS="filename"
>autoexec.bat</TT
>. On Windows NT, go to My
        Computer =&#62; Control Panel =&#62; System and select the 
        environment tab.
       </P
></LI
></UL
><DIV
CLASS="warning"
><P
></P
><TABLE
CLASS="warning"
BORDER="1"
WIDTH="100%"
><TR
><TD
ALIGN="CENTER"
><B
>&#35686;&#21578;</B
></TD
></TR
><TR
><TD
ALIGN="LEFT"
><P
>&#13;         Make a temporary directory for Cygwin to use, otherwise many
         commands (particularly bison) will fail. On Windows 95/98,
         <TT
CLASS="userinput"
><B
>mkdir C:\TMP</B
></TT
>. For Windows NT,
         <TT
CLASS="userinput"
><B
>mkdir %SystemDrive%\tmp</B
></TT
>.
        </P
></TD
></TR
></TABLE
></DIV
><P
></P
><UL
><LI
><P
>&#13;       Make a directory and unzip <TT
CLASS="filename"
>win32build.zip</TT
> into it.
      </P
></LI
><LI
><P
>&#13;        Launch Microsoft Visual C++, and from the menu select
        Tools =&#62; Options. In the dialog, select the
        directories tab. Sequentially change the dropdown
        to Executables, Includes, and Library files,
        and ensure that <TT
CLASS="filename"
>cygwin\bin</TT
>,
        <TT
CLASS="filename"
>win32build\include</TT
>, and
        <TT
CLASS="filename"
>win32build\lib</TT
> are in each list,
        respectively. (To add an entry, select a blank line
        at the end of the list and begin typing).  Typical entries
        will look like this:
       </P
><P
></P
><UL
><LI
><P
>&#13;         <TT
CLASS="filename"
>c:\cygnus\bin</TT
>
        </P
></LI
><LI
><P
>&#13;         <TT
CLASS="filename"
>c:\php-win32build\include</TT
>
        </P
></LI
><LI
><P
>&#13;         <TT
CLASS="filename"
>c:\php-win32build\lib</TT
>
        </P
></LI
></UL
><P
>&#13;        Press OK, and exit out of Visual C++.
       </P
></LI
><LI
><P
>&#13;        Make another directory and unzip <TT
CLASS="filename"
>bindlib_w32.zip</TT
>
        into it. Decide whether you want to have debug symbols available
        (bindlib - Win32 Debug) or not (bindlib - Win32 Release).
        Build the appropriate configuration:
       </P
><P
></P
><UL
><LI
><P
>&#13;         For GUI users, launch VC++, and then select File =&#62; Open
         Workspace and select bindlib.  Then select Build=&#62;Set
         Active Configuration and select the desired configuration.
         Finally select Build=&#62;Rebuild All.
        </P
></LI
><LI
><P
>&#13;          For command line users, make sure that you either have
          the C++ environment variables registered, or have run
          <B
CLASS="command"
>vcvars.bat</B
>, and then execute one of the
          following:
         </P
><P
></P
><UL
><LI
><P
>&#13;            <TT
CLASS="userinput"
><B
>msdev bindlib.dsp /MAKE "bindlib - Win32 Debug"</B
></TT
>
           </P
></LI
><LI
><P
>&#13;            <TT
CLASS="userinput"
><B
>msdev bindlib.dsp /MAKE "bindlib - Win32 Release"</B
></TT
>
           </P
></LI
></UL
></LI
><LI
><P
>&#13;         At this point, you should have a usable
         <TT
CLASS="filename"
>resolv.lib</TT
> in either your
         <TT
CLASS="filename"
>Debug</TT
> or <TT
CLASS="filename"
>Release</TT
>
         subdirectories.  Copy this file into your
         <TT
CLASS="filename"
>win32build\lib</TT
> directory over the
         file by the same name found in there.
        </P
></LI
></UL
></LI
></UL
></DIV
><DIV
CLASS="sect3"
><H3
CLASS="sect3"
><A
NAME="install.windows.build.compile"
></A
>Compiling</H3
><P
>&#13;      The best way to get started is to build the standalone/CGI version.
     </P
><P
></P
><UL
><LI
><P
>&#13;       For GUI users, launch VC++, and then select File =&#62; Open
       Workspace and select php4ts.  Then select Build=&#62;Set Active
       Configuration and select the desired configuration. Finally
       select Build=&#62;Rebuild All.
      </P
></LI
><LI
><P
>&#13;        For command line users, make sure that you either have
        the C++ environment variables registered, or have run
        <B
CLASS="command"
>vcvars.bat</B
>, and then execute one of the
        following:
       </P
><P
></P
><UL
><LI
><P
>&#13;         <TT
CLASS="userinput"
><B
>msdev php4ts.dsp /MAKE "php4ts - Win32 Debug_TS"</B
></TT
>
        </P
></LI
><LI
><P
>&#13;         <TT
CLASS="userinput"
><B
>msdev php4ts.dsp /MAKE "php4ts - Win32 Release_TS"</B
></TT
>
        </P
></LI
><LI
><P
>&#13;         At this point, you should have a usable
         <TT
CLASS="filename"
>php.exe</TT
> in either
         your <TT
CLASS="filename"
>Debug_TS</TT
> or
         <TT
CLASS="filename"
>Release_TS</TT
> subdirectories.
        </P
></LI
></UL
></LI
></UL
><P
>&#13;      Repeat the above steps with <TT
CLASS="filename"
>php4isapi.dsp</TT
>
      (which can be found in <TT
CLASS="filename"
>sapi\isapi</TT
>) in
      order to build the code necessary for integrating PHP with
      Microsoft IIS.
     </P
><P
>&#13;      It is possible to do minor customization to the build process by editing
      the main/config.win32.h.in file.
     </P
></DIV
></DIV
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="install.windows.extensions"
></A
>Installation of Windows extensions</H2
><P
>&#13;     After installing PHP and a webserver on Windows, you will
     probably want to install some extensions for added functionality.
     The following table describes some of the extensions available. You
     can choose which extensions you would like to load when PHP starts
     by uncommenting the: 'extension=php_*.dll' lines in
     <TT
CLASS="filename"
>php.ini</TT
>. You can also load a module dynamically
     in your script using <A
HREF="function.dl.php"
><B
CLASS="function"
>dl()</B
></A
>.
    </P
><P
>&#13;     The DLLs for PHP extensions are prefixed with 'php_' in PHP 4 (and 
     'php3_' in PHP 3). This prevents confusion between PHP extensions 
     and their supporting libraries. 
    </P
><DIV
CLASS="note"
><BLOCKQUOTE
CLASS="note"
><P
><B
>&#27880;: </B
>
       In PHP 4.0.6 BCMath, Calendar, COM, FTP, MySQL, ODBC, PCRE, 
       Session, WDDX and XML support is <SPAN
CLASS="emphasis"
><I
CLASS="emphasis"
>built in</I
></SPAN
>.
       You don't need to load any additional extensions in order to 
       use these functions. See your distributions
       <TT
CLASS="filename"
>README.txt</TT
> or <TT
CLASS="filename"
>install.txt</TT
>
       for a list of built in modules.
     </P
></BLOCKQUOTE
></DIV
><DIV
CLASS="note"
><BLOCKQUOTE
CLASS="note"
><P
><B
>&#27880;: </B
>
      Some of these extensions need extra DLLs to work. Couple of them can be
      found in the distribution package, in the 'dlls' folder but
      some, for example Oracle (php_oci8.dll) require DLLs which are
      not bundled with the distribution package.
     </P
><P
>&#13;      Copy the bundled DLLs from 'DLLs' folder to your Windows 
      PATH, safe places are:
      <P
></P
><TABLE
BORDER="0"
><TBODY
><TR
><TD
>c:\windows\system for Windows 9x/Me</TD
></TR
><TR
><TD
>c:\winnt\system32 for Windows NT/2000</TD
></TR
><TR
><TD
>c:\windows\system32 for Windows XP</TD
></TR
></TBODY
></TABLE
><P
></P
>
      If you have them already installed on your system, overwrite them
      only if something doesn't work correctly (Before overwriting them,
      it is a good idea to make a backup of them, or move them to
      another folder - just in case something goes wrong).
     </P
></BLOCKQUOTE
></DIV
><P
>&#13;     <DIV
CLASS="table"
><A
NAME="AEN742"
></A
><P
><B
>&#34920;&#26684; 3-1. PHP Extensions</B
></P
><TABLE
BORDER="1"
CLASS="CALSTABLE"
><THEAD
><TR
><TH
ALIGN="LEFT"
VALIGN="MIDDLE"
>Extension</TH
><TH
ALIGN="LEFT"
VALIGN="MIDDLE"
>Description</TH
><TH
ALIGN="LEFT"
VALIGN="MIDDLE"
>Notes</TH
></TR
></THEAD
><TBODY
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_bz2.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.bzip2.php"
>bzip2</A
> compression functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_calendar.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.calendar.php"
>Calendar</A
> conversion functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Built in since PHP 4.0.3</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_cpdf.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.cpdf.php"
>ClibPDF</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_crack.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.crack.php"
>Crack</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php3_crypt.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Crypt functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>unknown</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_ctype.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.ctype.php"
>ctype</A
> family functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_curl.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.curl.php"
>CURL</A
>, Client URL library functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: libeay32.dll, ssleay32.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_cybercash.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.cybercash.php"
>Cybercash</A
> payment functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_db.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.dbm.php"
>DBM</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Deprecated. Use DBA instead (php_dba.dll)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_dba.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.dba.php"
>DBA</A
>: DataBase (dbm-style) Abstraction layer functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_dbase.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.dbase.php"
>dBase</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php3_dbm.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Berkeley DB2 library</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>unknown</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_domxml.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.domxml.php"
>DOM XML</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: libxml2.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_dotnet.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.dotnet.php"
>.NET</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_exif.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="function.read-exif-data.php"
>Read EXIF</A
> headers from JPEG</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_fbsql.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.fbsql.php"
>FrontBase</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_fdf.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.fdf.php"
>FDF</A
>: Forms Data Format functions.</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: fdftk.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_filepro.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.filepro.php"
>filePro</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Read-only access</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_ftp.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.ftp.php"
>FTP</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Built-in since PHP 4.0.3</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_gd.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.image.php"
>GD</A
> library image functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_gettext.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.gettext.php"
>Gettext</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: gnu_gettext.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_hyperwave.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.hyperwave.php"
>HyperWave</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_iconv.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.iconv.php"
>ICONV</A
> characterset conversion</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: iconv-1.3.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_ifx.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.ifx.php"
>Informix</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: Informix libraries</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_iisfunc.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>IIS management functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_imap.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.imap.php"
>IMAP</A
> POP3 and NNTP functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>PHP 3: php3_imap4r1.dll</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_ingres.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.ingres.php"
>Ingres II</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: Ingres II libraries</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_interbase.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.ibase.php"
>InterBase</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: gds32.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_java.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.java.php"
>Java</A
> extension</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: jvm.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_ldap.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.ldap.php"
>LDAP</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: libsasl.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_mhash.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.mhash.php"
>Mhash</A
> Functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_ming.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.ming.php"
>Ming</A
> functions for Flash</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_msql.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.msql.php"
>mSQL</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: msql.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php3_msql1.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>mSQL 1 client</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>unknown</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php3_msql2.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>mSQL 2 client</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>unknown</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_mssql.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.mssql.php"
>MSSQL</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: ntwdblib.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php3_mysql.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.mysql.php"
>MySQL</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Built-in in PHP 4</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php3_nsmail.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Netscape mail functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>unknown</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php3_oci73.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Oracle functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>unknown</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_oci8.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.oci8.php"
>Oracle 8</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: Oracle 8 client libraries</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_openssl.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.openssl.php"
>OpenSSL</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: libeay32.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_oracle.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.oracle.php"
>Oracle</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: Oracle 7 client libraries</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_pdf.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.pdf.php"
>PDF</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_pgsql.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.pgsql.php"
>PostgreSQL</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_printer.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.printer.php"
>Printer</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_xslt.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.xslt.php"
>XSLT</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: sablot.dll (bundled)</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_snmp.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.snmp.php"
>SNMP</A
> get and walk functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>NT only!</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_sybase_ct.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.sybase.php"
>Sybase</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>Requires: Sybase client libraries</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_yaz.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.yaz.php"
>YAZ</A
> functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>php_zlib.dll</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><A
HREF="ref.zlib.php"
>ZLib</A
> compression functions</TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
>None</TD
></TR
></TBODY
></TABLE
></DIV
>
    </P
></DIV
></DIV
><?php manualFooter('Installation on Windows systems','install.windows.php');
?>