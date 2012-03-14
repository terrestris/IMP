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

require_once('Dataset.php');
require_once('Parser.php');

/**
 * 
 * Imp.php
 * 
 * This class acts as the controller for the whole IMP application
 * It offers the public function to call from outside. <br>
 * This class has to be instaciated
 * 
 * @link http://webmapcenter.de/imp/webseite/index.html
 * 
 * @author C. Mayer <mayer@terrestris.de>
 * @author M. Jansen <jansen@terrestris.de>
 * 
 * @version 0.1
 * @since 2011-04-01
 * 
 * @id $Id: Imp.php 58 2012-03-14 10:33:56Z mayer $
 * 
 */
class Imp {
    
    /**
     * The referrence to the XML to parse
     */
    private $xml;
    
    /**
     * Indicates wheter XML or an URL is passed 
     * to this instance
     */
    private $isUrl;

    /**
     * Constructor to get an Instance of IMP
     * Submit the XML to parse whether as String or by URL
     * 
     * @param String $xml [optional] XML as String or the URL to the XML
     * @param boolean $isUrl [optional] Defines wheter XML or an URL is passed 
     * 
     * @return void
     *  
     */
    public function Imp($xml = null, $isUrl=false) {
        
        if ($xml !== null) {
            
            if ($isUrl) {
                $this->setXml(file_get_contents($xml));
            }
            else {
                //TODO test
                #$string = str_replace('xmlns=', 'ns=', $xml_str);
                #$rw_xml = $this->reworkCswResponse($xml_str);
                $this->setXml($xml);
            }
            
        }    
    }
    
    /**
     * @todo implement
     * @return 
     */
    private function loadFromUrl() {
        
    }
    
    /**
     * @todo impelement
     * @return 
     */
    private function loadFromString() {
        
    }
    
    
    /**
     * public function in order to receive the given XML as JSON data
     * Here the default configuration is returned
     * 
     * @todo make the result set configurable from outside
     * 
     * @return a default JSON object representing the metadata XML
     */
    public function asJson() {
        
        
        $parser = new Parser();
        try {
            $records = $parser->parseXML($this->xml);
            
            $jsonContainer = array(
                'data'=>$records, 
                'total'=>$parser->numRecords,
                'success'=>true, 
                'message'=>''
            );
        }
        catch(exception $e) {
            $jsonContainer = array(
                'data'=>null, 
                'total'=>0, 
                'success'=>false, 
                'message'=>'error parsing XML: ' . $e->getMessage()
            );
        }
        
        return json_encode($jsonContainer);
    
    }
    
    /**
     * Public function in order to receive the given metadata XML 
     * as JSON data warpped in a JS callback function
     * Here the default configuration is returned
     * 
     * @param object $funcName [optional] the name of the callback function (default is callback)
     * @return a JS function with the JSON data as parameter
     */
    public function asJsonP($funcName='callback') {
        
        if ($funcName === '' ) {
            $funcName='callback';
        }
        
        try {
            $json = $this->asJson();
            
            return $funcName.'('.$json.');';
        }
        catch(exception $e) {
            // TODO fill
        }

    }
    
