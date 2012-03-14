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
 * Thesaurus.php
 * 
 * This class acts as thesaurus for translating values of the
 * codelists, which are used in the XML documents <br>
 * 
 * CAUTION at the moment only German is supported
 * 
 * @todo implement other languages
 * @static
 * 
 * @author C. Mayer <mayer@terrestris.de>
 * @author M. Jansen <jansen@terrestris.de>
 * 
 * @version 0.1
 * @since 2011-04-01
 * 
 * @id $Id: Thesaurus.php 57 2012-03-14 10:30:18Z mayer $
 */
class Thesaurus {
    
    /*
     * STATIC MEMBER VARIABLES
     */
    
    /**
     * A look up object for the MD topic list
     */
    private static $mdTopicList = array(
        'biota'=>'Biotope', 
        'boundaries'=>'Grenzen', 
        'climatologyMeteorologyAtmosphere'=>'Wetterkunde', 
        'economy'=>'Wirtschaft', 
        'elevation'=>'Hoehendaten', 
        'environment'=>'Umwelt', 
        'geoscientificInformation'=>'Geowissenschaft', 
        'health'=>'Gesundheit',
        'imageryBaseMapsEarthCover'=>'Grundlagenkarten', 
        'intelligenceMilitary'=>'militärische Aufklärung', 
        'inlandWaters'=>'Binnengewässer', 
        'location'=>'Ortsinformationen', 
        'oceans'=>'Meereskunde', 
        'planningCadastre'=>'Landnutzung/Planung/Kataster', 
        'society'=>'Gesellschaft', 
        'structure'=>'Bauwerke', 
        'transportation'=>'Transportwesen', 
        'utilitiesCommunication'=>'Infrastruktur'
    );
    
    /**
     * A look up object for the MD resources list
     */
    private static $mdResourcesList = array(
        "dataset" => "Geodatensatz",
        "series" => "Geodatensatzreihe",
        "service" => "Geodatendienst",
        "application" => "Anwendung",
        "nonGeographicDataset" => "Nicht-Geographischer Datensatz"
    );
    
    /**
     * A look up object for language codes deutsch
     */
    private static $languageCodeListGerman = array(
        "ger" => "Deutsch",
        "deu" => "Deutsch",
        "eng" => "Englisch"
    );
    
    /**
     * A look up object for the MD topic list
     */
    private static $languageCodeListEnglish = array(
        "ger" => "German",
        "deu" => "German",
        "eng" => "English"
    );
    
    /**
     * A look up object for the date types
     */
    private static $dateTypeList = array(
        'creation' => 'Erstellung',
        'publication' => 'Publikation',
        'revision' => 'Aktualisierung'
    );
    
    /**
     * A look up object for the role types
     */
    private static $mdRoleList = array(
        'resourceProvider'=> 'Anbieter',
        'custodian'=> 'Verwalter',
        'owner'=> 'Eigentümer',
        'user'=> 'Nutzer',
        'distributor'=> 'Vertrieb',
        'originator'=> 'Urheber',
        'pointOfContact'=> 'Ansprechpartner',
        'principalInvestigator'=> 'Projektleitung',
        'processor'=> 'Bearbeiter',
        'publisher'=> 'Herausgeber',
        'author'=> 'Autor'
    );
    
    /**
     * A look up object for the epsg codes
     */
    private static $epsgList = array(
        'EPSG:25832' => 'EPSG:25832 - System ETRS89; 6° UTM-Abbildung Zone 32N (GRS80-Ellipsoid)',
        'EPSG:25833' => 'EPSG:25833 - System ETRS89; 6° UTM-Abbildung Zone 33N (GRS80-Ellipsoid)',
        'EPSG:2399'  => 'EPSG:2399 - System 42/83; 3° Gauß-Krüger-Abbildung Zone 5 (Krassowski-Ellipsoid)',
        'EPSG:28403' => 'EPSG:28403 - System 42/83; 6° Gauß-Krüger-Abbildung Zone 3 (Krassowski-Ellipsoid)',
        'EPSG:3034'  => 'EPSG:3034 - System ETRS89; LCC-Abbildung (GRS80-Ellipsoid)',
        'EPSG:3035'  => 'EPSG:3035 - System ETRS89; LAEA-Abbildung (GRS80-Ellipsoid)',
        'EPSG:3044'  => 'EPSG:3044 - System ETRS89; TM-Abbildung Zone 32N (GRS80-Ellipsoid)',
        'EPSG:3045'  => 'EPSG:3045 - System ETRS89; TM-Abbildung Zone 33N (GRS80-Ellipsoid)',
        'EPSG:3068'  => 'EPSG:3068 - DHDN; Soldner-Abbildung (Bessel-Ellipsoid)',
        'EPSG:31468' => 'EPSG:31468 - System 40/83; 3° Gauß-Krüger-Abbildung Zone 4 (Bessel-Ellipsoid)',
        'EPSG:31469' => 'EPSG:31469 - System 40/83; 3° Gauß-Krüger-Abbildung Zone 5 (Bessel-Ellipsoid)',
        'EPSG:4178'  => 'EPSG:4178 - System 42/83; geographische Koordinaten (Krassowski-Ellipsoid)',
        'EPSG:4258'  => 'EPSG:4258 - System ETRS89; geographische Koordinaten (GRS80-Ellipsoid)',
        'EPSG:4314'  => 'EPSG:4314 - System 40/83; geographische Koordinaten (Bessel-Ellipsoid)',
        'EPSG:4326'  => 'EPSG:4326 - System ETRS89; geographische Koordinaten (WGS84-Ellipsoid)',
        'EPSG:5181'  => 'EPSG:5181 - DHHN92 (Deutsches Haupthöhennetz von 1992, Amsterdamer Pegel)',
        'EPSG:5183'  => 'EPSG:5183 - SNN76 (Staatliches Nivellementnetz der DDR, Kronstädter Pegel)'
    );
    
