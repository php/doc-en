<?xml version="1.0" encoding="utf-8"?>
{EMPTY_REVISION_KEYWORD}

<phpdoc:classref xml:id="class.{CLASS_NAME_ID}" xmlns:phpdoc="http://php.net/ns/phpdoc" xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xi="http://www.w3.org/2001/XInclude">

 <title>The {CLASS_NAME} class</title>
 <titleabbrev>{CLASS_NAME}</titleabbrev>

 <partintro>

<!-- {{{ {CLASS_NAME} intro -->
  <section xml:id="{CLASS_NAME_ID}.intro">
   &reftitle.intro;
   <para>

   </para>
  </section>
<!-- }}} -->

  <section xml:id="{CLASS_NAME_ID}.synopsis">
   &reftitle.classsynopsis;

<!-- {{{ Synopsis -->
   <classsynopsis>
    <ooclass><classname>{CLASS_NAME}</classname></ooclass>

<!-- {{{ Class synopsis -->
    <classsynopsisinfo>
     <ooclass>
      <classname>{CLASS_NAME}</classname>
     </ooclass>
     {EXTENDS}
     {IMPLEMENTS}
    </classsynopsisinfo>
<!-- }}} -->
    {CONSTANTS_LIST}
    {PROPERTIES_LIST}
    {METHOD_XINCLUDE}
    {INHERITED_XINCLUDE}
   </classsynopsis>
<!-- }}} -->

  </section>

  {PROPERTIES}
  {CONSTANTS}

 </partintro>

 &reference.{EXT_NAME_ID}.entities.{CLASS_NAME_ID};

</phpdoc:classref>

<!-- Keep this comment at the end of the file
Local variables:
mode: sgml
sgml-omittag:t
sgml-shorttag:t
sgml-minimize-attributes:nil
sgml-always-quote-attributes:t
sgml-indent-step:1
sgml-indent-data:t
indent-tabs-mode:nil
sgml-parent-document:nil
sgml-default-dtd-file:"manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=sgml
vi: ts=1 sw=1
-->