    /**
     * Public function in order to receive the given metadata XML 
     * as HTML. <br>
     * Here a default layout is returned which offeres an overview of the 
     * single metadata records
     * 
     * @param boolean $withLogo [optional] the flag defining if the logo is visualized (default is true)
     * @return HTML overview table with the metadata records
     */
    public function asHtml($withLogo = true) {
        $parser = new Parser();
        $htmlParts = array();
        $html = null;
        try {
            $records = $parser->parseXML($this->xml);
                  
            // HTML head and footer from template files
            $prefix = file_get_contents(dirname(__FILE__) . '/../templates/html-head.tpl.html');
            $suffix = file_get_contents(dirname(__FILE__) . '/../templates/html-footer.tpl.html');
            
            // get the template for li
            $HtmlLiTemplate = file_get_contents(dirname(__FILE__) . '/../templates/record-li.tpl.html');
            
            //echo $HtmlLiTemplate;
            
            array_push($htmlParts, $prefix);
            
            if ($withLogo == true) {
                array_push($htmlParts, '        <img src="../images/imp-logo.png" width="80" style="float:left; padding-right: 20px;">');
            }
            
            array_push($htmlParts, '        <h1>Rudiment&auml;re HTML-Ausgabe von IMP</h1>');
            
            array_push($htmlParts, '        <ol class="list-of-records">');
            
            // iterate over csw records and place the 
            // template placeholder with the values of the record
            $i = 0;
            foreach ($records as $record) {
                
                $cls = (++$i%2 == 0) ? 'even' : 'odd';
                
                $browseGraphic = "../images/keine_vorschau.png";
                if ($record->getBrowseGraphic() !== null) {
                    $browseGraphic = $record->getBrowseGraphic();
                }
                
                $search = array(
                    "[class]",
                    "[title]",
                    "[browseGraphic]",
                    "[scopeCode]",
                    "[recordAbstract]"
                );
                $replace = array(
                    $cls,
                    $record->getTitle(),
                    $browseGraphic,
                    $record->getScopeCode(),
                    $record->getRecordAbstract()
                );
                
                $htmlLi = str_replace($search, $replace, $HtmlLiTemplate);
                
                  array_push($htmlParts, $htmlLi);
                
            }
            
            array_push($htmlParts, '        </ol>');
            array_push($htmlParts, $suffix);
            
            $html = implode("\n", $htmlParts);
        }
        catch(exception $e) {
            $jsonContainer = array(
                'data'=>null, 
                'total'=>0, 
                'success'=>false, 
                'message'=>'error parsing XML: ' . $e->getMessage()
            );
        }
        
        return $html;
    }
    
    
    /**
     * Public function in order to receive the given metadata XML 
     * as HTML. <br>
     * Here a layout showing the INSPIRE relevant tags in a table 
     * is returned.
     * 
     * @param boolean $withLogo [optional] the flag defining if the logo is visualized (default is true)
     * @return HTML overview table with the INSPIRE tags
     */
    public function asInspireHtml($withLogo = true) {
        $parser = new Parser();
        $htmlParts = array();
        $html = null;
        try {
            $records = $parser->parseXML($this->xml);
            
            // HTML head and footer from template files
            $prefix = file_get_contents(dirname(__FILE__) . '/../templates/html-head.tpl.html');
            $suffix = file_get_contents(dirname(__FILE__) . '/../templates/html-footer.tpl.html');
            
            // get CSS to put it inline
            $css = file_get_contents(dirname(__FILE__) . '/../templates/style.css');
            $prefix = str_replace('<link rel="stylesheet" href="../templates/style.css">', '<style>' . $css . '</style>', $prefix);
            
            // get the template for INSPIRE table
            $inspireTable = file_get_contents(dirname(__FILE__) . '/../templates/inspire-table.tpl.html');
            
            array_push($htmlParts, $prefix);
            
            // TODO: absolute path, url to images
            if ($withLogo == true) {
                array_push($htmlParts, '        <img src="../images/imp-logo.png" width="80" style="float:left; padding-right: 20px;">');
                array_push($htmlParts, '        <img src="../images/logo-inspire.gif" width="80" style="float:left; padding-right: 20px;">');
                array_push($htmlParts, '        <h1>INSPIRE Metadaten by IMP</h1>');
            }

            $search = array(
                '[tableHead]',
                '[title]',
                '[recordAbstract]',
                '[scopeCode]',
                '[onlineResource]',
                '[fileIdentifier]',
                
                '[dataSetLanguage]',
                '[topicCategory]', 
                '[keywords]',
                '[keywordsThesaurusName]',
                '[geographicBoundingBox]',
                '[temporalExtent]',
                '[publicationDate]',
                '[revisionDate]',
                '[creationDate]',
                '[lineage]',
                '[resolution]',
                '[specification]',
                '[pass]',
                '[useConstraint]',
                '[accessConstraint]',
                '[pointOfContact]',
                '[pointOfContactRole]',
                '[responsibleParty]',
                '[dateStamp]',
                '[metaDataLanguage]'
            );
            
            foreach ($records as $record) {
                
                $descriptiveKeywordArray = $record->getDescriptiveKeywords();
                $tempExtentArray = $record->getTemporalExtent();
                $resolutionArray = $record->getResolution();
                
                $resolution = '';
                if ($resolutionArray['equivalentScale'] !== null) {
                    
                    $resolution .= 'Maßstab 1:' . $resolutionArray['equivalentScale'] . '<br>';
                }
                if($resolutionArray['distance'] !== null) {
                    $resolution .= 'Räumliche Auflösung: ' . $resolutionArray['distance'];
                }
                $conformanceResultArray = $record->getConformanceResult();
                $legalConstraintsArray = $record->getLegalConstraints();
                
                $pointOfContactArray = $record->getPointOfContact();
                
                $responsiblePartyArray = $record->getResponsibleParty();
                
                $replace = array(
                    "Metadaten für " . $record->getTitle(),
                    $record->getTitle(),
                    substr($record->getRecordAbstract(), 0, 150) . "...",
                    $record->getScopeCode(),
                    $record->getOnlineResource(),
                    $record->getFileIdentifier(),
                    $record->getRessourceLang(),
                    $record->getTopicCategory(),
                    implode(", ", $descriptiveKeywordArray['keywords']),
                    $descriptiveKeywordArray['thesaurusName'],
                    implode(", ", $record->getGeographicBoundingBox()),
                    $tempExtentArray['begin'] . ' - ' . $tempExtentArray['end'],
                    $record->getPublicationDate(),
                    $record->getRevisionDate(),
                    $record->getCreationDate(),
                    substr($record->getLineage(), 0, 150) . "...",
                    $resolution,
                    $conformanceResultArray['title'],
                    $conformanceResultArray['pass'],
                    $legalConstraintsArray['useLimitation'],
                    $legalConstraintsArray['accessConstraints'] . " " . $legalConstraintsArray['otherConstraints'],
                    $pointOfContactArray['organisationName'] . " " .  $pointOfContactArray['electronicMailAddress'],
                    $pointOfContactArray['role'],
                    $responsiblePartyArray['organisationName'] . " " . $responsiblePartyArray['electronicMailAddress'],
                    $record->getDateStamp(),
                    $record->getLanguage()
                );
                
                // replace placeholders with real values
                $filledInspireTable = str_replace($search, $replace, $inspireTable); 
                
                if ($record->getScopeCode() === 'Geodatendienst') {
                    $s = array(
                        '[operatesOn]',
                        '[serviceType]'
                    );
                    $r = array(
                        $record->getOperatesOn(),
                        $record->getServiceType() . " " . $record->getServiceTypeVersion()
                    );
                    $filledInspireTable = str_replace($s, $r, $filledInspireTable);
                }
                else {
                    
                    $s = array(
                        '[operatesOn]',
                        '[serviceType]'
                    );
                    $r = array(
                        "",
                        ""
                    );
                    $filledInspireTable = str_replace($s, $r, $filledInspireTable);
                }
                
                array_push($htmlParts, $filledInspireTable);
                
            }
            
            array_push($htmlParts, $suffix);
            
            $html = implode("\n", $htmlParts);
        }
        catch(exception $e) {
            $jsonContainer = array(
                'data'=>null, 
                'total'=>0, 
                'success'=>false, 
                'message'=>'error parsing XML: ' . $e->getMessage()
            );
        }
        
        return $html;
    }
    
    
    /**
     * Public function in order to receive the given metadata XML 
     * as OpenLayers.Features. <br>
     * With this output type the BBOX elements of the records could be directly visualized in an OL client
     * 
     * @link http://openlayers.org
     * 
     * @todo add feature attributes
     * 
     * @return JS array with OpenLayers.Feature objects
     */
    public function asOpenLayersFeatures() {
        
        //TODO add features attributes
        
        $parser = new Parser();
        $returnString = "";
        try {
            $records = $parser->parseXML($this->xml);
            
            $returnString .= '[';
            foreach ($records as $record) {
                
                $bbox = $record->getGeographicBoundingBox();
                
                // attributes
                // TODO use a JSON function of IMP instead
                $attributes = array('title' => null, 'recordAbstract' => null, 'responsibleParty' => null);
                $attributes['title'] = $record->getTitle();
                $attributes['recordAbstract'] = $record->getRecordAbstract();
                $attributes['responsibleParty'] = $record->getResponsibleParty();
                
                
                $returnString .= 'new OpenLayers.Feature.Vector(';
                $returnString .= 'new OpenLayers.Bounds(';
                $returnString .= $bbox['westBoundLongitude'] . ',';
                $returnString .= $bbox['southBoundLatitude'] . ',';
                $returnString .= $bbox['eastBoundLongitude'] . ',';
                $returnString .= $bbox['northBoundLatitude'];
                
                if ($record === end($records)) {
                    $returnString .= ').toGeometry(), ' . json_encode($attributes) . ')';
                }
                else {
                    $returnString .= ').toGeometry(), ' . json_encode($attributes) . '), ';
                }

            }
            
            $returnString .= ']'; 
            
        }
        catch(exception $e) {
            $jsonContainer = array(
                'data'=>null, 
                'total'=>0, 
                'success'=>false, 
                'message'=>'error parsing XML: ' . $e->getMessage()
            );
        }
        
        return $returnString;
        
    }
    
