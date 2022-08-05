<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
  <xsl:for-each select="//detail">
    <xsl:value-of select="descriptif_bref"/>
  	<xsl:value-of select="descriptif_court"/>
    <xsl:value-of select="descriptif_bref_en"/>
    <xsl:value-of select="descriptif_court_en"/>
    <xsl:value-of select="type_bien"/>
    <xsl:value-of select="nombre_chambres"/>
    <xsl:value-of select="latitude"/>
    <xsl:value-of select="longitude"/>
    <xsl:value-of select="nb_adultes"/>
  </xsl:for-each>	
</xsl:template>
</xsl:stylesheet>