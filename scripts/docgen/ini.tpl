<?xml version="1.0" encoding="utf-8"?>
{EMPTY_REVISION_KEYWORD}

<section xml:id="{EXT_NAME_ID}.configuration" xmlns="http://docbook.org/ns/docbook">
 &reftitle.runtime;
 &extension.runtime;
 <para>
  <table>
   <title>{EXT_NAME} &ConfigureOptions;</title>
   <tgroup cols="4">
    <thead>
     <row>
      <entry>&Name;</entry>
      <entry>&Default;</entry>
      <entry>&Changeable;</entry>
      <entry>&Changelog;</entry>
     </row>
    </thead>
    {INI_ENTRIES}
   </tgroup>
  </table>
 </para>

 &ini.descriptions.title;

 <para>
  <variablelist>
   {INI_ENTRIES_DESCRIPTION}
  </variablelist>
 </para>
</section>

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
sgml-default-dtd-file:"~/.phpdoc/manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=sgml
vi: ts=1 sw=1
-->
