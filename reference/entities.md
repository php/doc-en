# Per extension entities

For textual entities, related to specific extensions, new or migrated
from `language-snippets.ent`, please use the following conventions.

* Use an `ext.` prefix for all entity names;
* Use model file below, for new entities files;
* Place the entities in file named `doc-en/reference/$extension/entities.ent`;
* Keep the entities sorted by name, one empty line separating each entity.

If the entity is "big" or otherwise difficult to edit in a file
with many others, it is possible to create a individual entity per file,
saved as `doc-en/entities/name.$extension.$entname.xml`, whereas the
content can be any valid [well-balanced
region](https://www.w3.org/TR/xml-fragment/#defn-well-balanced).
DTD entity names are also valid here, but XML declarations are not.

XML namespaces are to be placed in the root `<entities>` element, and
avoided in any `<entity>` child.

To rename existing entities to use the `ext.` prefix, is possible to
create temporary aliases, with the following format:
```xml
<entity name="old.name">&new.name;</entity>
```

Temporary aliases are better placed in
`doc-en/entities/entities-remove.ent`, to avoid duplicated work in
translations, but are acceptable here too.

### Example file

```xml
<?xml version="1.0" encoding="utf-8"?>
<!-- $Revision$ -->
<!--
    See `manual.xml` for XML namespaces defaults.
    Keep the lines under 79 columns, to make the process of manual
    translation easier, and to avoid wrap-around in some other contexts.
-->
<entities xmlns       = "http://docbook.org/ns/docbook"
          xmlns:xlink = "http://www.w3.org/1999/xlink"
          translate   = "yes">

<entity name="ext.$extention.$entity-name">
 <simpara>
  Text.
 </simpara>
</entity>

</entities>
```