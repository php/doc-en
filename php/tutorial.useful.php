<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP дтеU'),
  'prev' => array('tutorial.firstpage.php', 'Your first PHP-enabled page'),
  'next' => array('tutorial.forms.php', 'Dealing with Forms'),
  'up'   => array('tutorial.php', 'A simple tutorial'),
  'toc'  => array(
       array('tutorial.php#tutorial.requirements', 'What do I need?'),
     array('tutorial.firstpage.php', 'Your first PHP-enabled page'),
     array('tutorial.useful.php', 'Something Useful'),
     array('tutorial.forms.php', 'Dealing with Forms'),
     array('tutorial.oldcode.php', 'Using old code with new versions of PHP'),
     array('tutorial.whatsnext.php', 'What\'s next?'),
)));
manualHeader('Something Useful','tutorial.useful.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="tutorial.useful"
></A
>Something Useful</H1
><P
>&#13;    Let's do something a bit more useful now. We are going to check
    what sort of browser the person viewing the page is using.
    In order to do that we check the user agent string that the browser
    sends as part of its HTTP request. This information is stored in a <A
HREF="language.variables.php"
>variable</A
>. Variables always start
    with a dollar-sign in PHP. The variable we are interested in right now 
    is <TT
CLASS="varname"
>$_SERVER["HTTP_USER_AGENT"]</TT
>.
   </P
><DIV
CLASS="note"
><BLOCKQUOTE
CLASS="note"
><P
><B
>PHP Autoglobals Note: </B
>
     <A
HREF="reserved.variables.php#reserved.variables.server"
>$_SERVER</A
> is a 
     special reserved PHP variable that contains all web server information.
     It's known as an Autoglobal (or Superglobal).  See the related manual page on
     <A
HREF="language.variables.predefined.php#language.variables.superglobals"
>Autoglobals</A
>
     for more information.  These special variables were introduced in PHP 
     <A
HREF="http://www.php.net/release_4_1_0.php"
TARGET="_top"
>4.1.0</A
>.  Before this time, we used
     the older <TT
CLASS="varname"
>$HTTP_*_VARS</TT
> arrays instead,
     such as <TT
CLASS="varname"
>$HTTP_SERVER_VARS</TT
>.  Although deprecated, 
     these older variables still exist.  (See also the note on
     <A
HREF="tutorial.oldcode.php"
>old code</A
>.)
    </P
></BLOCKQUOTE
></DIV
><P
>&#13;    To display this variable, we can simply do:
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
NAME="AEN187"
></A
><P
><B
>&#31684;&#20363; 2-2. Printing a variable (Array element)</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="php"
>&#60;?php echo $_SERVER["HTTP_USER_AGENT"]; ?&#62;</PRE
></TD
></TR
></TABLE
><P
>&#13;     A sample output of this script may be:
     <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="html"
>Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)</PRE
></TD
></TR
></TABLE
>
    </P
></DIV
></TD
></TR
></TABLE
>
   </P
><P
>&#13;    There are many <A
HREF="language.types.php"
>types</A
> of 
    variables available in PHP.  In the above example we printed 
    an <A
HREF="language.types.array.php"
>Array</A
> element.
    Arrays can be very useful.
   </P
><P
>&#13;    <TT
CLASS="varname"
>$_SERVER</TT
> is just one variable that's automatically 
    made available to you by PHP.  A list can be seen in the 
    <A
HREF="reserved.variables.php"
>Reserved Variables</A
> section 
    of the manual or you can get a complete list of them by creating
    a file that looks like this:
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
NAME="AEN199"
></A
><P
><B
>&#31684;&#20363; 2-3. Show all predefined variables with <A
HREF="function.phpinfo.php"
><B
CLASS="function"
>phpinfo()</B
></A
></B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="php"
>&#60;?php phpinfo(); ?&#62;</PRE
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
>&#13;    If you load up this file in your browser you will see a page
    full of information about PHP along with a list of all the
    variables available to you.
   </P
><P
>&#13;    You can put multiple PHP statements inside a PHP tag and create
    little blocks of code that do more than just a single echo.
    For example, if we wanted to check for Internet Explorer we
    could do something like this:
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
NAME="AEN206"
></A
><P
><B
>&#31684;&#20363; 2-4. Example using <A
HREF="control-structures.php"
>control 
     structures</A
