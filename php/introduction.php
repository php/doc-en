<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP 手冊'),
  'prev' => array('getting-started.php', '入門'),
  'next' => array('intro-whatcando.php', 'What can PHP do?'),
  'up'   => array('getting-started.php', '入門'),
  'toc'  => array(
      array('introduction.php', 'Introduction'),
      array('tutorial.php', 'A simple tutorial'),
      array('installation.php', '安裝'),
      array('configuration.php', 'Configuration'),
      array('security.php', 'Security'),
)));
manualHeader('Introduction','introduction.php');
?><DIV
CLASS="chapter"
><H1
><A
NAME="introduction"
></A
>&#31456; 1. Introduction</H1
><DIV
CLASS="TOC"
><DL
><DT
><B
>&#20839;&#23481;&#30446;&#37636;</B
></DT
><DT
><A
HREF="introduction.php#intro-whatis"
>What is PHP?</A
></DT
><DT
><A
HREF="intro-whatcando.php"
>What can PHP do?</A
></DT
></DL
></DIV
><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="intro-whatis"
></A
>What is PHP?</H1
><P
>&#13;    <SPAN
CLASS="acronym"
>PHP</SPAN
> (recursive acronym for "PHP: Hypertext
    Preprocessor") is a widely-used Open Source general-purpose
    scripting language that is especially suited for Web
    development and can be embedded into HTML.
   </P
><P
>&#13;    Simple answer, but what does that mean? An example:
   </P
><P
>&#13;    <TABLE
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
NAME="AEN70"
></A
><P
><B
>&#31684;&#20363; 1-1. An introductory example</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="php"
>&#60;html&#62;
    &#60;head&#62;
        &#60;title&#62;Example&#60;/title&#62;
    &#60;/head&#62;
    &#60;body&#62;

        &#60;?php 
        echo "Hi, I'm a PHP script!"; 
        ?&#62;

    &#60;/body&#62;
&#60;/html&#62;</PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
>
   </P
><P
>&#13;    Notice how this is different from a script written in other
    languages like Perl or C -- instead of writing a program with lots
    of commands to output HTML, you write an HTML script with some
    embedded code to do something (in this case, output some
    text). The PHP code is enclosed in special <A
HREF="language.basic-syntax.php#language.basic-syntax.phpmode"
>start and end tags</A
>
    that allow you to jump into and out of "PHP mode".
   </P
><P
>&#13;    What distinguishes PHP from something like client-side JavaScript
    is that the code is executed on the server. If you were to have a
    script similar to the above on your server, the client would receive
    the results of running that script, with no way of determining what
    the underlying code may be. You can even configure your web server
    to process all your HTML files with PHP, and then there's really no
    way that users can tell what you have up your sleeve.
   </P
><P
>&#13;    The best things in using PHP are that it is extremely simple
    for a newcomer, but offers many advanced features for
    a professional programmer. Don't be afraid reading the long
    list of PHP's features. You can jump in, in a short time, and
    start writing simple scripts in a few hours.
   </P
><P
>&#13;    Although PHP's development is focused on server-side scripting,
    you can do much more with it. Read on, and see more in the
    <A
HREF="intro-whatcando.php"
>What can PHP do?</A
> section.
   </P
></DIV
></DIV
><?php manualFooter('Introduction','introduction.php');
?>