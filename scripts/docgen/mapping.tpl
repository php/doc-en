<?xml version="1.0" encoding="utf-8"?>
{EMPTY_REVISION_KEYWORD}

<refentry xml:id="{CLASS_NAME_ID}.{METHOD_NAME_ID}" xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink">
 <refnamediv>
  <refname>{FULL_METHOD_NAME}</refname>
  <refname>{FUNCTION_NAME}</refname>
  <refpurpose>The {METHOD_NAME} purpose</refpurpose>
 </refnamediv>

 <refsect1 role="description">
  &reftitle.description;
  <para>Object oriented style (method):</para>
   <methodsynopsis>
   {MODIFIERS}{RETURN_TYPE}<methodname>{FULL_METHOD_NAME}</methodname>
   {METHOD_PARAMETERS}
   </methodsynopsis>
  <para>Procedural style:</para>
  <methodsynopsis>
   <type>ReturnType</type><methodname>{FUNCTION_NAME}</methodname>
   {FUNCTION_PARAMETERS}
  </methodsynopsis>
  <para>
   Description here.
  </para>
  
  &warn.undocumented.func;
 
 </refsect1>

 <refsect1 role="parameters">
  &reftitle.parameters;
  {PARAMETERS_DESCRIPTION}
 </refsect1>

 <refsect1 role="returnvalues">
  &reftitle.returnvalues;
  <para>
   Description...
  </para>
 </refsect1>

 <refsect1 role="examples">
  &reftitle.examples;
  <para>
   <example>
    <title>Object oriented style</title>
    <programlisting role="php">
<![CDATA[
<?php
/* ... */
?>
]]>
     </programlisting>
     &example.outputs.similar;
     <screen>
<![CDATA[
...
]]>
    </screen>
   </example>
  </para>
  <para>
   <example>
    <title>Procedural style</title>
    <programlisting role="php">
<![CDATA[
<?php
/* ... */
?>
]]>
     </programlisting>
     &example.outputs.similar;
     <screen>
<![CDATA[
...
]]>
    </screen>
   </example>
  </para>
 </refsect1>

 <refsect1 role="seealso">
  &reftitle.seealso;
  <para>
   <simplelist>
    <member><methodname>Classname::Method</methodname></member>
   </simplelist>
  </para>
 </refsect1>

</refentry>

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
sgml-default-dtd-file:"../../../../manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=sgml
vi: ts=1 sw=1
-->
