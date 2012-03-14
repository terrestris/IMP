<?php

/*
 * Copyright (c) 2012, terrestris GmbH & Co. KG
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, 
 *   this list of conditions and the following disclaimer in the documentation 
 *   and/or other materials provided with the distribution.
 * - Neither the name of terrestris GmbH & Co. KG nor the names of its 
 *   contributors may be used to endorse or promote products derived from this 
 *   software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * BSD-License from http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once('MetadataRecord.php');
require_once('Dataset.php');
require_once('Series.php');
require_once('Application.php');
require_once('Service.php');
require_once('NonGeoGraphicDataset.php');
require_once('Thesaurus.php');

/**
 * 
 * MetadataRecord.php
 * 
 * This is the class for parsing the XML document and 
 * transferring it into the PHP object structure
 * 
 * 
 * @author C. Mayer <mayer@terrestris.de>
 * @author M. Jansen <jansen@terrestris.de>
 * 
 * @version 0.1
 * @since 2011-04-01
 * 
 * @id $Id: Parser.php 58 2012-03-14 10:33:56Z mayer $
 * 
 */
class Parser {
    
    /**
     * Reference to the xml object to parse
     */
    private $xml_simple;
    
    /**
     * 
     */
    private $xml_string;
    
    /**
     * The parsed records to be returned
     */
    private $returnRecords;
    
    /**
     * The last parsed record
     */
    private $recordObject;
    
    /**
     * Counter having the number of parsed records
     */
    public $numRecords;
    
    
    /**
     * The parser instance 
     * 
     * @constructor
     * 
     * @return The parser instance 
     */
    public function Parser() {
        
    }
    
    /**
     * Helper function to check if a given varibale
     * is set and not null.
     * 
     * @param object $var
     * @param object $idx
     * @return Boolean indicating given varibale is set and not null
     */
    private function setAndNotNull($var, $idx) {
        $setAndNotNull = false;
        if (isset($var) && $var != null && isset($var[$idx]) && $var[$idx] != null) {
            $setAndNotNull = true;    
        }
        return $setAndNotNull;
    }
    
