<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP дтеU'),
  'prev' => array('tutorial.useful.php', 'Something Useful'),
  'next' => array('tutorial.oldcode.php', 'Using old code with new versions of PHP'),
  'up'   => array('tutorial.php', 'A simple tutorial'),
  'toc'  => array(
       array('tutorial.php#tutorial.requirements', 'What do I need?'),
     array('tutorial.firstpage.php', 'Your first PHP-enabled page'),
     array('tutorial.useful.php', 'Something Useful'),
     array('tutorial.forms.php', 'Dealing with Forms'),
     array('tutorial.oldcode.php', 'Using old code with new versions of PHP'),
     array('tutorial.whatsnext.php', 'What\'s next?'),
)));
manualHeader('Dealing with Forms','tutorial.forms.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="tutorial.forms"
></A
>Dealing with Forms</H1
><P
>&#13;    One of the most powerful features of PHP is the way it handles HTML
    forms. The basic concept that is important to understand is that any
    form element in a form will automatically be available to your PHP 
    scripts.  Please read the manual section on
    <A
HREF="language.variables.external.php"
>Variables from outside 
    of PHP</A
> for more information and examples on using forms 
    with PHP.  Here's an example HTML form:
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
NAME="AEN248"
></A
><P
><B
>&#31684;&#20363; 2-6. A simple HTML form</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="html"
>&#60;form action="action.php" method="POST"&#62;
 Your name: &#60;input type="text" name="name" /&#62;
 Your age: &#60;input type="text" name="age" /&#62;
 &#60;input type="submit"&#62;
&#60;/form&#62;</PRE
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
>&#13;    There is nothing special about this form. It is a straight HTML form
    with no special tags of any kind. When the user fills in this form
    and hits the submit button, the <TT
CLASS="filename"
>action.php</TT
> page
    is called. In this file you would have something like this:
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
NAME="AEN254"
></A
><P
><B
>&#31684;&#20363; 2-7. Printing data from our form</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="php"
>Hi &#60;?php echo $_POST["name"]; ?&#62;.
You are &#60;?php echo $_POST["age"]; ?&#62; years old.</PRE
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
>Hi Joe.
You are 22 years old.</PRE
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
>&#13;    It should be obvious what this does. There is nothing more to it.
    The <TT
CLASS="varname"
>$_POST["name"]</TT
> and <TT
CLASS="varname"
>$_POST["age"]</TT
>
    variables are automatically set for you by PHP.  Earlier we
    used the <TT
CLASS="varname"
>$_SERVER</TT
> autoglobal, now above we just 
    introduced the <A
HREF="reserved.variables.php#reserved.variables.post"
>$_POST</A
>
    autoglobal which contains all POST data.  Notice how the
    <SPAN
CLASS="emphasis"
><I
CLASS="emphasis"
>method</I
></SPAN
> of our form is POST.  If we used the 
    method <SPAN
CLASS="emphasis"
><I
CLASS="emphasis"
>GET</I
></SPAN
> then our form information would live in 
    the <A
HREF="reserved.variables.php#reserved.variables.get"
>$_GET</A
> autoglobal instead.
    You may also use the <A
HREF="reserved.variables.php#reserved.variables.request"
>$_REQUEST</A
>
    autoglobal if you don't care the source of your request data. It 
    contains a mix of GET, POST, COOKIE and FILE data.  See also the 
    <A
HREF="function.import-request-variables.php"
><B
CLASS="function"
>import_request_variables()</B
></A
> function.
   </P
></DIV
><?php manualFooter('Dealing with Forms','tutorial.forms.php');
?>