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
 
/**
 * 
 * MetadataRecord.php
 * 
 * This is the base class representing a metadata set.
 * 
 * 
 * @author C. Mayer <mayer@terrestris.de>
 * @author M. Jansen <jansen@terrestris.de>
 * 
 * @version 0.1
 * @since 2011-04-01
 * 
 * @id $Id: MetadataRecord.php 57 2012-03-14 10:30:18Z mayer $
 * 
 */
class MetadataRecord {
    
    /*
     * General metadata tags
     */
    public $title;
    public $recordAbstract;
    public $scopeCode;
    public $onlineResource;
    public $fileIdentifier;
    public $ressourceLang;
    public $topicCategory;
    public $descriptiveKeywords;
    public $geographicBoundingBox;
    public $temporalExtent;
    public $publicationDate; 
    public $creationDate; 
    public $revisionDate;
    public $lineage;
    public $resolution;
    public $conformanceResult;
    public $legalConstraints;
    public $pointOfContact;
    public $responsibleParty;
    public $browseGraphic;
    
    public $language;
    public $referenceSystemIdentifiers;
    public $distributor;
    
    
    public $dateStamp;
    
    
    /**
     * Object representing an Metadata set 
     * 
     * @constructor
     * 
     * @return 
     */
    function class_metadata() {
        
    }
    
    
    /*
     * GETTER AND SETTER
     */

    
    /**
     * Returns $distributor.
     *
     * @see MetaDataRecord::$distributor
     */
    public function getDistributor() {
        return $this->distributor;
    }
    
    /**
     * Sets $distributor.
     *
     * @param object $distributor
     * @see MetaDataRecord::$distributor
     */
    public function setDistributor($distributor) {
        $this->distributor = $distributor;
    }
    
    /**
     * Returns $fileIdentifier.
     *
     * @see MetaDataRecord::$fileIdentifier
     */
    public function getFileIdentifier() {
        return $this->fileIdentifier;
    }
    
    /**
     * Sets $fileIdentifier.
     *
     * @param object $fileIdentifier
     * @see MetaDataRecord::$fileIdentifier
     */
    public function setFileIdentifier($fileIdentifier) {
        $this->fileIdentifier = $fileIdentifier;
    }
    
    /**
     * Returns $ressourceLang.
     *
     * @see MetadataRecord::$ressourceLang
     */
    public function getRessourceLang() {
        return $this->ressourceLang;
    }
    
    /**
     * Sets $ressourceLang.
     *
     * @param object $ressourceLang
     * @see MetadataRecord::$ressourceLang
     */
    public function setRessourceLang($ressourceLang) {
        $this->ressourceLang = $ressourceLang;
    }
    
    /**
     * Returns $topicCategory.
     *
     * @see MetadataRecord::$topicCategory
     */
    public function getTopicCategory() {
        return $this->topicCategory;
    }
    
    /**
     * Sets $topicCategory.
     *
     * @param object $topicCategory
     * @see MetadataRecord::$topicCategory
     */
    public function setTopicCategory($topicCategory) {
        $this->topicCategory = $topicCategory;
    }
    
    /**
     * Returns $descriptiveKeywords.
     *
     * @see MetadataRecord::$descriptiveKeywords
     */
    public function getDescriptiveKeywords() {
        return $this->descriptiveKeywords;
    }
    
    /**
     * Sets $descriptiveKeywords.
     *
     * @param object $descriptiveKeywords
     * @see MetadataRecord::$descriptiveKeywords
     */
    public function setDescriptiveKeywords($descriptiveKeywords) {
        $this->descriptiveKeywords = $descriptiveKeywords;
    }
    
    /**
     * Returns $geographicBoundingBox.
     *
     * @see MetadataRecord::$geographicBoundingBox
     */
    public function getGeographicBoundingBox() {
        return $this->geographicBoundingBox;
    }
    