    /**
     * Public function in order to receive the given metadata XML 
     * as PDF. <br>
     * Here a default layout is returned which offeres an overview of the 
     * single metadata records
     * 
     * @return HTML overview table with the metadata records
     */
    public function asPdf(){
        require_once('../third-party/tcpdf/config/lang/ger.php');
        define('K_TCPDF_EXTERNAL_CONFIG', true);
        require_once('../third-party/tcpdf/config/config-imp.php');

        
        require_once('../third-party/tcpdf/tcpdf.php');

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('IMP (INSPIRE Metadata Parser)');
        $pdf->SetTitle('IMP (INSPIRE Metadata Parser)');
        $pdf->SetSubject('INSPIRE Metadata Parser');
        $pdf->SetKeywords('INSPIRE, Metadata, Parser');
        
        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
        
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        //set some language-dependent strings
        $pdf->setLanguageArray($l);
        
        
        // ---------------------------------------------------------
        
        // set default font subsetting mode
        $pdf->setFontSubsetting(true);
        
        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        $pdf->SetFont('dejavusans', '', 14, '', true);
        
        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // parse the XML
        $parser = new Parser();
        $records = $parser->parseXML($this->xml);
        
        // host
        $i = strrpos($_SERVER['PHP_SELF'], "/");
        $tmp = substr ( $_SERVER['PHP_SELF'] , 0 , $i );
        $i = strrpos($tmp, "/");
        $host = substr ( $tmp , 0 , $i );
        
        // create HTMl for output as PDF
        $htmlParts = array();
        array_push($htmlParts, '        <h1>Rudiment&auml;re PDF-Ausgabe von IMP</h1>');
        
        
        $i = 0;
        foreach ($records as $record) {
            array_push($htmlParts, '        <table border="1" cellpadding="10">');
            array_push($htmlParts, '            <tr>');
            array_push($htmlParts, '                <td style="font-size:30px">TITEL: ' . $record->getTitle() . '</td>');
            array_push($htmlParts, '                <td style="font-size:30px">RESOURCE: ' . $record->getScopeCode() . '</td>');
            array_push($htmlParts, '                <td style="font-size:30px">');
            if ($record->getBrowseGraphic() !== null) {
                array_push($htmlParts, '                <img src="' . $record->getBrowseGraphic() . '" alt="Vorschaubild" width="150" />' );
            } else {
                array_push($htmlParts, '               <img src="http://' . $_SERVER['HTTP_HOST'] . $host . '/images/keine_vorschau.jpg" alt="Vorschaubild" width="150" />');
            }
            array_push($htmlParts, '                </td>');
            array_push($htmlParts, '            </tr>');
            
            array_push($htmlParts, '            <tr>');
            array_push($htmlParts, '                <td colspan="3" style="font-size:25px">ABSTRACT: ' . $record->getRecordAbstract() . '</td>');
            array_push($htmlParts, '            </tr>');
            array_push($htmlParts, '        </table>');
            array_push($htmlParts, '        <p>&nbsp;</p>');
            
        }
        
        $html = implode("\n", $htmlParts);

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
        
        // ---------------------------------------------------------
        
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $pdf->Output('imp-pdf-output-' . date('YmdHis') . '.pdf', 'D');
        
    }

    
    /**
     * Returns $xml.
     *
     * @see Imp::$xml
     */
    public function getXml() {
        return $this->xml;
    }
    
    /**
     * Sets $xml.
     *
     * @param object $xml
     * @see Imp::$xml
     */
    public function setXml($xml) {
        $this->xml = $xml;
    }
}