    /**
     * Main function parsing the XML.
     * Delegates the parsing of the single records.
     * 
     * @param object $xml_string
     * @return an array of parsed records
     */
    public function parseXML($xml_string) {
        
        if ($xml_string !== null && $xml_string !== '') {
            
            $this->returnRecords = array();
            
            $this->xml_simple = simplexml_load_string(strval($xml_string));
            
            if ($this->xml_simple->registerXPathNamespace('gmd', 'http://www.isotc211.org/2005/gmd') === false) {
                   throw new exception ("Parser::parseXML - Registring needed namespace failed");
            }
            
            if ($this->xml_simple->registerXPathNamespace('csw', 'http://www.opengis.net/cat/csw/2.0.2') === false) {
                   throw new exception ("Parser::parseXML - Registring needed namespace failed");
            }
            
            
            // get single records and iterate over
            $result = $this->xml_simple->xpath('//gmd:MD_Metadata');
            if($result === FALSE) {
                throw new exception ("Parser::parseXML - Problem while converting XML string to Object");
            }
            
            foreach ($result as $md_metadata) {
                array_push($this->returnRecords, Parser::parseSingleRecord($md_metadata));
            }
            
            //get number of matches
            $number = $this->xml_simple->xpath('SearchResults//@numberOfRecordsMatched');
            if ($this->setAndNotNull($number, 0)) {
                $this->numRecords = intval($number[0]);
            }
            // in some cases the num is nor read out correctly --> read out with regex
            // TODO check xpath
            else {
                $pattern = '/numberOfRecordsMatched="(\d*)"/';
                $subject = $this->xml_simple->asXML();
                $groups = array();
                preg_match($pattern, $subject, $groups);
                if ($this->setAndNotNull($groups, 1)) {
                    $this->numRecords = intval($groups[1]);
                }
            }
            
            return $this->returnRecords;
        }
        else {
            throw new Exception('Parser::parseXML - Need a valid XML as argument');
        }
    }
    
    
    /**
     * Parses a single metadata record
     * 
     * @param object $xmlSingleRecord
     * @return a metadata record object
     */
    private function parseSingleRecord($xmlSingleRecord) {
        
        
        // empty the record object
        $this->recordObject = null;
        
        // parse the given XML part
        $md_metadata_xml = simplexml_load_string($xmlSingleRecord->asXML());
        
        // set missing namespaces
        if ($md_metadata_xml->registerXPathNamespace('gco', 'http://www.isotc211.org/2005/gco') == false) {
            echo "registring needed namespace failed";
        }
        if ($md_metadata_xml->registerXPathNamespace('gmd', 'http://www.isotc211.org/2005/gmd') == false) {
               echo "registring needed namespace failed";
        }
        if ($md_metadata_xml->registerXPathNamespace('srv', 'http://www.isotc211.org/2005/srv') == false) {
               echo "registring needed namespace failed";
        }
        
        
        // ressource type
        // done
        $ressource_res = $md_metadata_xml->xpath('gmd:hierarchyLevel/gmd:MD_ScopeCode//@codeListValue');
        if ($ressource_res == null) {
            $ressource_res = $md_metadata_xml->xpath('gmd:dataQualityInfo/gmd:DQ_DataQuality/gmd:scope/gmd:DQ_Scope/gmd:level/gmd:MD_ScopeCode//@codeListValue');
        }
        
        $ressource = "";
        if($this->setAndNotNull($ressource_res, 0)) {
            $ressource = (string)$ressource_res[0];
        }
        
        // decide which object we need
        if (strnatcasecmp($ressource, 'dataset') === 0) {
            $this->recordObject = new Dataset();
        }
        elseif(strnatcasecmp($ressource, 'series') === 0) {
            $this->recordObject = new Series();
        }
        elseif(strnatcasecmp($ressource, 'application') === 0) {
            $this->recordObject = new Application();
        }
        elseif(strnatcasecmp($ressource, 'service') === 0) {
            $this->recordObject = new Service();
        }
        elseif(strnatcasecmp($ressource, 'nonGeographicDataset') === 0) {
            $this->recordObject = new NonGeoGraphicDataset();
        }
        else {
            $this->recordObject = new Dataset();
        }
        
        
        $this->recordObject->setScopeCode($ressource);
        
        
        
        // uid 
        // done
        $identifier = $md_metadata_xml->xpath('gmd:fileIdentifier/gco:CharacterString');
        
        if($this->setAndNotNull($identifier, 0)) {
            $this->recordObject->setFileIdentifier(trim((string)$identifier[0]));
        }
        
    
        // done
        // TODO check if there are more than one URI possible
        $ressourceLink = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:transferOptions[1]/gmd:MD_DigitalTransferOptions/gmd:onLine/gmd:CI_OnlineResource/gmd:linkage/gmd:URL');
        if($this->setAndNotNull($ressourceLink, 0)) {
            $this->recordObject->setOnlineResource(trim((string)$ressourceLink[0]));
        }
    
        // 7 SPECIFICATION
        // done
        $conformanceResult = array('title' => null, 'date' => null, 'dateType' => null, 'pass' => null);
        
        // TODO check XPATH does not fit with exmaple from INSPIRE
        #$spec = $md_metadata_xml->xpath('gmd:dataQualityInfo/gmd:DQ_DataQuality/gmd:report/gmd:DQ_DomainConsistency/gmd:result/gmd:DQ_ConformanceResult/gmd:specification/gmd:citation/gmd:CI_Citation/gmd:title/gco:CharacterString');
        
        $spec = $md_metadata_xml->xpath('gmd:dataQualityInfo/gmd:DQ_DataQuality/gmd:report/gmd:DQ_DomainConsistency/gmd:result/gmd:DQ_ConformanceResult/gmd:specification/gmd:CI_Citation/gmd:title/gco:CharacterString');
        
        if($this->setAndNotNull($spec, 0)) {
            $conformanceResult['title'] = trim((string)$spec[0]);
        }
        
        // TODO check XPATH does not fit with exmaple from INSPIRE
        #$specDate = $md_metadata_xml->xpath('gmd:dataQualityInfo/gmd:DQ_DataQuality/gmd:report/gmd:DQ_DomainConsistency/gmd:result/gmd:DQ_ConformanceResult/gmd:specification/gmd:citation/gmd:CI_Citation/gmd:date/gmd:CI_Date/gmd:date/gco:DateTime');
        
        $specDate = $md_metadata_xml->xpath('gmd:dataQualityInfo/gmd:DQ_DataQuality/gmd:report/gmd:DQ_DomainConsistency/gmd:result/gmd:DQ_ConformanceResult/gmd:specification/gmd:CI_Citation/gmd:date/gmd:CI_Date/gmd:date');
        if($this->setAndNotNull($specDate, 0)){
            $dateTime = new DateTime((string)$specDate[0]);
            $conformanceResult['date'] = date_format($dateTime, 'd.m.Y');
        }

        // TODO check XPATH does not fit with exmaple from INSPIRE
        #$specDateType = $md_metadata_xml->xpath('gmd:dataQualityInfo/gmd:DQ_DataQuality/gmd:report/gmd:DQ_DomainConsistency/gmd:result/gmd:DQ_ConformanceResult/gmd:specification/gmd:citation/gmd:CI_Citation/gmd:date/gmd:CI_Date/gmd:dateType/gmd:CI_DateTypeCode');
        
        $specDateType = $md_metadata_xml->xpath('gmd:dataQualityInfo/gmd:DQ_DataQuality/gmd:report/gmd:DQ_DomainConsistency/gmd:result/gmd:DQ_ConformanceResult/gmd:specification/gmd:CI_Citation/gmd:date/gmd:CI_Date/gmd:dateType/gmd:CI_DateTypeCode');
        if ($this->setAndNotNull($specDateType, 0)) {
            $conformanceResult['dateType'] = Thesaurus::getMdDateTypeTranslation(trim((string)$specDateType[0]));
        }

        $specPass = $md_metadata_xml->xpath('gmd:dataQualityInfo/gmd:DQ_DataQuality/gmd:report/gmd:DQ_DomainConsistency/gmd:result/gmd:DQ_ConformanceResult/gmd:pass/gco:Boolean');
        if ($this->setAndNotNull($specPass, 0)) {
            $conformanceResult['pass'] = Thesaurus::getSpecificationPassTranslation(trim((string)$specPass[0]));
        }
        
        $this->recordObject->setConformanceResult($conformanceResult);
        
        
        // Responsible Party
        // TODO check if there are other properties to parse for responsible party
        $responsibleParty = array('organisationName' => null, 'electronicMailAddress'  => null);
        
        $metaOrganisation = $md_metadata_xml->xpath('gmd:contact/gmd:CI_ResponsibleParty/gmd:organisationName/gco:CharacterString');
        if ($this->setAndNotNull($metaOrganisation, 0)) {
            $responsibleParty['organisationName'] = trim((string)$metaOrganisation[0]);
        }
        
        $role = $md_metadata_xml->xpath('gmd:contact/gmd:CI_ResponsibleParty/gmd:role/gmd:CI_RoleCode//@codeListValue');
        if ($this->setAndNotNull($role, 0)) {
            $responsibleParty['role'] = Thesaurus::getRoleTranslation(trim((string)$role[0]));
        }
        
        $metaEmail = $md_metadata_xml->xpath('gmd:contact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:electronicMailAddress/gco:CharacterString');
        if ($this->setAndNotNull($metaEmail, 0)) {
            $responsibleParty['electronicMailAddress'] = trim((string)$metaEmail[0]);
        }
        
        $this->recordObject->setResponsibleParty($responsibleParty);
        
        
        // 10.2 metadata language
        $metaDate = $md_metadata_xml->xpath('gmd:dateStamp/gco:DateTime');
        if ($this->setAndNotNull($metaDate, 0)) {
            $dateTime = new DateTime((string) $metaDate[0]);
            $this->recordObject->setDateStamp(date_format($dateTime, 'd.m.Y'));
        } else {
            $metaDate = $md_metadata_xml->xpath('gmd:dateStamp/gco:Date');
            if ($this->setAndNotNull($metaDate,0)) {
                $this->recordObject->setDateStamp(date_format($dateTime, 'd.m.Y'));
            }
            else {
                $this->recordObject->setDateStamp(null);
            }
            
        }

        
        // 10.3 metadata language
        $metaLang = $md_metadata_xml->xpath('gmd:language/gco:CharacterString');
        if (!$this->setAndNotNull( $metaLang, 0)) {
            $metaLang = $md_metadata_xml->xpath('gmd:language/gmd:LanguageCode//@codeListValue');
        }
        if ($this->setAndNotNull($metaLang, 0)) {
            $this->recordObject->setLanguage(Thesaurus::getMdLanguageTranslation(trim((string) $metaLang[0])));
        }
        else {
            $this->recordObject->setLanguage(null);
        }

        
        
        // Reference Systems
        $srs_array = array();
        
        $refSystems = $md_metadata_xml->xpath('gmd:referenceSystemInfo');
        
        for ($i=0; $i<count($refSystems); $i++) {
            
            $code      = $md_metadata_xml->xpath('gmd:referenceSystemInfo['. ($i+1) . ']/gmd:MD_ReferenceSystem/gmd:referenceSystemIdentifier/gmd:RS_Identifier/gmd:code/gco:CharacterString');
            $codeSpace = $md_metadata_xml->xpath('gmd:referenceSystemInfo['. ($i+1) . ']/gmd:MD_ReferenceSystem/gmd:referenceSystemIdentifier/gmd:RS_Identifier/gmd:codeSpace/gco:CharacterString');
                
            $refSysXML = null; 
            if ($this->setAndNotNull( $code, 0)) {
                if ($this->setAndNotNull( $codeSpace, 0)) {
                    $refSysXML = trim((String)$codeSpace[0]) . ":" . trim((string)$code[0]);
                }
                else {
                    $refSysXML = (string)$code[0];
                }
            }
        
            if (Thesaurus::getEpsgTranslation($refSysXML) !== null && Thesaurus::getEpsgTranslation($refSysXML) !== "") {
                array_push($srs_array, Thesaurus::getEpsgTranslation($refSysXML));
                
            }
            else {
                array_push($srs_array, $refSysXML);
            }
        }
        
        $this->recordObject->setReferenceSystemIdentifiers($srs_array);
        
        // DISTRIBUTOR INFOS
        $distributorInfos = array('role'=>null, 'organisationName'=>null, 'individualName'=>null, 'electronicMailAddress'=>null, 'deliveryPoint'=>null, 'postalCode'=>null, 'city'=>null, 'phoneVoice'=>null, 'phoneFacsimile'=>null, 'onlineResource'=>null, 'fees'=>null);
        
        $distribRole = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorContact/gmd:CI_ResponsibleParty/gmd:role/gmd:CI_RoleCode//@codeListValue');
        if ($this->setAndNotNull( $distribRole, 0)) {
            $distributorInfos['role'] = Thesaurus::getRoleTranslation(trim((string)$distribRole[0]));
        }
        
        $distribOrganisation = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorContact/gmd:CI_ResponsibleParty/gmd:organisationName/gco:CharacterString');
        if ($this->setAndNotNull( $distribOrganisation, 0)) {
            $distributorInfos['organisationName'] = trim((string)$distribOrganisation[0]);
        }
        
        $distribIndividual = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorContact/gmd:CI_ResponsibleParty/gmd:individualName/gco:CharacterString');
        if ($this->setAndNotNull( $distribIndividual, 0)) {
            $distributorInfos['individualName'] =  utf8_decode(trim((string)$distribIndividual[0]));
        }
        
        $distribEmail = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:electronicMailAddress/gco:CharacterString');
        if ($this->setAndNotNull( $distribEmail, 0)) {
            $distributorInfos['electronicMailAddress'] =  trim((string)$distribEmail[0]);
        }
        
        $distribStreet = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:deliveryPoint/gco:CharacterString');
        if ($this->setAndNotNull( $distribStreet, 0)) {
            $distributorInfos['deliveryPoint'] =  trim((string)$distribStreet[0]);
        }
        
        $distribPostalCode = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:postalCode/gco:CharacterString');
        if ($this->setAndNotNull( $distribPostalCode, 0)) {
                $distributorInfos['postalCode'] =  trim((string)$distribPostalCode[0]);
        }
        
        $distribCity = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city/gco:CharacterString');
        if ($this->setAndNotNull( $distribCity, 0)) {
                $distributorInfos['city'] =  trim((string)$distribCity[0]);
        }
        
        $distribPhone = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:voice/gco:CharacterString');
        if ($this->setAndNotNull( $distribPhone, 0)) {
            $distributorInfos['phoneVoice'] =  trim((string)$distribPhone[0]);
        }
        
        $distribFacs = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:facsimile/gco:CharacterString');
        if ($this->setAndNotNull( $distribFacs, 0)) {
            $distributorInfos['phoneFacsimile'] =  trim((string)$distribFacs[0]);
        }
        
        $distribHomepage = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:onlineResource/gmd:CI_OnlineResource/gmd:linkage/gmd:URL');
        if ($this->setAndNotNull( $distribHomepage, 0)) {
            $distributorInfos['onlineResource'] =  trim((string)$distribHomepage[0]);
        }
        
        $distribFees = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributionOrderProcess/gmd:MD_StandardOrderProcess/gmd:fees/gco:CharacterString');
        if ($this->setAndNotNull( $distribFees, 0)) {
            $distributorInfos['fees']  = trim((string)$distribFees[0]);
        }
        
        $this->recordObject->setDistributor($distributorInfos);
        
        /*
         * Delegate the parsing for ressource specific properties
         */
        if ($this->recordObject instanceof DataSet) {
            $this->parseDataSetProperties($md_metadata_xml);
        }
        elseif($this->recordObject instanceof Series) {
            $this->parseDataSetProperties($md_metadata_xml);
        }
        elseif($this->recordObject instanceof Application) {
            $this->parseDataSetProperties($md_metadata_xml);
        }
        elseif($this->recordObject instanceof Service) {
            $this->parseServiceProperties($md_metadata_xml);
        }
        elseif($this->recordObject instanceof NonGeoGraphicDataset) {
            $this->parseDataSetProperties($md_metadata_xml);
        }
        else {
            
        }
        
        //$this->parseServiceProperties($md_metadata_xml);
        
        return $this->recordObject;
    }
    
    
    /**
     * Parses the DataSet specific tags and puts it into the recordObject reference
     * 
     * @param object $md_metadata_xml
     */
    private function parseDataSetProperties($md_metadata_xml) {
        
        // translation of ressource
        $this->recordObject->setScopeCode(Thesaurus::getMdRessourceTranslation($this->recordObject->getScopeCode()));
        
        // title
        $title_res = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/gmd:title/gco:CharacterString');
        $this->recordObject->setTitle(trim((string)$title_res[0]));
        
            
        
        // abstract
        $abstract = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:abstract/gco:CharacterString');
        $this->recordObject->setRecordAbstract(trim((string)$abstract[0]));
        

        // language of ressource
        $ressourceLang = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:language/gmd:LanguageCode//@codeListValue');
        if (!$this->setAndNotNull($ressourceLang, 0)) {
            $ressourceLang = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:language/gco:CharacterString');
        }
        if ($this->setAndNotNull($ressourceLang, 0)) {
            $this->recordObject->setRessourceLang(Thesaurus::getMdLanguageTranslation(trim((string)$ressourceLang[0])));
        }
        else {
            $this->recordObject->setRessourceLang(null);
        }
        
        
        // MD Topic Category
        // TODO: test value ogf translation, otherwise null returned
        $topicCategory = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:topicCategory/gmd:MD_TopicCategoryCode');
        if ($this->setAndNotNull($topicCategory, 0)) {
            $this->recordObject->setTopicCategory(Thesaurus::translateTopicCategory(trim((string)$topicCategory[0])));
        }
        else {
            $this->recordObject->setTopicCategory("");
        }
        
        
        
        // KEYWORDS
        $descriptiveKeywords = array('keywords' => null, 'thesaurusName' => null, 'thesaurusDate' => null, 'thesaurusDateType' => null);
        
        $keywords = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:descriptiveKeywords[1]/gmd:MD_Keywords/gmd:keyword/gco:CharacterString');
        $cnt = 0;
        $keyword_arr = array();
        while ($this->setAndNotNull($keywords, $cnt)) {
            array_push($keyword_arr, trim((string)$keywords[$cnt]));
            $cnt++;
        }
        $keyword_arr = array_unique($keyword_arr);
        $descriptiveKeywords['keywords'] = $keyword_arr;
        
        
        #$this->recordObject->setKeywords($keyword_arr);
        
        
        
        // VOCABULARY with date and date type
        $voc =    $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:thesaurusName/gmd:CI_Citation/gmd:title/gco:CharacterString');
        
        if ($this->setAndNotNull($voc, 0)) {
            $descriptiveKeywords['thesaurusName'] = trim((string)$voc[0]);
        }
        
        
        #$this->recordObject->setKeywordThesaurusName(trim((string)$voc[0]));
    
        
        $vocDate =    $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:thesaurusName/gmd:CI_Citation/gmd:date/gmd:CI_Date/gmd:date/gco:Date');
        if ($this->setAndNotNull($vocDate, 0)) {
            $dateTime = new DateTime((string)$vocDate[0]);
            $descriptiveKeywords['thesaurusDate'] = date_format($dateTime, 'd.m.Y');
        }
        else {
            $vocDate =    $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:thesaurusName/gmd:CI_Citation/gmd:date/gmd:CI_Date/gmd:date/gco:DateTime');
            if ($this->setAndNotNull($vocDate, 0)) {
                $dateTime = new DateTime((string) $vocDate[0]);
                $descriptiveKeywords['thesaurusDate'] = date_format($dateTime, 'd.m.Y');
            }
        }
        
        $vocDateType =    $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:thesaurusName/gmd:CI_Citation/gmd:date/gmd:CI_Date/gmd:dateType/gmd:CI_DateTypeCode//@codeListValue');
        if ($this->setAndNotNull($vocDateType, 0)) {
            $descriptiveKeywords['thesaurusDateType'] = Thesaurus::getMdDateTypeTranslation(trim((string)$vocDateType[0]));
        }
        
        $this->recordObject->setDescriptiveKeywords($descriptiveKeywords);
        
        // BBOX
        $westBoundLong = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/gmd:westBoundLongitude/gco:Decimal');
        
        $eastBoundLong = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/gmd:eastBoundLongitude/gco:Decimal');
        
        $southBoundLat = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/gmd:southBoundLatitude/gco:Decimal');
        
        $northBoundLat = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/gmd:northBoundLatitude/gco:Decimal');
        
        $this->recordObject->setGeographicBoundingBox(array(
                                            'westBoundLongitude' => floatval($westBoundLong[0]),
                                            'eastBoundLongitude' => floatval($eastBoundLong[0]),
                                            'southBoundLatitude' => floatval($southBoundLat[0]),
                                            'northBoundLatitude' => floatval($northBoundLat[0])
        ));
        
        
        
        // TIMERANGE WITH TIMEINSTANT
        $temporalParts = array('begin' => null, 'end' => null);
        $tempExtentBegin = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:temporalElement/gmd:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:begin/gml:TimeInstant/gml:timePosition');
        if ($this->setAndNotNull($tempExtentBegin, 0)) {
            $dateTime = new DateTime((string)$tempExtentBegin[0]);
            $temporalParts['begin'] = date_format($dateTime, 'd.m.Y');
        }
        
        $tempExtentEnd = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:temporalElement/gmd:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:end/gml:TimeInstant/gml:timePosition');
        if ($this->setAndNotNull($tempExtentEnd, 0)) {
            $dateTime = new DateTime((string)$tempExtentEnd[0]);
            $temporalParts['end'] = date_format($dateTime, 'd.m.Y');
        }
        
        $this->recordObject->setTemporalExtent($temporalParts);
        
        if ($temporalParts['begin'] === null && $temporalParts['end'] === null) {
            
            // TIMERANGE WITH BEGINPOSITION AND ENDPOSITION
            $temporalParts = array('begin' => null, 'end' => null);
            $tempExtentBegin = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:temporalElement/gmd:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:beginPosition');
            if ($this->setAndNotNull($tempExtentBegin, 0)) {
                $dateTime = new DateTime((string)$tempExtentBegin[0]);
                $temporalParts['begin'] = date_format($dateTime, 'd.m.Y');
            }
            
            $tempExtentEnd = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:temporalElement/gmd:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:endPosition');
            if ($this->setAndNotNull($tempExtentEnd, 0)) {
                $dateTime = new DateTime((string)$tempExtentEnd[0]);
                $temporalParts['end'] = date_format($dateTime, 'd.m.Y');
            }
            
            $this->recordObject->setTemporalExtent($temporalParts);
        
        }
        
        
        // PUBLICATION, CREATION, REVISION DATE
        $dateTag = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/gmd:date');
        $cnt = count($dateTag);
        
        for($i=1; $i<=$cnt; $i++) {
            $date = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/gmd:date[' . $i . ']/gmd:CI_Date/gmd:date/gco:DateTime');
            $dateTypeCode = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/gmd:date[' . $i . ']/gmd:CI_Date/gmd:dateType/gmd:CI_DateTypeCode//@codeListValue');
            
            if ($this->setAndNotNull($date, 0)) {
                
                $dateTime = new DateTime((string)$date[0]);
                
                $dtcode="";
                if($this->setAndNotNull($dateTypeCode, 0)) {
                    $dtcode=$dateTypeCode[0];
                }
                
                switch ($dtcode) {
                    case 'publication':
                    $this->recordObject->setPublicationDate(date_format($dateTime, 'd.m.Y'));
                    break;
                    case 'creation':
                    $this->recordObject->setCreationDate(date_format($dateTime, 'd.m.Y'));
                    break;
                    case 'revision':
                    $this->recordObject->setRevisionDate(date_format($dateTime, 'd.m.Y'));
                    break;
                }

                #$record_data[(string)$dateTypeCode[0]] = date_format($dateTime, 'd.m.Y');
            }    
        }
        
        //TODO
        $lineage = $md_metadata_xml->xpath('gmd:dataQualityInfo/gmd:DQ_DataQuality/gmd:lineage/gmd:LI_Lineage/gmd:statement/gco:CharacterString');
        if ($this->setAndNotNull($lineage, 0)) {
            $this->recordObject->setLineage(trim((string)$lineage[0]));
        }
    
        // RESOLUTION
        $resolutionArray = array('equivalentScale' => null, 'distance' => null);
        
        $resolutionScale = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:spatialResolution/gmd:MD_Resolution/gmd:equivalentScale/gmd:MD_RepresentativeFraction/gmd:denominator/gco:Integer');
        $resolutionDistance = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:spatialResolution/gmd:MD_Resolution/gmd:distance/gco:Distance');
        if ($this->setAndNotNull($resolutionScale, 0)) {
            $resolutionArray['equivalentScale'] = strval((string)$resolutionScale[0]);
        }
        if ($this->setAndNotNull($resolutionDistance, 0)) {
            $resolutionArray['distance'] = trim((string)$resolutionDistance[0]);
        }
        $this->recordObject->setResolution($resolutionArray);
        
        // RESTRICTIONS
        $legalConstraints = array('useLimitation' => null, 'accessConstraints' => null, 'useConstraints' => null, 'otherConstraints' => null);
        
        
        $useLimitation = $md_metadata_xml->xpath('gmd:identificationInfo/*/gmd:resourceConstraints/*/gmd:useLimitation/gco:CharacterString');
        
        if ($this->setAndNotNull($useLimitation, 0)) {
            $legalConstraints['useLimitation'] = trim((string)$useLimitation[0]);
        }
        
        $restriction = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:resourceConstraints/gmd:MD_LegalConstraints/gmd:accessConstraints/gmd:MD_RestrictionCode//@codeListValue');
        
        if ($this->setAndNotNull($restriction, 0)) {
            $legalConstraints['accessConstraints'] = Thesaurus::getRestrictionTranslation(trim((string)$restriction[0]));
        }
        
        $useRestriction = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:resourceConstraints/gmd:MD_LegalConstraints/gmd:useConstraints/gmd:MD_RestrictionCode//@codeListValue');
        if ($this->setAndNotNull($useRestriction, 0)) {
            $legalConstraints['useConstraints'] = Thesaurus::getRestrictionTranslation((string)$useRestriction[0]);
        }
        
        $otherConstraints = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:resourceConstraints/gmd:MD_LegalConstraints/gmd:otherConstraints/gco:CharacterString');
        if ($this->setAndNotNull($otherConstraints, 0)) {
            $legalConstraints['otherConstraints'] = trim((string)$otherConstraints[0]);
        }

        $this->recordObject->setLegalConstraints($legalConstraints);
        
        
        
        
        // POINT OF CONTACT
        
        $pointOfContact = array(
                'organisationName' => null,
                'electronicMailAddress' => null,
                'role' => null,
                'onlineResource' => null,
                'deliveryPoint' => null,
                'postalCode' => null,
                'city' => null,
                'phoneVoice' => null,
                'phoneFacsimile' => null
        );
        
        $organisation = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:organisationName/gco:CharacterString');
        if ($this->setAndNotNull($organisation, 0)) {
            $pointOfContact['organisationName'] = trim((string)$organisation[0]);
        }
    
        $email = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:electronicMailAddress/gco:CharacterString');
        if ($this->setAndNotNull($email, 0)) {
            $pointOfContact['electronicMailAddress'] = trim((string)$email[0]);
        }
        
        $role = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:role/gmd:CI_RoleCode//@codeListValue');
        if ($this->setAndNotNull($role, 0)) {
            $pointOfContact['role'] = Thesaurus::getRoleTranslation(trim((string)$role[0]));
        }
        
        // check 2 xpathes
        // TODO semantic correctness of 2nd xpath
        $homepage = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:onlineResource/gmd:CI_OnlineResource/gmd:linkage/gmd:URL');
        if(!$this->setAndNotNull($homepage, 0)) {
            $homepage = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:transferOptions[1]/gmd:MD_DigitalTransferOptions/gmd:onLine/gmd:CI_OnlineResource/gmd:linkage/gmd:URL');
        }
        if($this->setAndNotNull($homepage, 0)) {
            $pointOfContact['onlineResource'] = trim((string)$homepage[0]);
        }
        
        $street = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:deliveryPoint/gco:CharacterString');
        if($this->setAndNotNull($street, 0)) {
            $pointOfContact['deliveryPoint'] = trim((string)$street[0]);
        }
        
        $postalCode = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:postalCode/gco:CharacterString');
        if ($this->setAndNotNull($postalCode, 0)) {
            $pointOfContact['postalCode'] = trim((string)$postalCode[0]);
        }
        
        $city = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city/gco:CharacterString');
        if ($this->setAndNotNull($city, 0)) {
            $pointOfContact['city'] = trim((string)$city[0]);
        }
        
        $phone = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:voice/gco:CharacterString');
        if ($this->setAndNotNull($phone, 0)) {
            $pointOfContact['phoneVoice'] = trim((string)$phone[0]);
        }
        
        $facs = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:facsimile/gco:CharacterString');
        if ($this->setAndNotNull($facs, 0)) {
            $pointOfContact['phoneFacsimile'] = trim((string)$facs[0]);
        }
        
        $this->recordObject->setPointOfContact($pointOfContact);
        
        
        // Thumbnail
        $image = $md_metadata_xml->xpath('gmd:identificationInfo/gmd:MD_DataIdentification/gmd:graphicOverview/gmd:MD_BrowseGraphic/gmd:fileName/gco:CharacterString');
        if ($this->setAndNotNull($image, 0)) {
            $this->recordObject->setBrowseGraphic(trim((string)$image[0]));
        }
        else {
            $this->recordObject->setBrowseGraphic(null);
        }
        
        
    }
    
