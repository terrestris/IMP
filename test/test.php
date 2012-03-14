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
 * test.php
 * 
 * A little helper script to test IMP
 * 
 * @author C. Mayer <mayer@terrestris.de>
 * @author M. Jansen <jansen@terrestris.de>
 * 
 * @version 0.1
 * @since 2011-04-01
 * 
 * @id $Id: test.php 57 2012-03-14 10:30:18Z mayer $
 */

#error_reporting(E_ALL);
#ini_set('display_errors', 1);

require_once('../classes/Imp.php');

$imp = new Imp('../data/inspire-ir-md-dataset.xml', true);

#$out = $imp->asJson();
#$out = $imp->asJsonP('myCallBack');
#header('content-type: application/json');

#$out = $imp->asRawJson();
#$out = $imp->asJsonP('myCallBack');
#header('content-type: application/json');

#$out = $imp->asHtml(false);
$out = $imp->asInspireHtml();
header('content-type: text/html');

#$out = $imp->asOpenLayersFeatures();
#header('content-type: text/json');

#$out = $imp->asPdf();

echo($out);
