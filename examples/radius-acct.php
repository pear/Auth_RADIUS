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
$username = 'sepp';

$starttime = time();

$racct = new Auth_RADIUS_Acct_Start;
$racct->addServer('localhost', 0, 'testing123');
$racct->username = $username;
// RADIUS_AUTH_RADIUS => authenticated via Radius
// RADIUS_AUTH_LOCAL => authenicated local
// RADIUS_AUTH_REMOTE => authenticated remote
$racct->authentic = RADIUS_AUTH_LOCAL;
$status = $racct->start();
if(PEAR::isError($status)) {
    printf("Radius start: %s<br>\n", $status->getMessage());
    exit;
}
// you can put any additional attributes here
// $racct->putAttribute(RADIUS_ACCT_INPUT_PACKETS, 45236);
// $racct->putAttribute(RADIUS_ACCT_OUTPUT_PACKETS, 1212);
$result = $racct->send();
if (PEAR::isError($result)) {
    printf("Radius send failed: %s<br>\n", $result->getMessage());
    exit;
} else if ($result === true) {
    printf("Radius Acounting succeeded<br>\n");
} else {
    printf("Radius Acounting rejected<br>\n");
}

$racct->close();

// Wait a bit, that we can put the session-time
sleep(2);

// send an accounting update
$racct = new Auth_RADIUS_Acct_Update;
$racct->addServer('localhost', 0, 'testing123');
$racct->username = $username;
$racct->session_time = time() - $starttime;
$status = $racct->start();
if(PEAR::isError($status)) {
    printf("Radius start: %s<br>\n", $status->getMessage());
    exit;
}
$result = $racct->send();
if (PEAR::isError($result)) {
    printf("Radius send failed: %s<br>\n", $result->getMessage());
    exit;
} else if ($result === true) {
    printf("Radius Acounting succeeded<br>\n");
} else {
    printf("Radius Acounting rejected<br>\n");
}

// Wait a bit, that we can put the session-time
sleep(2);

// send the stop
$racct = new Auth_RADIUS_Acct_Stop;
$racct->addServer('localhost', 0, 'testing123');
$racct->username = $username;
$racct->session_time = time() - $starttime;
$status = $racct->start();
if(PEAR::isError($status)) {
    printf("Radius start: %s<br>\n", $status->getMessage());
    exit;
}
// you can put any additional attributes here
// $racct->putAttribute(RADIUS_ACCT_TERMINATE_CAUSE, RADIUS_TERM_SESSION_TIMEOUT);
$result = $racct->send();
if (PEAR::isError($result)) {
    printf("Radius send failed: %s<br>\n", $result->getMessage());
    exit;
} else if ($result === true) {
    printf("Radius Acounting succeeded<br>\n");
} else {
    printf("Radius Acounting rejected<br>\n");
}

$racct->close();

?>