    /**
     * A look up object for the service types
     */
    private static $serviceTypeList = array(
        'discovery'=>'Suchdienst', 
        'view'=>'Darstellungsdienst', 
        'download'=>'Download-Dienst', 
        'transformation'=>'Transformationsdienst', 
        'invoke'=>'Dienst zum Abrufen von Geodatendiensten', 
        'other'=>'Sonstiger Dienst'
    );
    
    /**
     * A look up object for the classification
     */
    private static $mdClassificationList = array(
        'unclassified'=> 'unbeschränkt',
        'restricted'=> 'beschränkt',              
        'confidential'=> 'vertraulich',
        'secret'=> 'geheim',
        'topSecret'=> 'streng geheim'
    );
    
    /**
     * A look up object for the restrictions
     */
    private static $mdRestrictionList = array(
        'copyright'=> 'Urheberrecht',
        'patent'=> 'Patent',
        'patentPending'=> 'Patent angemeldet',
        'trademark'=> 'Warenzeichen',
        'license'=> 'Lizenz',
        'intellectualPropertyRights'=> 'geistiges Eigentum',
        'restricted'=> 'beschränkterZugang',
        'otherRestrictions'=> 'Andere Einschränkungen'
    );
    
    /**
     * A look up object for specification pass
     */
    private static $specificationPassGerman = array(
        'true' => 'erfüllt',
        'false' => 'nicht erfüllt'
    );
    
    /*
     * PUBLIC STATIC FUNCTIONS TO ACCESS THE PRIVATE OBJECTS ABOVE
     */
    
    /**
     * Transforms the MD Topic categories to a given language
     * 
     * @static
     * 
     * @param object $topic
     * @return 
     */
    public static function getMdTopicTranslation($topic, $lang='ger') {
        return Thesaurus::$md_topic_list[$topic];
    }
    
    /**
     * Transforms the MD_ScopeCode from the profile to a German expression
     * 
     * @static
     * 
     * @param object $ressource
     * @return 
     */
    public static function getMdRessourceTranslation($ressource, $lang='ger') {
        return Thesaurus::$mdResourcesList[$ressource];
    }
    
    /**
     * Returns the full version of the language abbreviation in a 
     * a given language (default German)
     * 
     * @static
     * 
     * @param object $langCode
     * @param object $usedLang [optional]
     * @return 
     */
    public static function getMdLanguageTranslation($langCode, $lang='ger') {
        $retVal = null;
        switch ($lang) {
            case 'ger': 
                if (isset(Thesaurus::$languageCodeListGerman) 
                    && is_array(Thesaurus::$languageCodeListGerman)
                    && $langCode
                    && isset(Thesaurus::$languageCodeListGerman[$langCode])) {
                    $retVal = Thesaurus::$languageCodeListGerman[$langCode];
                }
                break;
            case 'en':
                if (isset(Thesaurus::$languageCodeListEnglish) 
                    && is_array(Thesaurus::$languageCodeListEnglish)
                    && $langCode
                    && isset(Thesaurus::$languageCodeListEnglish[$langCode])) {
                    $retVal = Thesaurus::$languageCodeListEnglish[$langCode];
                }
                break;
        }
        return $retVal;
    }
    
    /**
     * Get the translated date type (for example creationDate => Erstellung)
     * 
     * @static
     * 
     * @param object $dateType
     * @return 
     */
    public static function getMdDateTypeTranslation($dateType) {
        return Thesaurus::$dateTypeList[$dateType];
    }
    
    /**
     * Get the translated topic category
     * 
     * @static
     * 
     * @param object $category
     * @return 
     */
    public static function translateTopicCategory($category) {
        return Thesaurus::$mdTopicList[$category];
    }
    
    /**
     * get the translated role code
     * 
     * @static
     * 
     * @param object $role
     * @return 
     */
    public static function getRoleTranslation($role) {
        return Thesaurus::$mdRoleList[$role];
    }
    
    /**
     * get the translated SRS
     * 
     * @static
     * 
     * @param object $epsg
     * @return 
     */
    public static function getEpsgTranslation($epsg) {
        if (isset(Thesaurus::$epsgList[$epsg])) {
            return Thesaurus::$epsgList[$epsg];
        }
        return null;
    }
    
    /**
     * get the traslated service type 
     * 
     * @static
     * 
     * @param object $type
     * @return 
     */
    public static function getServiceTypeTranslation($type) {
        return Thesaurus::$serviceTypeList[$type];
    }
    
    /**
     * get the translated classification term
     * 
     * @static
     * 
     * @param object $class
     * @return 
     */
    public static function getClassificationTranslation($class) {
        return Thesaurus::$mdClassificationList[$class];
    }
    
    /**
     * get the translation for the restriction terms (for example otherConstraints => Andere Einschränkungen)
     * 
     * @static
     * 
     * @param object $restriction
     * @return 
     */
    public static function getRestrictionTranslation($restriction) {
        return Thesaurus::$mdRestrictionList[$restriction];
    }
    
    
    /**
     * get the translation for the pass flag of the used specification
     * 
     * @static
     * 
     * @param object $pass
     * @param object $lang [optional]
     * @return 
     */
    public static function getSpecificationPassTranslation($pass, $lang='ger') {
        switch ($lang) {
            case 'ger': 
                return Thesaurus::$specificationPassGerman[$pass];
                break;
        }
    }
}