    /**
     * Sets $geographicBoundingBox.
     *
     * @param object $geographicBoundingBox
     * @see MetadataRecord::$geographicBoundingBox
     */
    public function setGeographicBoundingBox($geographicBoundingBox) {
        $this->geographicBoundingBox = $geographicBoundingBox;
    }
    
    /**
     * Returns $temporalExtent.
     *
     * @see MetadataRecord::$temporalExtent
     */
    public function getTemporalExtent() {
        return $this->temporalExtent;
    }
    
    /**
     * Sets $temporalExtent.
     *
     * @param object $temporalExtent
     * @see MetadataRecord::$temporalExtent
     */
    public function setTemporalExtent($temporalExtent) {
        $this->temporalExtent = $temporalExtent;
    }
    
    
        /**
     * Returns $publicationDate.
     *
     * @see DataSet::$publicationDate
     */
    public function getPublicationDate() {
        return $this->publicationDate;
    }
    
    /**
     * Sets $publicationDate.
     *
     * @param object $publicationDate
     * @see DataSet::$publicationDate
     */
    public function setPublicationDate($publicationDate) {
        $this->publicationDate = $publicationDate;
    }
    
    /**
     * Returns $revisionDate.
     *
     * @see DataSet::$revisionDate
     */
    public function getRevisionDate() {
        return $this->revisionDate;
    }
    
    /**
     * Sets $revisionDate.
     *
     * @param object $revisionDate
     * @see DataSet::$revisionDate
     */
    public function setRevisionDate($revisionDate) {
        $this->revisionDate = $revisionDate;
    }
    
    /**
     * Returns $creationDate.
     *
     * @see DataSet::$creationDate
     */
    public function getCreationDate() {
        return $this->creationDate;
    }
    
    /**
     * Sets $creationDate.
     *
     * @param object $creationDate
     * @see DataSet::$creationDate
     */
    public function setCreationDate($creationDate) {
        $this->creationDate = $creationDate;
    }
    
    /**
     * Returns $lineage.
     *
     * @see DataSet::$lineage
     */
    public function getLineage() {
        return $this->lineage;
    }
    
    /**
     * Sets $lineage.
     *
     * @param object $lineage
     * @see DataSet::$lineage
     */
    public function setLineage($lineage) {
        $this->lineage = $lineage;
    }
    
    /**
     * Returns $resolution.
     *
     * @see DataSet::$resolution
     */
    public function getResolution() {
        return $this->resolution;
    }
    
    /**
     * Sets $resolution.
     *
     * @param object $resolution
     * @see DataSet::$resolution
     */
    public function setResolution($resolution) {
        $this->resolution = $resolution;
    }
    
    
    /**
     * Returns $language.
     *
     * @see MetaDataRecord::$language
     */
    public function getLanguage() {
        return $this->language;
    }
    
    /**
     * Sets $language.
     *
     * @param object $language
     * @see MetaDataRecord::$language
     */
    public function setLanguage($language) {
        $this->language = $language;
    }
    
    /**
     * Returns $onlineResource.
     *
     * @see MetaDataRecord::$onlineResource
     */
    public function getOnlineResource() {
        return $this->onlineResource;
    }
    
    /**
     * Sets $onlineResource.
     *
     * @param object $onlineResource
     * @see MetaDataRecord::$onlineResource
     */
    public function setOnlineResource($onlineResource) {
        $this->onlineResource = $onlineResource;
    }
    
    /**
     * Returns $conformanceResult.
     *
     * @see MetaDataRecord::$conformanceResult
     */
    public function getConformanceResult() {
        return $this->conformanceResult;
    }
    
    /**
     * Sets $conformanceResult.
     *
     * @param object $conformanceResult
     * @see MetaDataRecord::$conformanceResult
     */
    public function setConformanceResult($conformanceResult) {
        $this->conformanceResult = $conformanceResult;
    }
    
