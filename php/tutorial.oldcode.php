<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP дтеU'),
  'prev' => array('tutorial.forms.php', 'Dealing with Forms'),
  'next' => array('tutorial.whatsnext.php', 'What\'s next?'),
  'up'   => array('tutorial.php', 'A simple tutorial'),
  'toc'  => array(
       array('tutorial.php#tutorial.requirements', 'What do I need?'),
     array('tutorial.firstpage.php', 'Your first PHP-enabled page'),
     array('tutorial.useful.php', 'Something Useful'),
     array('tutorial.forms.php', 'Dealing with Forms'),
     array('tutorial.oldcode.php', 'Using old code with new versions of PHP'),
     array('tutorial.whatsnext.php', 'What\'s next?'),
)));
manualHeader('Using old code with new versions of PHP','tutorial.oldcode.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="tutorial.oldcode"
></A
>Using old code with new versions of PHP</H1
><P
>&#13;    Now that PHP has grown to be a popular scripting language, there are
    more resources out there that have listings of code you can reuse
    in your own scripts. For the most part the developers of the PHP
    language have tried to be backwards compatible, so a script written
    for an older version should run (ideally) without changes in a newer
    version of PHP, in practice some changes will usually be needed.
   </P
><P
>&#13;    Two of the most important recent changes that affect old code are:
    <P
></P
><UL
><LI
><P
>&#13;       The deprecation of the old <TT
CLASS="varname"
>$HTTP_*_VARS</TT
> arrays
       (which need to be indicated as global when used inside a function or
       method).  The following 
       <A
HREF="language.variables.predefined.php#language.variables.superglobals"
>autoglobal arrays</A
>
       were introduced in PHP <A
HREF="http://www.php.net/release_4_1_0.php"
TARGET="_top"
>4.1.0</A
>. 
       They are: <TT
CLASS="varname"
>$_GET</TT
>, <TT
CLASS="varname"
>$_POST</TT
>, 
       <TT
CLASS="varname"
>$_COOKIE</TT
>, <TT
CLASS="varname"
>$_SERVER</TT
>, 
       <TT
CLASS="varname"
>$_ENV</TT
>, <TT
CLASS="varname"
>$_REQUEST</TT
>, and 
       <TT
CLASS="varname"
>$_SESSION</TT
>.  The older <TT
CLASS="varname"
>$HTTP_*_VARS</TT
>
       arrays, such as $HTTP_POST_VARS, still exist and have since PHP 3.
      </P
></LI
><LI
><P
>&#13;       External variables are no longer registered in the global scope by
       default.  In other words, as of PHP
       <A
HREF="http://www.php.net/release_4_2_0.php"
TARGET="_top"
>4.2.0</A
> the PHP directive 
       <A
HREF="configuration.directives.php#ini.register-globals"
>register_globals</A
> is 
       <SPAN
CLASS="emphasis"
><I
CLASS="emphasis"
>off</I
></SPAN
> by default in <TT
CLASS="filename"
>php.ini</TT
>. The preferred 
       method of accessing these values is via the autoglobal arrays mentioned
       above.  Older scripts, books, and tutorials may rely on this 
       directive being on.  If on, for example, one could use 
       <TT
CLASS="varname"
>$id</TT
> from the URL 
       <TT
CLASS="literal"
>http://www.example.com/foo.php?id=42</TT
>.  Whether on 
       or off, <TT
CLASS="varname"
>$_GET['id']</TT
> is available.
      </P
></LI
></UL
>
    For more details on these changes, see the section on 
    <A
HREF="language.variables.predefined.php"
>predefined variables</A
>
    and links therein.
   </P
></DIV
><?php manualFooter('Using old code with new versions of PHP','tutorial.oldcode.php');
?>