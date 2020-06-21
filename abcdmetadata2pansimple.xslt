<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:oai="http://www.openarchives.org/OAI/2.0/" xmlns:abcd="http://www.tdwg.org/schemas/abcd/2.06"  xmlns:so="https://ws.gfbio.org/so/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php" extension-element-prefixes="php">
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<oai:record>
			<oai:header>
				<oai:identifier><xsl:text>urn:gfbio.org:abcd:set:</xsl:text>
					<xsl:value-of select="/abcd:DataSets/abcd:DataSet/so:BMS_ArchiveFolder"/>
				</oai:identifier>
				<oai:datestamp>
					<xsl:value-of select="/abcd:DataSets/abcd:DataSet/so:ABCDHarvesttime"/>
				</oai:datestamp>
			</oai:header>
			<oai:metadata>
				<dataset xmlns="urn:pangaea.de:dataportals" xmlns:dc="http://purl.org/dc/elements/1.1/">
					<dc:title>
					<xsl:text>Data set: </xsl:text>
					<xsl:value-of select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Description/abcd:Representation/abcd:Title"/>
					<xsl:if test="/abcd:DataSets/abcd:DataSet/abcd:ContentContacts/abcd:ContentContact/abcd:Name">
						<xsl:text>. Contact: </xsl:text>
						<xsl:value-of select="/abcd:DataSets/abcd:DataSet/abcd:ContentContacts/abcd:ContentContact/abcd:Name"/>
					</xsl:if>
					</dc:title>
					<xsl:if test="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Description/abcd:Representation/abcd:Details">
						<dc:description>
							<xsl:value-of select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Description/abcd:Representation/abcd:Details"/>
							<xsl:text>, zipped ABCD Archive</xsl:text>
						</dc:description>
					</xsl:if>
					<xsl:for-each select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Owners">
					<xsl:if test="abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text">
						<dc:contributor>
							<xsl:value-of select="abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text"/>
						</dc:contributor>
						</xsl:if>
					</xsl:for-each>
					<xsl:for-each select="/abcd:DataSets/abcd:DataSet/abcd:ContentContacts">
					<xsl:if test="abcd:ContentContacts/abcd:ContentContact/abcd:Name">
						<dc:contributor>
							<xsl:value-of select="abcd:ContentContacts/abcd:ContentContact/abcd:Name"/>
						</dc:contributor>
						</xsl:if>
					</xsl:for-each>
					<dc:publisher>
						<xsl:value-of select="/abcd:DataSets/abcd:DataSet/so:BMS_Publisher"/>
					</dc:publisher>
					<dataCenter>
						<xsl:value-of select="/abcd:DataSets/abcd:DataSet/so:BMS_Datacenter"/>
					</dataCenter>
					<dc:type>ABCD_Dataset</dc:type>
					<dc:format>text/html</dc:format>
					<xsl:if test="abcd:RecordURI">
						<linkage type="metadata">
							<xsl:value-of select="abcd:RecordURI"/>
						</linkage>
					</xsl:if>
					<linkage type="data">
						<xsl:value-of select="/abcd:DataSets/abcd:DataSet/so:BMS_ArchiveUrl"/>
					</linkage>
					<dc:identifier>
						<xsl:choose>
							<xsl:when test="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Description/abcd:Representation/abcd:URI">
								<xsl:value-of select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Description/abcd:Representation/abcd:URI"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="/abcd:DataSets/abcd:DataSet/so:BMS_ArchiveFolder"/>
							</xsl:otherwise>
						</xsl:choose>
					</dc:identifier>
					<xsl:if test="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:Citations/abcd:Citation/abcd:Text">
			        <dc:source>
						<xsl:value-of select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:Citations/abcd:Citation/abcd:Text"/>
					</dc:source>
					</xsl:if>
					<xsl:if test="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:Text">
					<xsl:if test="not(/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:Text = /abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:Licenses/abcd:License/abcd:Text)">
						<dc:rights>
							<xsl:value-of select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:Text"/>
						</dc:rights>
						</xsl:if>
					</xsl:if>
					<xsl:if test="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:Licenses/abcd:License/abcd:Text">
						<dc:rights>
							License: <xsl:value-of select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:Licenses/abcd:License/abcd:Text"/>
						</dc:rights>
					</xsl:if>
					<dc:relation>
						<xsl:value-of select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Representation/abcd:URI"/>
					</dc:relation>
				</dataset>
			</oai:metadata>
		</oai:record>
	</xsl:template>
</xsl:stylesheet>
