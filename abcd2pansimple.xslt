<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:oai="http://www.openarchives.org/OAI/2.0/" xmlns:so="https://ws.gfbio.org/so/" xmlns:abcd="http://www.tdwg.org/schemas/abcd/2.06" xmlns:efg="http://www.synthesys.info/ABCDEFG/1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php" extension-element-prefixes="php">
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:variable name="archive_folder" select="/abcd:DataSets/abcd:DataSet/so:BMS_ArchiveFolder"></xsl:variable>
	<xsl:variable name="harvest_time" select="/abcd:DataSets/abcd:DataSet/so:ABCDHarvesttime"></xsl:variable>
	<xsl:variable name="dataset_title" select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Description/abcd:Representation/abcd:Title"></xsl:variable>
	<xsl:variable name="dataset_details" select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Description/abcd:Representation/abcd:Details"></xsl:variable>
	<xsl:variable name="dataset_owners" select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Owners"></xsl:variable>
	<xsl:variable name="ipr_statement" select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:Citations/abcd:Citation/abcd:Text"></xsl:variable>
	<xsl:variable name="dataset_uri" select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:Description/abcd:Representation/abcd:URI"></xsl:variable>
	<xsl:variable name="terms_of_use_text" select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:Text"></xsl:variable>
	<xsl:variable name="licence_text" select="/abcd:DataSets/abcd:DataSet/abcd:Metadata/abcd:IPRStatements/abcd:Licenses/abcd:License/abcd:Text"></xsl:variable>
	<xsl:variable name="content_contacts" select="/abcd:DataSets/abcd:DataSet/abcd:ContentContacts"></xsl:variable>
	<xsl:variable name="technical_contact_name" select="/abcd:DataSets/abcd:DataSet/abcd:TechnicalContacts/abcd:TechnicalContact/abcd:Name"></xsl:variable>
	<xsl:variable name="bms_publisher" select="/abcd:DataSets/abcd:DataSet/so:BMS_Publisher"></xsl:variable>
	<xsl:variable name="bms_datacenter" select="/abcd:DataSets/abcd:DataSet/so:BMS_Datacenter"></xsl:variable>
	<xsl:variable name="bms_datacenter_short" select="/abcd:DataSets/abcd:DataSet/so:BMS_Datacenter_short"></xsl:variable>
	<xsl:variable name="bms_pywrapper" select="/abcd:DataSets/abcd:DataSet/so:BMS_Pywrapper"></xsl:variable>
	<xsl:variable name="bms_dsa" select="/abcd:DataSets/abcd:DataSet/so:BMS_dsa"></xsl:variable>
	<xsl:variable name="bms_archive_folder" select="/abcd:DataSets/abcd:DataSet/so:BMS_ArchiveFolder"></xsl:variable>
	
	<xsl:template match="/">	
		<xsl:for-each select="abcd:DataSets/abcd:DataSet/abcd:Units/abcd:Unit">
			<oai:record>
				<oai:header>
					<oai:identifier><xsl:text>urn:gfbio.org:abcd:</xsl:text><xsl:value-of select="$archive_folder"/><xsl:text>:</xsl:text><xsl:value-of select="translate(translate(normalize-space(abcd:UnitID),' ',''),'/','-')"/>
					</oai:identifier>
						<oai:datestamp>
							<xsl:value-of select="$harvest_time"/>
							<!--<xsl:value-of select="substring(../../abcd:Metadata/abcd:RevisionData/abcd:DateModified,0,11)"/>-->
						</oai:datestamp>
				</oai:header>
				<oai:metadata>
					<dataset xmlns="urn:pangaea.de:dataportals" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:pangaea.de:dataportals http://ws.pangaea.de/schemas/pansimple/pansimple.xsd">
						<dc:title>
							<xsl:choose>
								<xsl:when test="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString">
									<xsl:value-of select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString"/><xsl:text>, a </xsl:text><xsl:value-of select="php:function('oai::CamelcaseToWords', string(abcd:RecordBasis))"/><xsl:text> record of the "</xsl:text><xsl:value-of select="$dataset_title"/>" dataset
								</xsl:when>
								<!--last-->
								<xsl:when test="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:HigherTaxa/abcd:HigherTaxon[last()]/abcd:HigherTaxonName">
									<xsl:value-of select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:HigherTaxa/abcd:HigherTaxon[last()]/abcd:HigherTaxonName"/><xsl:text> (</xsl:text><xsl:value-of select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:HigherTaxa/abcd:HigherTaxon[1]/abcd:HigherTaxonRank"/><xsl:text>), unspecified, a </xsl:text><xsl:value-of select="php:function('oai::CamelcaseToWords', string(abcd:RecordBasis))"/><xsl:text> record of the "</xsl:text><xsl:value-of select="$dataset_title"/>" dataset
								</xsl:when>
								<xsl:otherwise>
										<xsl:text>Undetermined </xsl:text><xsl:value-of select="php:function('oai::CamelcaseToWords', string(abcd:RecordBasis))"/>
								</xsl:otherwise>
							</xsl:choose><xsl:text> [ID: </xsl:text><xsl:value-of select="abcd:UnitID"/><xsl:text>]</xsl:text>
							</dc:title>
						<xsl:if test="$dataset_details">
							<dc:description>
								<xsl:value-of select="$dataset_details"/>
							</dc:description>
						</xsl:if>
						<xsl:for-each select="$dataset_owners">
							<xsl:if test="abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text">
								<dc:contributor>
									<xsl:value-of select="abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text"/>
								</dc:contributor>
							</xsl:if>
							<xsl:if test="abcd:Owner/abcd:Owner/abcd:Person/abcd:FullName">
								<dc:contributor>
									<xsl:value-of select="abcd:Owner/abcd:Owner/abcd:Person/abcd:FullName"/>
								</dc:contributor>
							</xsl:if>
						</xsl:for-each>
						<xsl:for-each select="abcd:MultiMediaObjects">
							<xsl:if test="abcd:MultiMediaObject/abcd:Creator">
								<dc:contributor>
									<xsl:value-of select="abcd:MultiMediaObject/abcd:Creator"/>
								</dc:contributor>
							</xsl:if>
						</xsl:for-each>
						<dc:contributor>
							<xsl:value-of select="$bms_datacenter_short"/>
						</dc:contributor>
						<xsl:for-each select="$content_contacts">
						<xsl:if test="abcd:ContentContact/abcd:Name">
							<dc:contributor>
								<xsl:value-of select="abcd:ContentContact/abcd:Name"/>
							</dc:contributor>
							</xsl:if>
						</xsl:for-each>
						<xsl:for-each select="abcd:Gathering/abcd:Agents">
							<xsl:choose>
								<xsl:when test="abcd:GatheringAgent/abcd:Person/abcd:FullName!=''">
									<dc:contributor>
										<xsl:value-of select="abcd:GatheringAgent/abcd:Person/abcd:FullName"/>
									</dc:contributor>
								</xsl:when>
								<xsl:when test="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:InheritedName!=''">
									<dc:contributor>
										<xsl:value-of select="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:InheritedName"/>
										<xsl:if test="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:GivenNames!=''">, <xsl:value-of select="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:GivenNames"/>
										</xsl:if>
									</dc:contributor>
								</xsl:when>
								<xsl:when test="abcd:GatheringAgent/abcd:AgentText!=''">
									<dc:contributor>
										<xsl:value-of select="abcd:GatheringAgent/abcd:AgentText"/>
									</dc:contributor>
								</xsl:when>
								<xsl:when test="abcd:GatheringAgentsText!=''">
									<dc:contributor>
										<xsl:value-of select="abcd:GatheringAgentsText"/>
									</dc:contributor>
								</xsl:when>
							</xsl:choose>
						</xsl:for-each>
						<dc:contributor>
							<xsl:value-of select="$bms_publisher"/>
						</dc:contributor>
						<!---
						<dc:date>
							<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin"/>
						</dc:date>-->
						<dc:contributor>
							<xsl:value-of select="$technical_contact_name"/>
						</dc:contributor>
						<dc:publisher>
							<xsl:value-of select="$bms_datacenter"/>
						</dc:publisher>
						<dataCenter>
							<xsl:value-of select="$bms_datacenter"/>
							<!--<xsl:value-of select="../../abcd:TechnicalContacts/abcd:TechnicalContact/abcd:Name"/>-->
						</dataCenter>
						<dc:type>ABCD_Unit</dc:type>
						<dc:type>
							<xsl:value-of select="abcd:RecordBasis"/>
						</dc:type>
						<xsl:if test="abcd:MultiMediaObjects/abcd:MultiMediaObject/abcd:FileURI">
							<dc:type>MultimediaObject</dc:type>
						</xsl:if>
						<dc:type>
							<xsl:choose>
								<xsl:when test="contains(abcd:RecordBasis,'Specimen') or contains(abcd:KindOfUnit,'Specimen') 
								or abcd:RecordBasis='MaterialSample' or abcd:KindOfUnit='MaterialSample'">PhysicalObject</xsl:when>
								<xsl:when test="contains(abcd:RecordBasis,'Observation') or contains(abcd:KindOfUnit,'Observation') 
								or abcd:RecordBasis='Literature' or abcd:KindOfUnit='Literature'">Dataset</xsl:when>
								<xsl:when test="contains(abcd:RecordBasis,'Photograph') or contains(abcd:KindOfUnit,'Photograph') 
								or abcd:RecordBasis='MultimediaObject' or abcd:KindOfUnit='MultimediaObject'">Image</xsl:when>
							</xsl:choose>
						</dc:type>
						<dc:format>text/html</dc:format>
						<xsl:if test="abcd:MultiMediaObjects/abcd:MultiMediaObject/abcd:FileURI">
							<linkage type="multimedia"><xsl:value-of select="abcd:MultiMediaObjects/abcd:MultiMediaObject/abcd:FileURI"/></linkage>
						</xsl:if>
						<xsl:if test="abcd:MultiMediaObjects/abcd:MultiMediaObject/abcd:fileURI">
							<linkage type="multimedia"><xsl:value-of select="abcd:MultiMediaObjects/abcd:MultiMediaObject/abcd:fileURI"/></linkage>
						</xsl:if>
						<xsl:choose>
							<xsl:when test="abcd:RecordURI">
								<linkage type="metadata">
									<xsl:value-of select="abcd:RecordURI"/>
								</linkage>
							</xsl:when>
							<!--
							<xsl:when test="contains(abcd:UnitGUID,'http:')">
								<linkage type="metadata">
									<xsl:value-of select="abcd:UnitGUID"/>
								</linkage>
							</xsl:when>
							-->
							<xsl:otherwise>
								<linkage type="metadata">
									<xsl:value-of select="$bms_pywrapper"/>/querytool/details.cgi?dsa=<xsl:value-of select="$bms_dsa"/><xsl:text>&amp;detail=unit&amp;schema=http%3A%2F%2Fwww.tdwg.org%2Fschemas%2Fabcd%2F2.06&amp;cat=</xsl:text><xsl:value-of select="php:function('urlencode',string(abcd:UnitID))"/>
								</linkage>
							</xsl:otherwise>
						</xsl:choose>
						<dc:identifier>
							<xsl:value-of select="$bms_dsa"/>:<xsl:value-of select="abcd:UnitID"/>
						</dc:identifier>
						<xsl:if test="$ipr_statement">
						<dc:source>
							<xsl:value-of select="$ipr_statement"/>
						</dc:source>
						</xsl:if>
						<dc:coverage xsi:type="CoverageType">
							<xsl:if test="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LatitudeDecimal!=''">
								<northBoundLatitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LatitudeDecimal"/>
								</northBoundLatitude>
								<westBoundLongitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LongitudeDecimal"/>
								</westBoundLongitude>
								<southBoundLatitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LatitudeDecimal"/>
								</southBoundLatitude>
								<eastBoundLongitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LongitudeDecimal"/>
								</eastBoundLongitude>
							</xsl:if>
							<xsl:if test="abcd:Gathering/abcd:Country/abcd:Name!=''">
								<location>
									<xsl:value-of select="abcd:Gathering/abcd:Country/abcd:Name"/>
								</location>
							</xsl:if>
							<!--
							<xsl:if test="abcd:Gathering/abcd:LocalityText!=''">
								<location>
									<xsl:value-of select="abcd:Gathering/abcd:LocalityText"/>
								</location>
							</xsl:if>
							-->
							<xsl:if test="abcd:Gathering/abcd:NamedAreas">
								<xsl:for-each select="abcd:Gathering/abcd:NamedAreas">
									<location>
										<xsl:value-of select="abcd:NamedArea/abcd:AreaName"/>
									</location>
								</xsl:for-each>
							</xsl:if>
							<xsl:choose>
								<xsl:when test="php:function('oai::convertdate',string(abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin))!=''">
									<startDate>
										<xsl:value-of select="php:function('oai::convertdate',string(abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin))"/>
									</startDate>
									<xsl:choose>
										<xsl:when test="php:function('oai::convertdate',string(abcd:Gathering/abcd:DateTime/abcd:ISODateTimeEnd))!=''">
											<endDate>
												<xsl:value-of select="php:function('oai::convertdate',string(abcd:Gathering/abcd:DateTime/abcd:ISODateTimeEnd))"/>
											</endDate>
										</xsl:when>
										<xsl:otherwise>
											<endDate>
												<xsl:value-of select="php:function('oai::convertdate',string(abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin))"/>
											</endDate>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:when>
								<xsl:when test="php:function('oai::convertdate',string(abcd:Gathering/abcd:DateTime/abcd:DateText[1]))!=''">
									<startDate>
										<xsl:value-of select="php:function('oai::convertdate',string(abcd:Gathering/abcd:DateTime/abcd:DateText[1]))"/>
									</startDate>
									<endDate>
										<xsl:value-of select="php:function('oai::convertdate',string(abcd:Gathering/abcd:DateTime/abcd:DateText[1]))"/>
									</endDate>
								</xsl:when>
							</xsl:choose>
						</dc:coverage>
						<xsl:if test="abcd:MultiMediaObjects/abcd:MultiMediaObject/abcd:FileURI!=''">
						<dc:subject xsi:type="SubjectType" type="parameter">Multimedia Object</dc:subject>
						</xsl:if>
						<xsl:if test="abcd:Gathering/abcd:Altitude!=''">
						<dc:subject xsi:type="SubjectType" type="parameter">Altitude</dc:subject>
						</xsl:if>
						<xsl:if test="abcd:Gathering/abcd:LocalityText!=''">
						<dc:subject xsi:type="SubjectType" type="parameter">Locality</dc:subject>
						</xsl:if>
						<xsl:if test="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin!=''">
						<dc:subject xsi:type="SubjectType" type="parameter">Date</dc:subject>
						</xsl:if>
						<xsl:if test="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LatitudeDecimal!=''">
						<dc:subject xsi:type="SubjectType" type="parameter">Latitude</dc:subject>
						</xsl:if>
						<xsl:if test="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LongitudeDecimal!=''">
						<dc:subject xsi:type="SubjectType" type="parameter">Longitude</dc:subject>
						</xsl:if>
						<xsl:if test="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString">
							<dc:subject xsi:type="SubjectType" type="taxonomy">
								<xsl:value-of select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString"/>
							</dc:subject>
						</xsl:if>
						<xsl:for-each select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:HigherTaxa">
							<dc:subject xsi:type="SubjectType" type="taxonomy">
								<xsl:value-of select="abcd:HigherTaxon/abcd:HigherTaxonName"/>
							</dc:subject>
						</xsl:for-each>
						<xsl:if test="abcd:UnitExtension/efg:EarthScienceSpecimen/efg:UnitStratigraphicDetermination/efg:ChronostratigraphicAttributions">
							<xsl:for-each select="abcd:UnitExtension/efg:EarthScienceSpecimen/efg:UnitStratigraphicDetermination/efg:ChronostratigraphicAttributions">
								<dc:subject xsi:type="SubjectType" type="stratigraphy">
									<xsl:value-of select="efg:ChronostratigraphicAttribution/efg:ChronostratigraphicName"/>
								</dc:subject>
							</xsl:for-each>
						</xsl:if>
						<xsl:if test="abcd:Stratigraphy/abcd:ChronostratigraphicTerms">
							<xsl:for-each select="abcd:Stratigraphy/abcd:ChronostratigraphicTerms">
								<dc:subject xsi:type="SubjectType" type="stratigraphy">
									<xsl:value-of select="abcd:ChronostratigraphicTerm/abcd:Term"/>
								</dc:subject>
							</xsl:for-each>
						</xsl:if>
						<!--+lithostrat-->
					
						<xsl:if test="$terms_of_use_text">
						<xsl:if test="not($terms_of_use_text =$licence_text)">
							<dc:rights>
								<xsl:value-of select="$terms_of_use_text"/>
							</dc:rights>
							</xsl:if>
						</xsl:if>
						<xsl:if test="$licence_text">
							<dc:rights>
							<xsl:text>License: </xsl:text><xsl:value-of select="$licence_text"/>
							</dc:rights>
						</xsl:if>
						<xsl:for-each select="abcd:MultiMediaObjects">
							<xsl:if test="abcd:MultiMediaObject/abcd:IPR/abcd:Licenses/abcd:License/abcd:Text">
								<dc:rights>
							<xsl:text>License for associated multimedia objects: </xsl:text><xsl:value-of select="abcd:MultiMediaObject/abcd:IPR/abcd:Licenses/abcd:License/abcd:Text"/>
								</dc:rights>
							</xsl:if>
						</xsl:for-each>
						<!--	Licence (multimedia objects): /DataSets/DataSet/Units/Unit/MultiMediaObjects/MultiMediaObject/IPR/Licenses/License/Text-->
							<!--	<xsl:if test="../../abcd:Metadata/abcd:IPRStatements/abcd:Citations"/>
							
