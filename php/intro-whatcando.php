<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP дтеU'),
  'prev' => array('introduction.php', 'Introduction'),
  'next' => array('tutorial.php', 'A simple tutorial'),
  'up'   => array('introduction.php', 'Introduction'),
  'toc'  => array(
     array('introduction.php#intro-whatis', 'What is PHP?'),
     array('intro-whatcando.php', 'What can PHP do?'),
)));
manualHeader('What can PHP do?','intro-whatcando.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="intro-whatcando"
></A
>What can PHP do?</H1
><P
>&#13;    Anything. PHP is mainly focused on server-side scripting,
    so you can do anything any other CGI program can do, such
    as collect form data, generate dynamic page content, or
    send and receive cookies. But PHP can do much more.
   </P
><P
>&#13;    There are three main fields where PHP scripts are used.
    <P
></P
><UL
><LI
><P
>&#13;       Server-side scripting. This is the most traditional
       and main target field for PHP. You need three things
       to make this work. The PHP parser (CGI or server
       module), a webserver and a web browser. You need to
       run the webserver, with a connected PHP installation.
       You can access the PHP program output with a web browser,
       viewing the PHP page through the server. See the
       <A
HREF="installation.php"
>installation instructions</A
>
       section for more information.
      </P
></LI
><LI
><P
>&#13;       Command line scripting. You can make a PHP script
       to run it without any server or browser.
       You only need the PHP parser to use it this way.
       This type of usage is ideal for scripts regularly
       executed using cron (on *nix or Linux) or Task Scheduler (on
       Windows). These scripts can also be used for simple text
       processing tasks. See the section about 
       <A
HREF="features.commandline.php"
>Command line usage of PHP</A
>
       for more information.
      </P
></LI
><LI
><P
>&#13;       Writing client-side GUI applications. PHP is probably
       not the very best language to write windowing
       applications, but if you know PHP very well, and
       would like to use some advanced PHP features in
       your client-side applications you can also use
       PHP-GTK to write such programs. You also have the
       ability to write cross-platform applications this way.
       PHP-GTK is an extension to PHP, not available in
       the main distribution. If you are interested
       in PHP-GTK, visit <A
HREF="http://gtk.php.net/"
TARGET="_top"
>it's
       own website</A
>.
      </P
></LI
></UL
>
   </P
><P
>&#13;    PHP can be used on all major operating systems, including
    Linux, many Unix variants (including HP-UX, Solaris and OpenBSD),
    Microsoft Windows, Mac OS X, RISC OS, and probably others.
    PHP has also support for most of the web servers today. This
    includes Apache, Microsoft Internet Information Server,
    Personal Web Server, Netscape and iPlanet servers, Oreilly
    Website Pro server, Caudium, Xitami, OmniHTTPd, and many
    others. For the majority of the servers PHP has a module,
    for the others supporting the CGI standard, PHP can work
    as a CGI processor.
   </P
><P
>&#13;    So with PHP, you have the freedom of choosing an operating
    system and a web server. Furthermore, you also have the choice
    of using procedural programming or object oriented
    programming, or a mixture of them. Although not every
    standard OOP feature is realized in the current version
    of PHP, many code libraries and large applications (including the
    PEAR library) are written only using OOP code.
   </P
><P
>&#13;    With PHP you are not limited to output HTML. PHP's abilities
    includes outputting images, PDF files and even Flash movies
    (using libswf and Ming) generated on the fly. You can also
    output easily any text, such as XHTML and any other XML file.
    PHP can autogenerate these files, and save them in the file
    system, instead of printing it out, forming a server-side
    cache for your dynamic content.
   </P
><P
>&#13;    One of the strongest and most significant feature in PHP is its
    support for a wide range of databases. Writing a database-enabled
    web page is incredibly simple. The following databases are currently
    supported:
    <A
NAME="AEN97"
></A
><BLOCKQUOTE
CLASS="BLOCKQUOTE"
><P
></P
><TABLE
BORDER="0"
><TBODY
><TR
><TD
>Adabas D</TD
><TD
>Ingres</TD
><TD
>Oracle (OCI7 and OCI8)</TD
></TR
><TR
><TD
>dBase</TD
><TD
>InterBase</TD
><TD
>Ovrimos</TD
></TR
><TR
><TD
>Empress</TD
><TD
>FrontBase</TD
><TD
>PostgreSQL</TD
></TR
><TR
><TD
>FilePro (read-only)</TD
><TD
>mSQL</TD
><TD
>Solid</TD
></TR
><TR
><TD
>Hyperwave</TD
><TD
>Direct MS-SQL</TD
><TD
>Sybase</TD
></TR
><TR
><TD
>IBM DB2</TD
><TD
>MySQL</TD
><TD
>Velocis</TD
></TR
><TR
><TD
>Informix</TD
><TD
>ODBC</TD
><TD
>Unix dbm</TD
></TR
></TBODY
></TABLE
><P
></P
></BLOCKQUOTE
>
    We also have a DBX database abstraction extension allowing you
    to transparently use any database supported by that extension.
    Additionally PHP supports ODBC, the Open Database Connection
    standard, so you can connect to any other database supporting
    this world standard.
   </P
><P
>&#13;    PHP also has support for talking to other services using protocols
    such as LDAP, IMAP, SNMP, NNTP, POP3, HTTP, COM (on Windows) and
    countless others. You can also open raw network sockets and
    interact using any other protocol. PHP has support for the WDDX
    complex data exchange between virtually all Web programming
    languages. Talking about interconnection, PHP has support for
    instantiation of Java objects and using them transparently
    as PHP objects. You can also use our CORBA extension to
    access remote objects.
   </P
><P
>&#13;    PHP has extremely useful text processing features, from the
    POSIX Extended or Perl regular expressions to parsing XML
    documents. For parsing and accessing XML documents, we
    support the SAX and DOM standards. You can use our XSLT
    extension to transform XML documents.
   </P
><P
>&#13;    While using PHP in the ecommerce field, you'll find
    the Cybercash payment, CyberMUT, VeriSign Payflow
    Pro and CCVS functions useful for your online payment
    programs.
   </P
><P
>&#13;    At last but not least, we have many other interesting
    extensions, the mnoGoSearch search engine functions,
    the IRC Gateway functions, many compression utilities
    (gzip, bz2), calendar conversion, translation...
   </P
><P
>&#13;    As you can see this page is not enough to list all
    the features and benefits PHP can offer. Read on in
    the sections about <A
HREF="installation.php"
>installing
    PHP</A
>, and see the <A
HREF="funcref.php"
>function
    reference</A
> part for explanation of the extensions
    mentioned here.
   </P
></DIV
><?php manualFooter('What can PHP do?','intro-whatcando.php');
?>