<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  html.xsl: Chunked HTML specific stylesheet

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<xsl:import href="./docbook/html/chunkfast.xsl"/>
<xsl:include href="html-common.xsl"/>


<xsl:param name="base.dir" select="'html/'"/>
<xsl:param name="use.id.as.filename" select="1"/>

<!-- Speed up generation (no file name printouts) -->
<xsl:param name="chunk.quietly">1</xsl:param>


<!-- Make LEGALNOTICE an extra-file, omit extra-link on start-page (link
     directly from the original <COPYRIGHT>), and make nav-header/footer -->
<xsl:template match="copyright" mode="titlepage.mode">
  <p>
    <a href="{concat('copyright',$html.ext)}">
      <xsl:call-template name="gentext">
        <xsl:with-param name="key" select="'Copyright'"/>
      </xsl:call-template>
    </a>
    <xsl:text> </xsl:text>
    <xsl:call-template name="dingbat">
      <xsl:with-param name="dingbat">copyright</xsl:with-param>
    </xsl:call-template>
    <xsl:text> </xsl:text>
    <xsl:call-template name="copyright.years">
      <xsl:with-param name="years" select="year"/>
      <xsl:with-param name="print.ranges" select="$make.year.ranges"/>
      <xsl:with-param name="single.year.ranges"
                      select="$make.single.year.ranges"/>
    </xsl:call-template>
    <xsl:text> </xsl:text>
    <xsl:apply-templates select="holder" mode="titlepage.mode"/>
  </p>
</xsl:template>

<xsl:template match="legalnotice" mode="titlepage.mode">
  <xsl:variable name="filename">
    <xsl:value-of select="concat($base.dir,'copyright',$html.ext)"/>
  </xsl:variable>

  <xsl:call-template name="write.chunk">
    <xsl:with-param name="filename" select="$filename"/>
    <xsl:with-param name="quiet" select="$chunk.quietly"/>
    <xsl:with-param name="content">
      <html>
        <head>
          <xsl:call-template name="system.head.content"/>
          <title>
            <xsl:apply-templates select="." mode="object.title.markup.textonly"/>
          </title>
        </head>
        <body>
          <xsl:call-template name="body.attributes"/>
            <table width="100%" summary="Navigation header">
              <tr>
                <th align="center"><xsl:value-of select="/book/title"/></th>
              </tr>
            </table>
          <hr/>
          <xsl:apply-templates mode="titlepage.mode"/>
          <hr/>
          <table width="100%" summary="Navigation footer">
          <tr>
            <td align="center">
              <a accesskey="h">
                <xsl:attribute name="href">
                  <xsl:call-template name="href.target">
                    <xsl:with-param name="object" select="/*[1]"/>
                  </xsl:call-template>
                </xsl:attribute>
                <xsl:call-template name="navig.content">
                  <xsl:with-param name="direction" select="'home'"/>
                </xsl:call-template>
              </a>
            </td>
          </tr>
          </table>
        </body>
      </html>
    </xsl:with-param>
  </xsl:call-template>
</xsl:template>



<!-- NAVIGATION-HEADERS: let them look similar to dsssl
     (not in 1st page, 1st line always "PHP manual", titles
     labelled just with e.g. 'A.' instead of 'appendix A.') -->
