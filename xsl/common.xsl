<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  common.xsl: Common customizations for all formats

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">


<!-- Set the LANGUAGE for autom. generated text -->
<xsl:param name="l10n.gentext.language" select="/book/@lang"/>

<!-- temporary till also this is in std.-distrib. -->
<xsl:template match="titleabbrev"></xsl:template>

<!-- Start NUMBERING (e.g. chapters) in every part -->
<xsl:param name="label.from.part" select="'0'"/>

<!-- Colorize background for programlisting and screens -->
<xsl:param name="shade.verbatim" select="1"/>

<!-- REFENTRIES: make functionnames the title -->
<xsl:param name="refentry.generate.name" select="'0'"/>
<xsl:param name="refentry.generate.title" select="1"/>

<!-- Load VERSION INFORMATION into variable -->
<xsl:param name="version" select="document('version.xml')/versions"/>


<!-- PROTOTYPES: PHP-Version without semicolon, etc. 
     note: methodparams are separated in html-common and fo -->
<xsl:template match="methodsynopsis">
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="methodsynopsis/type">
  <xsl:apply-templates />
  <xsl:text> </xsl:text>
</xsl:template>

<xsl:template match="methodsynopsis/void">
  <xsl:text> (void)</xsl:text>
</xsl:template>

<xsl:template match="methodparam/type">
  <xsl:apply-templates />
  <xsl:text> </xsl:text>
</xsl:template>

<xsl:template match="methodparam/parameter">
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="methodparam">
  <xsl:if test="preceding-sibling::methodparam=false()">
    <xsl:text> (</xsl:text>
    <xsl:if test="@choice='opt'">
      <xsl:text>[</xsl:text>
    </xsl:if>
  </xsl:if>
  <xsl:apply-templates />
  <xsl:choose>
    <xsl:when test="following-sibling::methodparam">
      <xsl:choose>
        <xsl:when test="following-sibling::methodparam[position()=1]/attribute::choice[.='opt']">
          <xsl:text> [, </xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>, </xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:for-each select="preceding-sibling::methodparam/attribute::choice[.='opt']">
        <xsl:text>]</xsl:text>
      </xsl:for-each>
      <xsl:if test="self::methodparam/attribute::choice[.='opt']">
        <xsl:text>]</xsl:text>
      </xsl:if>
      <xsl:text>)</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>


<!-- for the list of TRANSLATORS -->
<xsl:template match="collab" mode="titlepage.mode">
  <xsl:choose>
    <xsl:when test="position()=last()">
      <xsl:apply-templates/>
    </xsl:when>
    <xsl:when test="position() &gt; 1">
      <xsl:apply-templates/><xsl:text>, </xsl:text>
    </xsl:when>
    <xsl:otherwise></xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="collabname">
  <xsl:apply-templates/>
</xsl:template>


</xsl:stylesheet>
