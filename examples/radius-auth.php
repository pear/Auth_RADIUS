<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/*
Copyright (c) 2003, Michael Bretterklieber <michael@bretterklieber.com>
All rights reserved.

Redistribution and use in source and binary forms, with or without 
modification, are permitted provided that the following conditions 
are met:

1. Redistributions of source code must retain the above copyright 
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright 
   notice, this list of conditions and the following disclaimer in the 
   documentation and/or other materials provided with the distribution.
3. The names of the authors may not be used to endorse or promote products 
   derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY 
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING 
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

This code cannot simply be copied and put under the GNU Public License or 
any other GPL-like (LGPL, GPL2) License.

    $Id$
*/

if ($argv[1] == 'pearcvs') {
    ini_set('include_path', '..:../..:' . ini_get('include_path'));
    require_once 'RADIUS.php';
    require_once 'Crypt_CHAP/CHAP.php';
} else {
    require_once 'Auth/RADIUS.php';
    require_once 'Crypt/CHAP.php';
}

//$type = 'PAP';
//$type = 'CHAP_MD5';
$type = 'MSCHAPv1';
//$type = 'MSCHAPv2';

$username = 'sepp';
$password = 'sepp';

$classname = 'Auth_RADIUS_' . $type;
$rauth = new $classname($username, $password);
$rauth->addServer('localhost', 0, 'testing123');
//$rauth->setConfigfile('/etc/radius.conf');
// turn of standard attributes
//$rauth->useStandardAttributes = false;
$rauth->username = $username;

switch($type) {
case 'CHAP_MD5':
case 'MSCHAPv1':
    $classname = $type == 'MSCHAPv1' ? 'Crypt_CHAP_MSv1' : 'Crypt_CHAP_MD5';
    $crpt = new $classname;
    $crpt->password = $password;
    $rauth->challenge = $crpt->challenge;
    $rauth->chapid = $crpt->chapid;
    $rauth->response = $crpt->challengeResponse();
    $rauth->flags = 1;
// If you must use deprecated and weak LAN-Manager-Responses use this:
//    $rauth->lmResponse = $crpt->lmChallengeResponse();
//    $rauth->flags = 0;
    break;
  
case 'MSCHAPv2':
    $crpt = new Crypt_CHAP_MSv2;
    $crpt->username = $username;
    $crpt->password = $password;
    $rauth->challenge = $crpt->authChallenge;
    $rauth->peerChallenge = $crpt->peerChallenge;
    $rauth->chapid = $crpt->chapid;
    $rauth->response = $crpt->challengeResponse();
    break;
    
default:
    $rauth->password = $password;
    break;
}

if (!$rauth->start()) {
    printf("Radius start: %s<br>\n", $rauth->getError());
    exit;
}


$result = $rauth->send();
if (PEAR::isError($result)) {
    printf("Radius send failed: %s<br>\n", $result->getMessage());
    exit;
} else if ($result === true) {
    printf("Radius Auth succeeded<br>\n");
} else {
    printf("Radius Auth rejected<br>\n");
}

// get attributes, even if auth failed
if (!$rauth->getAttributes()) {
    printf("Radius getAttributes: %s<br>\n", $rauth->getError());
} else {
    $rauth->dumpAttributes();
}

$rauth->close();


?>
