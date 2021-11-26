# PHP Manual Contribution Guide

## Installing PhD

### Requirements:

- PHP 7.1
- DOM, libXML2, XMLReader and SQLite3.

Clone the [phd](https://github.com/php/phd.git) repository:

```bash
git clone https://github.com/php/phd.git
```

## Setting up a documentation environment

Clone the [doc-base](https://github.com/php/doc-base) repository one level with [doc-en](https://github.com/php/doc-en)
so that the folder structure is as follows:

```
├── doc-base/
└── en/
└── phd/
```

Note that language directory must be without the `doc-` prefix.

For cloning, you can use the command `git clone https://github.com/php/doc-en.git en`.

## Build the documentation

Run the following commands in the terminal to build the documentation in HTML format:

```bash
php doc-base/configure.php
php phd/render.php --docbook doc-base/.manual.xml --package PHP --format xhtml
```

Then you can preview the build in ``output/php-chunked-xhtml/`` directory with your browser. 