    /**
     * Returns $legalConstraints.
     *
     * @see DataSet::$legalConstraints
     */
    public function getLegalConstraints() {
        return $this->legalConstraints;
    }
    
    /**
     * Sets $legalConstraints.
     *
     * @param object $legalConstraints
     * @see DataSet::$legalConstraints
     */
    public function setLegalConstraints($legalConstraints) {
        $this->legalConstraints = $legalConstraints;
    }
    
    /**
     * Returns $pointOfContact.
     *
     * @see DataSet::$pointOfContact
     */
    public function getPointOfContact() {
        return $this->pointOfContact;
    }
    
    /**
     * Sets $pointOfContact.
     *
     * @param object $pointOfContact
     * @see DataSet::$pointOfContact
     */
    public function setPointOfContact($pointOfContact) {
        $this->pointOfContact = $pointOfContact;
    }
    
    
    /**
     * Returns $recordAbstract.
     *
     * @see MetaDataRecord::$recordAbstract
     */
    public function getRecordAbstract() {
        return $this->recordAbstract;
    }
    
    /**
     * Sets $recordAbstract.
     *
     * @param object $recordAbstract
     * @see MetaDataRecord::$recordAbstract
     */
    public function setRecordAbstract($recordAbstract) {
        $this->recordAbstract = $recordAbstract;
    }
    
    /**
     * Returns $referenceSystemIdentifiers.
     *
     * @see MetaDataRecord::$referenceSystemIdentifiers
     */
    public function getReferenceSystemIdentifiers() {
        return $this->referenceSystemIdentifiers;
    }
    
    /**
     * Sets $referenceSystemIdentifiers.
     *
     * @param object $referenceSystemIdentifiers
     * @see MetaDataRecord::$referenceSystemIdentifiers
     */
    public function setReferenceSystemIdentifiers($referenceSystemIdentifiers) {
        $this->referenceSystemIdentifiers = $referenceSystemIdentifiers;
    }
    
    /**
     * Returns $responsibleParty.
     *
     * @see MetaDataRecord::$responsibleParty
     */
    public function getResponsibleParty() {
        return $this->responsibleParty;
    }
    
    /**
     * Sets $responsibleParty.
     *
     * @param object $responsibleParty
     * @see MetaDataRecord::$responsibleParty
     */
    public function setResponsibleParty($responsibleParty) {
        $this->responsibleParty = $responsibleParty;
    }
    
        
    /**
     * Returns $browseGraphic.
     *
     * @see DataSet::$browseGraphic
     */
    public function getBrowseGraphic() {
        return $this->browseGraphic;
    }
    
    /**
     * Sets $browseGraphic.
     *
     * @param object $browseGraphic
     * @see DataSet::$browseGraphic
     */
    public function setBrowseGraphic($browseGraphic) {
        $this->browseGraphic = $browseGraphic;
    }
    
    /**
     * Returns $scopeCode.
     *
     * @see MetaDataRecord::$scopeCode
     */
    public function getScopeCode() {
        return $this->scopeCode;
    }
    
    /**
     * Sets $scopeCode.
     *
     * @param object $scopeCode
     * @see MetaDataRecord::$scopeCode
     */
    public function setScopeCode($scopeCode) {
        $this->scopeCode = $scopeCode;
    }
    
   
    
    /**
     * Returns $title.
     *
     * @see MetaDataRecord::$title
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * Sets $title.
     *
     * @param object $title
     * @see MetaDataRecord::$title
     */
    public function setTitle($title) {
        $this->title = $title;
    }
    
    /**
     * Returns $dateStamp.
     *
     * @see MetaDataRecord::$dateStamp
     */
    public function getDateStamp() {
        return $this->dateStamp;
    }
    
    /**
     * Sets $dateStamp.
     *
     * @param object $dateStamp
     * @see MetaDataRecord::$dateStamp
     */
    public function setDateStamp($dateStamp) {
        $this->dateStamp = $dateStamp;
    }
        
}