<xsl:template name="header.navigation">
  <xsl:param name="prev" select="/foo"/>
  <xsl:param name="next" select="/foo"/>
  <xsl:param name="nav.context"/>

  <xsl:variable name="home" select="/*[1]"/>
  <xsl:variable name="up" select="parent::*"/>

  <xsl:variable name="row2" select="(count($prev) &gt; 0
                                    or (count($up) &gt; 0 and $up != $home)
                                    or count($next) &gt; 0)
                                    and local-name(.) != 'book'"/>

  <div class="navheader">
    <xsl:if test="$row2">
      <table width="100%" summary="Navigation header">
        <tr>
          <th colspan="3" align="center">
            <xsl:value-of select="/book/title"/>
          </th>
        </tr>
        <tr>
          <td width="20%" align="left">
            <xsl:if test="count($prev)>0">
              <a>
                <xsl:attribute name="href">
                  <xsl:call-template name="href.target">
                    <xsl:with-param name="object" select="$prev"/>
                  </xsl:call-template>
                </xsl:attribute>
                <xsl:call-template name="navig.content">
                  <xsl:with-param name="direction" select="'prev'"/>
                </xsl:call-template>
              </a>
            </xsl:if>
            <xsl:text>&#160;</xsl:text>
          </td>
          <td width="60%" align="center">
            <xsl:choose>
              <xsl:when test="count($up) > 0 and $up != $home">
                <xsl:variable name="label">
                  <xsl:apply-templates select="$up" mode="label.markup"/>
                </xsl:variable>
                <xsl:copy-of select="$label"/>
                <xsl:if test="$label != ''">
                  <xsl:text>. </xsl:text>
                </xsl:if>
                <xsl:apply-templates select="$up" mode="title.markup"/>
              </xsl:when>
              <xsl:otherwise>&#160;</xsl:otherwise>
            </xsl:choose>
          </td>
          <td width="20%" align="right">
            <xsl:text>&#160;</xsl:text>
            <xsl:if test="count($next)>0">
              <a>
                <xsl:attribute name="href">
                  <xsl:call-template name="href.target">
                    <xsl:with-param name="object" select="$next"/>
                  </xsl:call-template>
                </xsl:attribute>
                <xsl:call-template name="navig.content">
                  <xsl:with-param name="direction" select="'next'"/>
                </xsl:call-template>
              </a>
            </xsl:if>
          </td>
        </tr>
      </table>
      <hr/>
    </xsl:if>
  </div>
</xsl:template>