    /**
     * Parses the Service specific tags and puts it into the recordObject reference
     */
    private function parseServiceProperties($md_metadata_xml) {
        
        
        // 
        $linkedRessource = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:operatesOn');
        if ($this->setAndNotNull($linkedRessource, 0)) {
            $this->recordObject->setOperatesOn(trim((string)$linkedRessource[0]));
        }
        else {
            $this->recordObject->setOperatesOn(null);
        }
        
        
        $serviceType = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:serviceType/gco:LocalName');
        if ($this->setAndNotNull($serviceType, 0)) {
            $this->recordObject->setServiceType(Thesaurus::getServiceTypeTranslation(trim((string)$serviceType[0]))); 
        }
        else {
            $this->recordObject->setServiceType(null);
        }
        
        $serviceTypeVersion = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:serviceTypeVersion/gco:CharacterString');
        $this->recordObject->setServiceTypeVersion(trim((string)$serviceTypeVersion[0])); 
        
        
        // translation of ressource
        $this->recordObject->setScopeCode(Thesaurus::getMdRessourceTranslation($this->recordObject->getScopeCode()));
        
        // title
        $title_res = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:citation/gmd:CI_Citation/gmd:title/gco:CharacterString');
        $this->recordObject->setTitle(trim((string)$title_res[0]));
            
        
        // abstract
        $abstract = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:abstract/gco:CharacterString');
        $this->recordObject->setRecordAbstract(trim((string)$abstract[0]));
        

        // language of ressource
        $ressourceLang = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:language/gmd:LanguageCode//@codeListValue');
        if (!$this->setAndNotNull($ressourceLang, 0)) {
            $ressourceLang = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:language/gco:CharacterString');
        }
        if ($this->setAndNotNull($ressourceLang, 0)) {
            $this->recordObject->setRessourceLang(Thesaurus::getMdLanguageTranslation(trim((string)$ressourceLang[0])));
        }
        else {
            $this->recordObject->setRessourceLang(null);
        }
        
        
        // MD Topic Category
        // TODO: test value ogf translation, otherwise null returned
        $topicCategory = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:topicCategory/gmd:MD_TopicCategoryCode');
        if ($this->setAndNotNull($topicCategory, 0)) {
            $this->recordObject->setTopicCategory(Thesaurus::translateTopicCategory(trim((string)$topicCategory[0])));
        }
        else {
            $this->recordObject->setTopicCategory(null);
        }
        
        $linkedRessource = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:operatesOn');
        if ($this->setAndNotNull($linkedRessource, 0)) {
            $this->recordObject->setOperatesOn(trim((string)$linkedRessource[0]));
        }
        else {
            $this->recordObject->setOperatesOn(null);
        }
        
        $serviceType = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:serviceType/gco:LocalName');
        if ($this->setAndNotNull($serviceType, 0)) {
            $this->recordObject->setServiceType(Thesaurus::getServiceTypeTranslation(trim((string)$serviceType[0]))); 
        }
        else {
            $this->recordObject->setServiceType(null);
        }
        
        $serviceTypeVersion = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:serviceTypeVersion/gco:CharacterString');
        if ($this->setAndNotNull($serviceTypeVersion, 0)) {
            $this->recordObject->setServiceTypeVersion(trim((string)$serviceTypeVersion[0]));
        }
        else {
            $this->recordObject->setServiceTypeVersion(null);
        }
        
        
        
        // KEYWORDS
        $descriptiveKeywords = array('keywords' => null, 'thesaurusName' => null, 'thesaurusDate' => null, 'thesaurusDateType' => null);
        
        $keywords = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:descriptiveKeywords[1]/gmd:MD_Keywords/gmd:keyword/gco:CharacterString');
        $cnt = 0;
        $keyword_arr = array();
        while ($keywords[$cnt] != null) {
            array_push($keyword_arr, trim((string)$keywords[$cnt]));
            $cnt++;
        }
        $keyword_arr = array_unique($keyword_arr);
        $descriptiveKeywords['keywords'] = $keyword_arr;
        
        
        
        // VOCABULARY with date and date type
        $voc =    $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:thesaurusName/gmd:CI_Citation/gmd:title/gco:CharacterString');
        $descriptiveKeywords['thesaurusName'] = trim((string)$voc[0]);
        
        #$this->recordObject->setKeywordThesaurusName(trim((string)$voc[0]));
    
        
        $vocDate =    $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:thesaurusName/gmd:CI_Citation/gmd:date/gmd:CI_Date/gmd:date/gco:Date');
        if ($this->setAndNotNull($vocDate, 0)) {
            $dateTime = new DateTime((string)$vocDate[0]);
            $descriptiveKeywords['thesaurusDate'] = date_format($dateTime, 'd.m.Y');
        }
        else {
            $vocDate =    $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentificationn/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:thesaurusName/gmd:CI_Citation/gmd:date/gmd:CI_Date/gmd:date/gco:DateTime');
            if ($this->setAndNotNull($vocDate, 0)) {
                $dateTime = new DateTime((string) $vocDate[0]);
                $descriptiveKeywords['thesaurusDate'] = date_format($dateTime, 'd.m.Y');
            }
        }
        
        $vocDateType =    $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:thesaurusName/gmd:CI_Citation/gmd:date/gmd:CI_Date/gmd:dateType/gmd:CI_DateTypeCode//@codeListValue');
        if ($this->setAndNotNull($vocDateType, 0)) {
            $descriptiveKeywords['thesaurusDateType'] = Thesaurus::getMdDateTypeTranslation(trim((string)$vocDateType[0]));
        }
        
        $this->recordObject->setDescriptiveKeywords($descriptiveKeywords);
        
        
        // BBOX
        $westBoundLong = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:extent//gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/gmd:westBoundLongitude/gco:Decimal');
        if ($this->setAndNotNull($westBoundLong, 0)) {
            $record_data['westBoundLong'] = (string)$westBoundLong[0];
        }
        
        $eastBoundLong = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/gmd:eastBoundLongitude/gco:Decimal');
        if ($this->setAndNotNull($eastBoundLong, 0)) {
            $record_data['eastBoundLong'] = (string)$eastBoundLong[0];
        }
        
        $southBoundLat = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/gmd:southBoundLatitude/gco:Decimal');
        if ($this->setAndNotNull($southBoundLat, 0)) {
            $record_data['southBoundLat'] = (string)$southBoundLat[0];
        }
                
        $northBoundLat = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/gmd:northBoundLatitude/gco:Decimal');
        if ($this->setAndNotNull($northBoundLat, 0)) {
            $record_data['northBoundLat'] = (string)$northBoundLat[0];
        }
        
        $this->recordObject->setGeographicBoundingBox(array(
            'westBoundLongitude' => floatval($westBoundLong[0]),
            'eastBoundLongitude' => floatval($eastBoundLong[0]),
            'southBoundLatitude' => floatval($southBoundLat[0]),
            'northBoundLatitude' => floatval($northBoundLat[0])
        ));
        
        
        
        // TIMERANGE 
        $temporalParts = array('begin' => null, 'end' => null);
        $tempExtentBegin = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:extent/gmd:EX_Extent/gmd:temporalElement/gmd:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:begin/gml:TimeInstant/gml:timePosition');
        if ($this->setAndNotNull($tempExtentBegin, 0)) {
            $dateTime = new DateTime((string)$tempExtentBegin[0]);
            $temporalParts['begin'] = date_format($dateTime, 'd.m.Y');
        }
        
        $tempExtentEnd = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/srv:extent/gmd:EX_Extent/gmd:temporalElement/gmd:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:end/gml:TimeInstant/gml:timePosition');
        if ($this->setAndNotNull($tempExtentEnd, 0)) {
            $dateTime = new DateTime((string)$tempExtentEnd[0]);
            $temporalParts['end'] = date_format($dateTime, 'd.m.Y');
        }
        
        $this->recordObject->setTemporalExtent($temporalParts);
        
        
        
        
        // PUBLICATION, CREATION, REVISION DATE
        $dateTag = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:citation/gmd:CI_Citation/gmd:date');
        $cnt = count($dateTag);
        
        for($i=1; $i<=$cnt; $i++) {
            $date = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:citation/gmd:CI_Citation/gmd:date[' . $i . ']/gmd:CI_Date/gmd:date/gco:DateTime');
            $dateTypeCode = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:citation/gmd:CI_Citation/gmd:date[' . $i . ']/gmd:CI_Date/gmd:dateType/gmd:CI_DateTypeCode//@codeListValue');
            
            if ($date[0] != null) {
                
                $dateTime = new DateTime((string)$date[0]);
                
                $dtCode = "";
                
                if ($this->setAndNotNull($dateTypeCode, 0)) {
                    $dtCode = $dateTypeCode[0];
                }
                
                switch ($dtCode) {
                    case 'publication':
                    $this->recordObject->setPublicationDate(date_format($dateTime, 'd.m.Y'));
                    break;
                    case 'creation':
                    $this->recordObject->setCreationDate(date_format($dateTime, 'd.m.Y'));
                    break;
                    case 'revision':
                    $this->recordObject->setRevisionDate(date_format($dateTime, 'd.m.Y'));
                    break;
                }

                #$record_data[(string)$dateTypeCode[0]] = date_format($dateTime, 'd.m.Y');
            }    
        }
        
        //TODO
        $lineage = $md_metadata_xml->xpath('gmd:dataQualityInfo/gmd:DQ_DataQuality/gmd:lineage/gmd:LI_Lineage/gmd:statement/gco:CharacterString');
        $this->recordObject->setLineage(trim((string)$lineage[0]));
        
        
    
        // RESOLUTION
        $resolutionArray = array('equivalentScale' => null, 'distance' => null);
        
        $resolutionScale = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:spatialResolution/gmd:MD_Resolution/gmd:equivalentScale/gmd:MD_RepresentativeFraction/gmd:denominator/gco:Integer');
        $resolutionDistance = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:spatialResolution/gmd:MD_Resolution/gmd:distance/gco:Distance');
        if ($this->setAndNotNull($resolutionScale, 0)) {
            $resolutionArray['equivalentScale'] = strval((string)$resolutionScale[0]);
        }
        if ($this->setAndNotNull($resolutionDistance, 0)) {
            $resolutionArray['distance'] = trim((string)$resolutionDistance[0]);
        }
        $this->recordObject->setResolution($resolutionArray);
        
        
        
        
        // RESTRICTIONS
        $legalConstraints = array('useLimitation' => null, 'accessConstraints' => null, 'useConstraints' => null, 'otherConstraints' => null);
        
        $useLimitation = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:resourceConstraints/gmd:MD_LegalConstraints/gmd:useLimitation/gco:CharacterString');
        if ($this->setAndNotNull($useLimitation, 0)) {
            $legalConstraints['useLimitation'] = trim((string)$useLimitation[0]);
        }
        
        $restriction = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:resourceConstraints/gmd:MD_LegalConstraints/gmd:accessConstraints/gmd:MD_RestrictionCode//@codeListValue');
        if ($this->setAndNotNull($restriction, 0)) {
            $legalConstraints['accessConstraints'] = Thesaurus::getRestrictionTranslation(trim((string)$restriction[0]));
        }
        
        $useRestriction = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:resourceConstraints/gmd:MD_LegalConstraints/gmd:useConstraints/gmd:MD_RestrictionCode//@codeListValue');
        if ($this->setAndNotNull($useRestriction, 0)) {
            $legalConstraints['useConstraints'] = Thesaurus::getRestrictionTranslation((string)$useRestriction[0]);
        }
        
        $otherConstraints = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:resourceConstraints/gmd:MD_LegalConstraints/gmd:otherConstraints/gco:CharacterString');
        if ($this->setAndNotNull($otherConstraints, 0)) {
            $legalConstraints['otherConstraints'] = (string)$otherConstraints[0];
        }

        $this->recordObject->setLegalConstraints($legalConstraints);
        
        
        
        
        // POINT OF CONTACT
        
        $pointOfContact = array(
                'organisationName' => null,
                'electronicMailAddress' => null,
                'role' => null,
                'onlineResource' => null,
                'deliveryPoint' => null,
                'postalCode' => null,
                'city' => null,
                'phoneVoice' => null,
                'phoneFacsimile' => null
        );
        
        $organisation = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:organisationName/gco:CharacterString');
        if ($this->setAndNotNull($organisation, 0)) {
            $pointOfContact['organisationName'] = trim((string)$organisation[0]);
        }
    
        $email = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:electronicMailAddress/gco:CharacterString');
        if ($this->setAndNotNull($email, 0)) {
            $pointOfContact['electronicMailAddress'] = trim((string)$email[0]);
        }
        
        $role = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:role/gmd:CI_RoleCode//@codeListValue');
        if ($this->setAndNotNull($role,0)) {
            $pointOfContact['role'] = Thesaurus::getRoleTranslation(trim((string)$role[0]));
        }
        
        // check 2 xpathes
        // TODO semantic correctness of 2nd xpath
        $homepage = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:onlineResource/gmd:CI_OnlineResource/gmd:linkage/gmd:URL');
        if(!$this->setAndNotNull($homepage, 0)) {
            $homepage = $md_metadata_xml->xpath('gmd:distributionInfo/gmd:MD_Distribution/gmd:transferOptions[1]/gmd:MD_DigitalTransferOptions/gmd:onLine/gmd:CI_OnlineResource/gmd:linkage/gmd:URL');
        }
        if($this->setAndNotNull($homepage, 0)) {
            $pointOfContact['onlineResource'] = trim((string)$homepage[0]);
        }
        
        $street = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:deliveryPoint/gco:CharacterString');
        if($this->setAndNotNull($street, 0)) {
            $pointOfContact['deliveryPoint'] = trim((string)$street[0]);
        }
        
        $postalCode = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:postalCode/gco:CharacterString');
        if ($this->setAndNotNull($postalCode, 0)) {
            $pointOfContact['postalCode'] = trim((string)$postalCode[0]);
        }
        
        $city = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city/gco:CharacterString');
        if ($this->setAndNotNull($city, 0)) {
            $pointOfContact['city'] = trim((string)$city[0]);
        }
        
        $phone = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:voice/gco:CharacterString');
        if ($this->setAndNotNull($phone, 0)) {
            $pointOfContact['phoneVoice'] = trim((string)$phone[0]);
        }
        
        $facs = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:facsimile/gco:CharacterString');
        if ($this->setAndNotNull($facs, 0)) {
            $pointOfContact['phoneFacsimile'] = trim((string)$facs[0]);
        }
        
        $this->recordObject->setPointOfContact($pointOfContact);
        
        
        // Thumbnail
        $image = $md_metadata_xml->xpath('gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:graphicOverview/gmd:MD_BrowseGraphic/gmd:fileName/gco:CharacterString');
        if ($this->setAndNotNull($image, 0)) {
            $this->recordObject->setBrowseGraphic(trim((string)$image[0]));
        }
        else {
            $this->recordObject->setBrowseGraphic(null);
        }
        
        
    }
    
}
