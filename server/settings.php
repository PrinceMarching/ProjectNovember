<?php

// Basic settings
// You must set these for the server to work

$databaseServer = "localhost";
$databaseUsername = "testUser";
$databasePassword = "testPassword";
$databaseName = "test";

// The URL of to the server.php script.
$fullServerURL = "http://localhost/jcr13/novemberServer/server.php";



// End Basic settings



// Customization settings

// Adjust these to change the way the server  works.





// Prefix to use in table names (in case more than one application is using
// the same database).  Multiple tables are created (example: "log", "reviews",
//  and so on).
//
// If $tableNamePrefix is "test_" then the tables will be named
// "test_log" and "test_reviews" and son on.
//
// Thus, more than one server installation can use the same database
// (or the server can share a database with another application that uses
//  similar table names).
$tableNamePrefix = "novemberServer_";



$enableLog = 1;


// should web-based admin require yubikey two-factor authentication?
$enableYubikey = 1;

// 12-character Yubikey IDs, one list for each access password
// each list is a set of ids separated by :
// (there can be more than one Yubikey ID associated with each password)
$yubikeyIDs = array( "ccccccbjlfbi:ccccccbjnhjc:ccccccbjnhjn", "ccccccbjlfbi" );

// used for verifying response that comes back from yubico
// Note that these are working values, but because they are in a public
// repository, they are not secret and should be replaced with your own
// values (go here:  https://upgrade.yubico.com/getapikey/ )
$yubicoClientID = "9943";
$yubicoSecretKey = "rcGgz0rca1gqqsa/GDMwXFAHjWw=";


// For hashing admin passwords so that they don't appear in the clear
// in this file.
// You can change this to your own string so that password hashes in
// this file differ from hashes of the same passwords used elsewhere.
$passwordHashingPepper = "262f43f043031282c645d0eb352df723a3ddc88f";

// passwords are given as hashes below, computed by:
// hmac_sha1( $passwordHashingPepper,
//            hmac_sha1( $passwordHashingPepper, $password ) )
// Where $passwordHashingPepper is used as the hmac key.
// Client-side hashing sends the password to the server as:
//   hmac_sha1( $passwordHashingPepper, $password )
// The extra hash performed by the server prevents the hashes in
// this file from being used to login directly without knowing the actual
// password.

// For convenience, after setting a $passwordHashingPepper and chosing a
// password, hashes can be generated by invoking passwordHashUtility.php
// in your browser.

// default passwords that have been included as hashes below are:
// "secret" and "secret2"

// hashes of passwords for for web-based admin access
$accessPasswords = array( "8e409075ab35b161f6d2d57775e5efbee8d7b674",
                          "20e1883a3d63607b60677dca87b41e04316ffc63" );


// for assigning end-user pass word sequences
// replace this with your own secret string
$passWordSelectionSecret = "83e0b8ff3bcc5861efd17dcc1990424be0a9ae61";



// number of users shown per page in the browse view
$usersPerPage = 20;


$defaultPageCharMS = 10;


$defaultPageTextColor = "#FFFFFF";
$defaultPagePromptColor = "#FFFF00";


$humanTypedPrefix = "Human: ";

$aiBufferLimit = 1000;

// send keep-alive request every 15 minutes
// (ai server spins down after 30 minutes idle)
$aiKeepAliveIntervalSeconds = 15 * 60;



// coreweave transformer URL
$coreWeaveURL = "http://put_your_url_here";


?>