<!-- NAVIGATION-FOOTERS: label just with e.g. 'A.' instead of 'appendix A.') -->
<xsl:template name="footer.navigation">
  <xsl:param name="prev" select="/foo"/>
  <xsl:param name="next" select="/foo"/>
  <xsl:param name="nav.context"/>

  <xsl:variable name="home" select="/*[1]"/>
  <xsl:variable name="up" select="parent::*"/>

  <xsl:variable name="row1" select="count($prev) &gt; 0
                                    or count($up) &gt; 0
                                    or count($next) &gt; 0"/>

  <xsl:variable name="row2" select="$prev != 0 or $next != 0
                                    or ($home != . or $nav.context = 'toc')"/>

  <div class="navfooter">
    <hr/>
    <xsl:if test="$row1 or $row2">
      <table width="100%" summary="Navigation footer">
        <xsl:if test="$row1">
          <tr>
            <td width="40%" align="left">
              <xsl:if test="count($prev)>0">
                <a accesskey="p">
                  <xsl:attribute name="href">
                    <xsl:call-template name="href.target">
                      <xsl:with-param name="object" select="$prev"/>
                    </xsl:call-template>
                  </xsl:attribute>
                  <xsl:call-template name="navig.content">
                    <xsl:with-param name="direction" select="'prev'"/>
                  </xsl:call-template>
                </a>
              </xsl:if>
              <xsl:text>&#160;</xsl:text>
            </td>
            <td width="20%" align="center">
              <xsl:choose>
                <xsl:when test="count($up)>0">
                  <a accesskey="u">
                    <xsl:attribute name="href">
                      <xsl:call-template name="href.target">
                        <xsl:with-param name="object" select="$up"/>
                      </xsl:call-template>
                    </xsl:attribute>
                    <xsl:call-template name="navig.content">
                      <xsl:with-param name="direction" select="'up'"/>
                    </xsl:call-template>
                  </a>
                </xsl:when>
                <xsl:otherwise>&#160;</xsl:otherwise>
              </xsl:choose>
            </td>
            <td width="40%" align="right">
              <xsl:text>&#160;</xsl:text>
              <xsl:if test="count($next)>0">
                <a accesskey="n">
                  <xsl:attribute name="href">
                    <xsl:call-template name="href.target">
                      <xsl:with-param name="object" select="$next"/>
                    </xsl:call-template>
                  </xsl:attribute>
                  <xsl:call-template name="navig.content">
                    <xsl:with-param name="direction" select="'next'"/>
                  </xsl:call-template>
                </a>
              </xsl:if>
            </td>
          </tr>
        </xsl:if>

        <xsl:if test="$row2">
          <tr>
            <td width="40%" align="left" valign="top">
              <xsl:variable name="label">
                <xsl:apply-templates select="$prev" mode="label.markup"/>
              </xsl:variable>
              <xsl:copy-of select="$label"/>
              <xsl:if test="$label != ''">
                <xsl:text>. </xsl:text>
              </xsl:if>
              <xsl:apply-templates select="$prev" mode="title.markup"/>
              <xsl:text>&#160;</xsl:text>
            </td>
            <td width="20%" align="center">
              <xsl:choose>
                <xsl:when test="$home != . or $nav.context = 'toc'">
                  <a accesskey="h">
                    <xsl:attribute name="href">
                      <xsl:call-template name="href.target">
                        <xsl:with-param name="object" select="$home"/>
                      </xsl:call-template>
                    </xsl:attribute>
                    <xsl:call-template name="navig.content">
                      <xsl:with-param name="direction" select="'home'"/>
                    </xsl:call-template>
                  </a>
                </xsl:when>
                <xsl:otherwise>&#160;</xsl:otherwise>
              </xsl:choose>
            </td>
            <td width="40%" align="right" valign="top">
              <xsl:text>&#160;</xsl:text>
              <xsl:variable name="label">
                <xsl:apply-templates select="$next" mode="label.markup"/>
              </xsl:variable>
              <xsl:copy-of select="$label"/>
              <xsl:if test="$label != ''">
                <xsl:text>. </xsl:text>
              </xsl:if>
              <xsl:apply-templates select="$next" mode="title.markup"/>
            </td>
          </tr>
        </xsl:if>
      </table>
    </xsl:if>
  </div>
</xsl:template>


<!-- Add parenthesis FUNCTIONS, and if target exists link
     them to their refentry, otherweise make it bold  -->
<xsl:template match="function">
  <xsl:variable name="content">
    <xsl:apply-templates/><xsl:text>()</xsl:text>
  </xsl:variable>
  <xsl:variable name="targetid">
    <xsl:value-of select="concat('function.', translate(string(current()),'_','-'))"/>
  </xsl:variable>

  <xsl:choose>
    <xsl:when test="ancestor::refentry/refnamediv/refname=translate(current(),
                      'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')
                    or count(/*/part[@id='funcref']/*/refentry[@id=$targetid]) = 0">
      <b><xsl:copy-of select="$content"/></b>
    </xsl:when>
    <xsl:otherwise>
      <a>
        <xsl:attribute name="href">
          <xsl:value-of select="concat($targetid, '.html')"/> 
        </xsl:attribute>
        <xsl:copy-of select="$content"/>
      </a>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>


<!-- Suppress Links in html-header -->
<xsl:template name="html.head">
  <xsl:param name="prev"/>
  <xsl:param name="next"/>
  <head>
    <title>
      <xsl:apply-templates select="." mode="object.title.markup.textonly"/>
    </title>
  </head>
</xsl:template>



<!-- Don't let the "next"-links of navigation-headers(footers)
     point to a section below reference/partintro, which is no chunk -->
