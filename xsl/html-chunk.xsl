<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  html-common.xsl: Common HTML customizations

  $Id: html-chunk.xsl,v 1.2 2003-04-25 18:41:22 goba Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<!-- Use the IDs to create filenames -->
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
      <xsl:call-template name="chunk-element-content">
        <xsl:with-param name="prev" select="/foo"/>
        <xsl:with-param name="next" select="/foo"/>
        <xsl:with-param name="content">
          <xsl:apply-templates mode="titlepage.mode"/>
        </xsl:with-param>
      </xsl:call-template>
    </xsl:with-param>
  </xsl:call-template>
</xsl:template>

<!-- Add parenthesis FUNCTIONS, and if target exists link
     them to their refentry, otherweise make them bold  -->
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
          <xsl:value-of select="concat($targetid,$html.ext)"/> 
        </xsl:attribute>
        <xsl:copy-of select="$content"/>
      </a>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
