<?php

global $pico_editor_password;
global $pico_editor_url;

/* 
 * $pico_editor_password should be a sha512 hash of your password.
 * Use a tool like http://www.sha1-online.com to generate.
 * Or, in any shell: echo -n Y0ur_4we5OM3_P455W0RD | sha512sum
 * 
 * $pico_editor_url is the url where you access the editor, at http://yoursite/picocms_directory/admin if you set it to 'admin'
 */
$pico_editor_password = '';
$pico_editor_url = 'admin42';

?>