<xsl:template name="chunk-first-section-with-parent">
  <xsl:param name="content">
    <xsl:apply-imports/>
  </xsl:param>

  <xsl:variable name="prev-v1"
     select="(ancestor::sect1[$chunk.section.depth &gt; 0
                               and preceding-sibling::sect1][1]

             |ancestor::sect2[$chunk.section.depth &gt; 1
                               and preceding-sibling::sect2
                               and parent::sect1[preceding-sibling::sect1]][1]

             |ancestor::section[$chunk.section.depth &gt; count(ancestor::section)
                                and not(ancestor::section[not(preceding-sibling::section)])][1])[last()]"/>

  <xsl:variable name="prev-v2"
     select="(preceding::sect1[$chunk.section.depth &gt; 0
                               and preceding-sibling::sect1][1]

             |preceding::sect2[$chunk.section.depth &gt; 1
                               and preceding-sibling::sect2
                               and parent::sect1[preceding-sibling::sect1]][1]

             |preceding::section[$chunk.section.depth &gt; count(ancestor::section)
                                 and preceding-sibling::section
                                 and not(ancestor::section[not(preceding-sibling::section)])][1])[last()]"/>

  <xsl:variable name="prev"
    select="(preceding::book[1]
             |preceding::preface[1]
             |preceding::chapter[1]
             |preceding::appendix[1]
             |preceding::part[1]
             |preceding::reference[1]
             |preceding::refentry[1]
             |preceding::colophon[1]
             |preceding::article[1]
             |preceding::bibliography[1]
             |preceding::glossary[1]
             |preceding::index[1]
             |preceding::setindex[1]
             |ancestor::set
             |ancestor::book[1]
             |ancestor::preface[1]
             |ancestor::chapter[1]
             |ancestor::appendix[1]
             |ancestor::part[1]
             |ancestor::reference[1]
             |ancestor::article[1]
             |$prev-v1
             |$prev-v2)[last()]"/>

  <xsl:variable name="next-v1"
    select="(following::sect1[$chunk.section.depth &gt; 0
                               and preceding-sibling::sect1][1]

             |following::sect2[$chunk.section.depth &gt; 1
                               and preceding-sibling::sect2
                               and parent::sect1[preceding-sibling::sect1]][1]

             |following::section[$chunk.section.depth &gt; count(ancestor::section)
                                 and preceding-sibling::section
                                 and not(ancestor::section[not(preceding-sibling::section)])
                                 and not(parent::partintro)][1])[1]"/>

  <xsl:variable name="next-v2"
    select="(descendant::sect1[$chunk.section.depth &gt; 0
                               and preceding-sibling::sect1][1]

             |descendant::sect2[$chunk.section.depth &gt; 1
                               and preceding-sibling::sect2
                               and parent::sect1[preceding-sibling::sect1]][1]

             |descendant::section[$chunk.section.depth &gt; count(ancestor::section)
                                 and preceding-sibling::section
                                 and not(ancestor::section[not(preceding-sibling::section)])
                                 and not(parent::partintro)])[1]"/>

  <xsl:variable name="next"
    select="(following::book[1]
             |following::preface[1]
             |following::chapter[1]
             |following::appendix[1]
             |following::part[1]
             |following::reference[1]
             |following::refentry[1]
             |following::colophon[1]
             |following::bibliography[1]
             |following::glossary[1]
             |following::index[1]
             |following::article[1]
             |following::setindex[1]
             |descendant::book[1]
             |descendant::preface[1]
             |descendant::chapter[1]
             |descendant::appendix[1]
             |descendant::article[1]
             |descendant::bibliography[1]
             |descendant::glossary[1]
             |descendant::index[1]
             |descendant::colophon[1]
             |descendant::setindex[1]
             |descendant::part[1]
             |descendant::reference[1]
             |descendant::refentry[1]
             |$next-v1
             |$next-v2)[1]"/>

  <xsl:call-template name="process-chunk">
    <xsl:with-param name="prev" select="$prev"/>
    <xsl:with-param name="next" select="$next"/>
    <xsl:with-param name="content" select="$content"/>
  </xsl:call-template>
</xsl:template>



</xsl:stylesheet>