<xsl:for-each select="../../abcd:Metadata/abcd:IPRStatements/abcd:Citations">
							<dc:source>
								<xsl:value-of select="abcd:Citation/abcd:Text"/>
								<xsl:if test="abcd:Citation/abcd:Details">
										, <xsl:value-of select="abcd:Citation/abcd:Details"/>
								</xsl:if>
								<xsl:if test="abcd:Citation/abcd:URI">
										. <xsl:value-of select="abcd:Citation/abcd:URI"/>
								</xsl:if>
							</dc:source>
						</xsl:for-each>
			
						<xsl:if test="abcd:UnitReferences/abcd:UnitReference/abcd:TitleCitation">
							<dc:source>
								<xsl:value-of select="abcd:UnitReferences/abcd:UnitReference/abcd:TitleCitation"/>
							</dc:source>
						</xsl:if>
-->
						<xsl:if test="$dataset_uri">
							<dc:relation>
								<xsl:value-of select="$dataset_uri"/>
							</dc:relation>
						</xsl:if>
						<xsl:if test="abcd:Associations/abcd:UnitAssociation/abcd:AssociatedUnitID">
							<!--here a type declaration would be necessary: IsPartOf-->
							<dc:relation>
								<xsl:value-of select="abcd:Associations/abcd:UnitAssociation/abcd:AssociatedUnitID"/>
							</dc:relation>
						</xsl:if>
						<parentIdentifier>
						<xsl:text>urn:gfbio.org:abcd:set:</xsl:text><xsl:value-of select="$bms_archive_folder"/>
						<!--<xsl:choose>
						<xsl:when test="../../abcd:Metadata/abcd:Description/abcd:Representation/abcd:URI">							
								<xsl:value-of select="../../abcd:Metadata/abcd:Description/abcd:Representation/abcd:URI"/>							
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="../../BMS_ArchiveFolder"/>
						</xsl:otherwise>
						</xsl:choose>
-->
						</parentIdentifier>
						<additionalContent>
							<xsl:for-each select="descendant::*">
								<xsl:if test="string-length(normalize-space(text()))!=0">
									<xsl:value-of select="normalize-space(text())"/><xsl:text>, </xsl:text>
								</xsl:if>
							</xsl:for-each>
						</additionalContent>
					</dataset>
				</oai:metadata>
			</oai:record>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