> and <A
HREF="functions.php"
>functions</A
></B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="php"
>&#60;?php
if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
	echo "You are using Internet Explorer&#60;br /&#62;";
}
?&#62;</PRE
></TD
></TR
></TABLE
><P
>&#13;      A sample output of this script may be:
      <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="html"
>You are using Internet Explorer&#60;br /&#62;</PRE
></TD
></TR
></TABLE
>
     </P
></DIV
></TD
></TR
></TABLE
>
   </P
><P
>&#13;    Here we introduce a couple of new concepts. We have an 
    <A
HREF="control-structures.php#control-structures.if"
>if</A
> statement.
    If you are familiar with the basic syntax used by the C
    language this should look logical to you. If you don't know enough
    C or some other language where the syntax used above is used, you
    should probably pick up any introductory PHP book and read the first
    couple of chapters, or read the <A
HREF="langref.php"
>Language
    Reference</A
> part of the manual. You can find a list of PHP books
    at <A
HREF="http://www.php.net/books.php"
TARGET="_top"
>http://www.php.net/books.php</A
>.
   </P
><P
>&#13;    The second concept we introduced was the <A
HREF="function.strstr.php"
><B
CLASS="function"
>strstr()</B
></A
>
    function call. <A
HREF="function.strstr.php"
><B
CLASS="function"
>strstr()</B
></A
> is a function built into
    PHP which searches a string for another string. In this case we are
    looking for <TT
CLASS="literal"
>"MSIE"</TT
> inside
    <TT
CLASS="varname"
>$_SERVER["HTTP_USER_AGENT"]</TT
>. If the string is found,
    the function returns <TT
CLASS="constant"
><B
>TRUE</B
></TT
> and if it isn't, it returns <TT
CLASS="constant"
><B
>FALSE</B
></TT
>. If
    it returns <TT
CLASS="constant"
><B
>TRUE</B
></TT
>, the <A
HREF="control-structures.php#control-structures.if"
>if</A
> 
    statement evaluates to <TT
CLASS="constant"
><B
>TRUE</B
></TT
> and the code within its {braces} is 
    executed.  Otherwise, it's not.  Feel free to create similar examples, 
    with <A
HREF="control-structures.php#control-structures.if"
>if</A
>, 
    <A
HREF="control-structures.else.php"
>else</A
>, and other 
    functions such as <A
HREF="function.strtoupper.php"
><B
CLASS="function"
>strtoupper()</B
></A
> and 
    <A
HREF="function.strlen.php"
><B
CLASS="function"
>strlen()</B
></A
>.  Each related manual page contains examples 
    too.
   </P
><P
>&#13;    We can take this a step further and show how you can jump in and out
    of PHP mode even in the middle of a PHP block:
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
NAME="AEN233"
></A
><P
><B
>&#31684;&#20363; 2-5. Mixing both HTML and PHP modes</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="php"
>&#60;?php
if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
?&#62;
&#60;h3&#62;strstr must have returned true&#60;/h3&#62;
&#60;center&#62;&#60;b&#62;You are using Internet Explorer&#60;/b&#62;&#60;/center&#62;
&#60;?php
} else {
?&#62;
&#60;h3&#62;strstr must have returned false&#60;/h3&#62;
&#60;center&#62;&#60;b&#62;You are not using Internet Explorer&#60;/b&#62;&#60;/center&#62;
&#60;?php
}
?&#62;</PRE
></TD
></TR
></TABLE
><P
>&#13;      A sample output of this script may be:
      <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="html"
>&#60;h3&#62;strstr must have returned true&#60;/h3&#62;
&#60;center&#62;&#60;b&#62;You are using Internet Explorer&#60;/b&#62;&#60;/center&#62;</PRE
></TD
></TR
></TABLE
>
     </P
></DIV
></TD
></TR
></TABLE
>
   </P
><P
>&#13;    Instead of using a PHP echo statement to output something, we jumped out of PHP
    mode and just sent straight HTML. The important and powerful point to note here
    is that the logical flow of the script remains intact. Only one of the HTML blocks
    will end up getting sent to the viewer depending on if
    <A
HREF="function.strstr.php"
><B
CLASS="function"
>strstr()</B
></A
> returned <TT
CLASS="constant"
><B
>TRUE</B
></TT
> or <TT
CLASS="constant"
><B
>FALSE</B
></TT
>  In other words, 
    if the string <TT
CLASS="literal"
>MSIE</TT
> was found or not.
   </P
></DIV
><?php manualFooter('Something Useful','tutorial.useful.php');
?>