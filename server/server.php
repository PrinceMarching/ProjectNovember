<?php



global $pn_version;
$pn_version = "1";



// edit settings.php to change server' settings
include( "settings.php" );




// no end-user settings below this point





// no caching
//header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache'); 



// enable verbose error reporting to detect uninitialized variables
error_reporting( E_ALL );


// for stack trace of errors
/*
set_error_handler(function($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});
*/




// page layout for web-based setup
$setup_header = "
<HTML>
<HEAD><TITLE>November Server Web-based setup</TITLE></HEAD>
<BODY BGCOLOR=#FFFFFF TEXT=#000000 LINK=#0000FF VLINK=#FF0000>

<CENTER>
<TABLE WIDTH=75% BORDER=0 CELLSPACING=0 CELLPADDING=1>
<TR><TD BGCOLOR=#000000>
<TABLE WIDTH=100% BORDER=0 CELLSPACING=0 CELLPADDING=10>
<TR><TD BGCOLOR=#EEEEEE>";

$setup_footer = "
</TD></TR></TABLE>
</TD></TR></TABLE>
</CENTER>
</BODY></HTML>";






// ensure that magic quotes are OFF
// we hand-filter all _REQUEST data with regexs before submitting it to the DB
if( get_magic_quotes_gpc() ) {
    // force magic quotes to be removed
    $_GET     = array_map( 'pn_stripslashes_deep', $_GET );
    $_POST    = array_map( 'pn_stripslashes_deep', $_POST );
    $_REQUEST = array_map( 'pn_stripslashes_deep', $_REQUEST );
    $_COOKIE  = array_map( 'pn_stripslashes_deep', $_COOKIE );
    }
    


// Check that the referrer header is this page, or kill the connection.
// Used to block XSRF attacks on state-changing functions.
// (To prevent it from being dangerous to surf other sites while you are
// logged in as admin.)
// Thanks Chris Cowan.
function pn_checkReferrer() {
    global $fullServerURL;
    
    if( !isset($_SERVER['HTTP_REFERER']) ||
        strpos($_SERVER['HTTP_REFERER'], $fullServerURL) !== 0 ) {
        
        die( "Bad referrer header" );
        }
    }




// all calls need to connect to DB, so do it once here
pn_connectToDatabase();

// close connection down below (before function declarations)


// testing:
//sleep( 5 );


// general processing whenver server.php is accessed directly




// grab POST/GET variables
$action = pn_requestFilter( "action", "/[A-Z_]+/i" );

$debug = pn_requestFilter( "debug", "/[01]/" );

$remoteIP = "";
if( isset( $_SERVER[ "REMOTE_ADDR" ] ) ) {
    $remoteIP = $_SERVER[ "REMOTE_ADDR" ];
    }



$requiredPages = array( "intro", "email_prompt", "pass_words_prompt", "login",
                        "main", "owned", "error", "matrix_dead",
                        "wipe_result", "builtIn", "spinUp" );


$replacableUserStrings = array( "%LAST_NAME%" => "fake_last_name",
                                "%CREDITS%" => "credits" );

$replacableSpecialStrings = array( "%AI_OWNED_LIST%" => "",
                                   "%AI_OWNED_LIST_AFTER%" => "",
                                   "%ERROR_MESSAGE%" => "" );




if( $action == "version" ) {
    global $pn_version;
    echo "$pn_version";
    }
else if( $action == "get_client_sequence_number" ) {
    pn_getClientSequenceNumber();
    }
else if( $action == "get_intro_text" ) {
    pn_echoPageText( "", "intro" );
    }
else if( $action == "get_email_prompt" ) {
    pn_echoPageText( "", "email_prompt" );
    }
else if( $action == "get_pass_words_prompt" ) {
    pn_echoPageText( "", "pass_words_prompt" );
    }
else if( $action == "login" ) {
    pn_clientLogin();
    }
else if( $action == "page" ) {
    pn_clientPage();
    }
else if( $action == "purchase_ai" ) {
    pn_purchaseAI();
    }
else if( $action == "talk_ai" ) {
    pn_talkAI();
    }
else if( $action == "show_log" ) {
    pn_showLog();
    }
else if( $action == "clear_log" ) {
    pn_clearLog();
    }
else if( $action == "add_user" ) {
    pn_addUser();
    }
else if( $action == "add_credits" ) {
    pn_addCredits();
    }
else if( $action == "show_data" ) {
    pn_showData();
    }
else if( $action == "show_detail" ) {
    pn_showDetail();
    }
else if( $action == "show_pages" ) {
    pn_showPages();
    }
else if( $action == "add_page" ) {
    pn_updatePage( true );
    }
else if( $action == "new_page" ) {
    pn_newPage();
    }
else if( $action == "edit_page" ) {
    pn_editPage();
    }
else if( $action == "update_page" ) {
    pn_updatePage( false );
    }
else if( $action == "delete_page" ) {
    pn_deletePage();
    }
else if( $action == "export_pages" ) {
    pn_exportPages();
    }
else if( $action == "export_users" ) {
    pn_exportUsers();
    }
else if( $action == "import_pages" ) {
    pn_importPages();
    }
else if( $action == "import_users" ) {
    pn_importUsers();
    }
else if( $action == "show_import_pages" ) {
    pn_showImportPages();
    }
else if( $action == "show_import_users" ) {
    pn_showImportUsers();
    }
else if( $action == "show_conversation" ) {
    pn_showConversation();
    }
else if( $action == "show_live_conversation" ) {
    pn_showLiveConversation();
    }
else if( $action == "delete_user" ) {
    pn_deleteUser();
    }
else if( $action == "toggle_conversation_logging" ) {
    pn_toggleConversationLogging();
    }
else if( $action == "purchase" ) {
    pn_purchase();
    }
else if( $action == "logout" ) {
    pn_logout();
    }
else if( $action == "pn_setup" ) {
    global $setup_header, $setup_footer;
    echo $setup_header; 

    echo "<H2>November Server Web-based Setup</H2>";

    echo "Creating tables:<BR>";

    echo "<CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=1>
          <TR><TD BGCOLOR=#000000>
          <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5>
          <TR><TD BGCOLOR=#FFFFFF>";

    pn_setupDatabase();

    echo "</TD></TR></TABLE></TD></TR></TABLE></CENTER><BR><BR>";
    
    echo $setup_footer;
    }
else if( preg_match( "/server\.php/", $_SERVER[ "SCRIPT_NAME" ] ) ) {
    // server.php has been called without an action parameter

    // the preg_match ensures that server.php was called directly and
    // not just included by another script
    
    // quick (and incomplete) test to see if we should show instructions
    global $tableNamePrefix;
    
    // check if our tables exist
    $exists =
        pn_doesTableExist( $tableNamePrefix . "server_globals" ) &&
        pn_doesTableExist( $tableNamePrefix . "random_nouns" ) &&
        pn_doesTableExist( $tableNamePrefix . "last_names" ) &&
        pn_doesTableExist( $tableNamePrefix . "users" ) &&
        pn_doesTableExist( $tableNamePrefix . "pages" ) &&
        pn_doesTableExist( $tableNamePrefix . "owned_ai" ) &&
        pn_doesTableExist( $tableNamePrefix . "conversation_logs" ) &&
        pn_doesTableExist( $tableNamePrefix . "log" );
    
        
    if( $exists  ) {
        echo "November Server database setup and ready";
        }
    else {
        // start the setup procedure

        global $setup_header, $setup_footer;
        echo $setup_header; 

        echo "<H2>November Server Web-based Setup</H2>";
    
        echo "November Server will walk you through a " .
            "brief setup process.<BR><BR>";
        
        echo "Step 1: ".
            "<A HREF=\"server.php?action=pn_setup\">".
            "create the database tables</A>";

        echo $setup_footer;
        }
    }



// done processing
// only function declarations below

pn_closeDatabase();








/**
 * Creates the database tables needed by seedBlogs.
 */
function pn_setupDatabase() {
    global $tableNamePrefix;

    $tableName = $tableNamePrefix . "log";
    if( ! pn_doesTableExist( $tableName ) ) {

        $query =
            "CREATE TABLE $tableName(" .
            "entry TEXT NOT NULL, ".
            "entry_time DATETIME NOT NULL, ".
            "index( entry_time ) );";

        $result = pn_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    $tableName = $tableNamePrefix . "server_globals";
    if( ! pn_doesTableExist( $tableName ) ) {

        // this table contains general info about the server
        $query =
            "CREATE TABLE $tableName(" .
            "last_keep_alive_time DATETIME NOT NULL );";

        $result = pn_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";

        $query = "INSERT INTO $tableName ".
            "SET last_keep_alive_time = CURRENT_TIMESTAMP;";
        pn_queryDatabase( $query );
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }

    

    // these words taken from a cognitive experiment database
    // http://www.datavis.ca/online/paivio/
    // Now moved here:
    // http://euclid.psych.yorku.ca/shiny/Paivio/

    $tableName = $tableNamePrefix . "random_nouns";
    if( ! pn_doesTableExist( $tableName ) ) {

        // a source list of character last names
        // cumulative count is number of people in 1993 population
        // who have this name or a more common name
        // less common names have higher cumulative counts
        $query =
            "CREATE TABLE $tableName( " .
            "id SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, ".
            "noun VARCHAR(14) NOT NULL, ".
            "UNIQUE KEY( noun ) );";

        $result = pn_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";


        if( $file = fopen( "randomNouns.txt", "r" ) ) {
            $firstLine = true;

            $query = "INSERT INTO $tableName (noun) VALUES ";
            /*
			( 'bird' ),
            ( 'monster' ),
            ( 'ability' );
            */

            while( !feof( $file ) ) {
                $noun = trim( fgets( $file) );
                
                if( ! $firstLine ) {
                    $query = $query . ",";
                    }
                
                $query = $query . " ( '$noun' )";
            
                $firstLine = false;
                }
            
            fclose( $file );

            $query = $query . ";";
            
            $result = pn_queryDatabase( $query );
            }
        }


    // these last names taken from nobel prizes in physics and chemistry
    // between 1970 and 1988
    // these API calls
    // http://api.nobelprize.org/v1/prize.json?
    //        category=physics&year=1970&yearTo=1988
    // http://api.nobelprize.org/v1/prize.json?
    //        category=chemistry&year=1970&yearTo=1988
    //
    // json processed with this command line:
    // cat chemistry.json | sed "s/surname\":/\n/g" |
    //     sed "s/,.*//" | sed "s/\"//g"
    
    $tableName = $tableNamePrefix . "last_names";
    if( ! pn_doesTableExist( $tableName ) ) {

        // a source list of character last names
        // cumulative count is number of people in 1993 population
        // who have this name or a more common name
        // less common names have higher cumulative counts
        $query =
            "CREATE TABLE $tableName( " .
            "id SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, ".
            "last_name VARCHAR(20) NOT NULL, ".
            "UNIQUE KEY( last_name ) );";

        $result = pn_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";


        if( $file = fopen( "lastNames.txt", "r" ) ) {
            $firstLine = true;

            $query = "INSERT INTO $tableName (last_name) VALUES ";
            /*
			( 'bird' ),
            ( 'monster' ),
            ( 'ability' );
            */

            while( !feof( $file ) ) {
                $last_name = trim( fgets( $file) );
                
                if( ! $firstLine ) {
                    $query = $query . ",";
                    }
                
                $query = $query . " ( '$last_name' )";
            
                $firstLine = false;
                }
            
            fclose( $file );

            $query = $query . ";";
            
            $result = pn_queryDatabase( $query );
            }
        }

    
    
    $tableName = $tableNamePrefix . "users";
    if( ! pn_doesTableExist( $tableName ) ) {

        $query =
            "CREATE TABLE $tableName(" .
            "id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT," .
            "email VARCHAR(254) NOT NULL," .
            "UNIQUE KEY( email ), ".
            // separated by spaces
            "pass_words VARCHAR(254) NOT NULL," .
            "fake_last_name VARCHAR(20) NOT NULL," .
            "credits int NOT NULL," .
            "current_page VARCHAR(254) NOT NULL," .
            // for use with client connections
            "client_sequence_number INT NOT NULL,".
            // track how many times they've typed exit from chat
            // stop showing the boilerplate help at beginning of chat
            // after they learn it
            "num_times_exit_used INT NOT NULL,".
            "conversations_logged TINYINT NOT NULL );";

        $result = pn_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }



    // pages in terminal user interface
    $tableName = $tableNamePrefix . "pages";
    if( ! pn_doesTableExist( $tableName ) ) {

        $query =
            "CREATE TABLE $tableName(" .
            "name VARCHAR(254) NOT NULL PRIMARY KEY,".
            "display_text TEXT NOT NULL,".
            "display_color VARCHAR(10) NOT NULL,".
            "prompt_color VARCHAR(10) NOT NULL,".
            "dest_names TEXT NOT NULL,".
            // these columns for AI pages
            // which must have "name" field that start with AI_
            "ai_name VARCHAR(30) NOT NULL,".
            "ai_cost int NOT NULL,".
            // what comes before AI's response when chatting with it
            // like "Computer: "
            "ai_response_label VARCHAR(30) NOT NULL,".
            // how many responses before complete
            // corruption
            "ai_longevity int NOT NULL,".
            // name of protocol used to get AI response
            "ai_protocol TEXT NOT NULL )";
        
        $result = pn_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    // owned AI pages per user
    $tableName = $tableNamePrefix . "owned_ai";
    if( ! pn_doesTableExist( $tableName ) ) {

        $query =
            "CREATE TABLE $tableName(" .
            "id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT," .
            "user_id INT UNSIGNED NOT NULL," .
            "page_name VARCHAR(254) NOT NULL,".
            "index( page_name ),".
            "ai_age INT UNSIGNED NOT NULL,".
            "conversation_buffer TEXT NOT NULL,".
            "conversation_log TEXT NOT NULL );";
        
        $result = pn_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    $tableName = $tableNamePrefix . "conversation_logs";
    if( ! pn_doesTableExist( $tableName ) ) {

        $query =
            "CREATE TABLE $tableName(" .
            "id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT," .
            "email VARCHAR(254) NOT NULL, ".
            "log_time DATETIME NOT NULL, ".
            "page_name VARCHAR(254) NOT NULL,".
            "conversation TEXT NOT NULL, ".
            "index( log_time ), index( email ) );";

        $result = pn_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }


    
    }



function pn_showLog() {
    pn_checkPassword( "show_log" );

    pn_showLinkHeader();

    $entriesPerPage = 1000;
    
    $skip = pn_requestFilter( "skip", "/\d+/", 0 );

    
    global $tableNamePrefix;


    // first, count results
    $query = "SELECT COUNT(*) FROM $tableNamePrefix"."log;";

    $result = pn_queryDatabase( $query );
    $totalEntries = pn_mysqli_result( $result, 0, 0 );


    
    $query = "SELECT entry, entry_time FROM $tableNamePrefix"."log ".
        "ORDER BY entry_time DESC LIMIT $skip, $entriesPerPage;";
    $result = pn_queryDatabase( $query );

    $numRows = mysqli_num_rows( $result );



    $startSkip = $skip + 1;
    
    $endSkip = $startSkip + $entriesPerPage - 1;

    if( $endSkip > $totalEntries ) {
        $endSkip = $totalEntries;
        }

    

    
    echo "$totalEntries Log entries".
        " (showing $startSkip - $endSkip):<br>\n";

    
    $nextSkip = $skip + $entriesPerPage;

    $prevSkip = $skip - $entriesPerPage;

    if( $skip > 0 && $prevSkip < 0 ) {
        $prevSkip = 0;
        }
    
    if( $prevSkip >= 0 ) {
        echo "[<a href=\"server.php?action=show_log" .
            "&skip=$prevSkip\">".
            "Previous Page</a>] ";
        }
    if( $nextSkip < $totalEntries ) {
        echo "[<a href=\"server.php?action=show_log" .
            "&skip=$nextSkip\">".
            "Next Page</a>]";
        }
    
        
    echo "<hr>";

        
    
    for( $i=0; $i<$numRows; $i++ ) {
        $time = pn_mysqli_result( $result, $i, "entry_time" );
        $entry = htmlspecialchars( pn_mysqli_result( $result, $i, "entry" ) );

        echo "<b>$time</b>:<br><pre>$entry</pre><hr>\n";
        }

    echo "<br><br><hr><a href=\"server.php?action=clear_log\">".
        "Clear log</a>";
    }



function pn_clearLog() {
    pn_checkPassword( "clear_log" );

    pn_showLinkHeader();

    global $tableNamePrefix;

    $query = "DELETE FROM $tableNamePrefix"."log;";
    $result = pn_queryDatabase( $query );
    
    if( $result ) {
        echo "Log cleared.";
        }
    else {
        echo "DELETE operation failed?";
        }
    }




function pn_addUser() {
    pn_checkPassword( "add_user" );

    pn_showLinkHeader();

    global $tableNamePrefix;
    
    $email = pn_getEmailParam();

    if( $email == "" ) {
        echo "Bad email.";
        return;
        }

    $credits = pn_requestFilter( "credits", "/[0-9]+/i", 0 );

    
    $pass_words = pn_generateRandomPasswordSequence( $email );
    $fake_last_name = pn_generateRandomLastName();
    

    $query = "INSERT INTO $tableNamePrefix"."users ".
        "SET email = '$email', pass_words = '$pass_words', ".
        "fake_last_name = '$fake_last_name', credits = '$credits', ".
        "current_page = '', client_sequence_number = 0, ".
        "num_times_exit_used = 0, conversations_logged = 0 ;";


    global $pn_mysqlLink;
    // run query directly, so we can catch error
    $result = mysqli_query( $pn_mysqlLink, $query );
    
    if( $result ) {
        echo "User created with passwords <b>$pass_words</b>";
        }
    else {
        echo "User creation failed (duplicate email?)";
        }
    }



function pn_parseNameParam() {
    return pn_requestFilter( "name", "/[A-Z0-9_]+/i", "" );
    }





function pn_updatePage( $inCreateNewOnly ) {
    pn_checkPassword( "update_page" );

    global $tableNamePrefix;

    $name = pn_parseNameParam();

    if( $name == "" ) {
        echo "Bad page name.";
        pn_showLinkHeader();
        return;
        }

    // no filtering
    $body = $_REQUEST[ "body" ];
    
    global $pn_mysqlLink;
    
    $slashedBody = pn_mysqlEscape( $body );


    $dest_names = pn_requestFilter( "dest_names", "/[A-Z0-9,_]+/i", "" );

    $display_color = strtoupper(
        pn_requestFilter( "display_color", "/#[A-F0-9]+/i", "#FFFFFF" ) );

    $prompt_color = strtoupper(
        pn_requestFilter( "prompt_color", "/#[A-F0-9]+/i", "#FFFFFF" ) );

    $ai_name = pn_requestFilter( "ai_name", "/[A-Z0-9\- ]+/i", "" );
    $ai_cost = pn_requestFilter( "ai_cost", "/[0-9]+/i", "0" );
    $ai_response_label =
        pn_requestFilter( "ai_response_label", "/[A-Z0-9\- :]+/i", "" );
    $ai_longevity = pn_requestFilter( "ai_longevity", "/[0-9]+/i", "0" );
    $ai_protocol = pn_requestFilter( "ai_protocol", "/[A-Z0-9\-_]+/i", "" );


    if( ! $inCreateNewOnly ) {
        // update it
        $query = "UPDATE $tableNamePrefix"."pages ".
            "SET display_text = '$slashedBody', ".
            "display_color = '$display_color',  ".
            "prompt_color = '$prompt_color', ".
            "ai_name = '$ai_name',".
            "ai_cost = '$ai_cost',".
            "ai_response_label = '$ai_response_label',".
            "ai_longevity = '$ai_longevity',".
            "ai_protocol = '$ai_protocol',".
            "dest_names = '$dest_names' WHERE name = '$name';";
        
        
        $result = pn_queryDatabase( $query );


        echo "Page <b>$name</b> updated.";
        }
    else {
        $query = "INSERT INTO $tableNamePrefix"."pages ".
            "SET name = '$name', display_text = '$slashedBody', ".
            "dest_names = '$dest_names', ".
            "display_color = '$display_color', ".
            "prompt_color = '$prompt_color',".
            "ai_name = '$ai_name',".
            "ai_cost = '$ai_cost',".
            "ai_response_label = '$ai_response_label',".
            "ai_longevity = '$ai_longevity',".
            "ai_protocol = '$ai_protocol';";

        
        global $pn_mysqlLink;
        // run query directly, so we can catch error
        $result = mysqli_query( $pn_mysqlLink, $query );
        
        if( $result ) {
            echo "Page <b>$name</b> created";
            }
        else {
            pn_showLinkHeader();
            echo "Page creation failed (duplicate page name?)";
            return;
            }
        }
    
    pn_editPage();
    }




function pn_deletePage() {
    pn_checkPassword( "delete_page" );

    pn_showLinkHeader();

    global $tableNamePrefix;
    
    $name = pn_parseNameParam();

    if( $name == "" ) {
        echo "Bad page name.";
        return;
        }
    $confirm = pn_requestFilter( "confirm", "/[0-1]/", "0" );

    if( $confirm == 0 ) {
        echo "Confirmation box not checked.";
        return;
        }

    $query = "DELETE FROM $tableNamePrefix"."pages ".
        "WHERE name = '$name';";


    $result = pn_queryDatabase( $query );


    echo "Page <b>$name</b> deleted.";
    }



function pn_deleteUser() {
    pn_checkPassword( "delete_user" );

    pn_showLinkHeader();

    global $tableNamePrefix;
    
    $email = pn_getEmailParam();

    if( $email == "" ) {
        echo "Bad email.";
        return;
        }
    $confirm = pn_requestFilter( "confirm", "/[0-1]/", "0" );

    if( $confirm == 0 ) {
        echo "Confirmation box not checked.";
        return;
        }

    $query = "DELETE FROM $tableNamePrefix"."users ".
        "WHERE email = '$email';";


    $result = pn_queryDatabase( $query );


    echo "User <b>$email</b> deleted.";
    }



function pn_toggleConversationLogging() {
    pn_checkPassword( "toggle_conversation_logging" );
    
    global $tableNamePrefix;

    $id = pn_requestFilter( "id", "/[0-9]+/i", -1 );

    $email = pn_getEmail( $id );
    
    $set = pn_requestFilter( "set", "/[0-1]/", "0" );

    pn_setUserField( $email, "conversations_logged", $set );

    pn_showDetail( false );
    }


















function pn_logout() {
    pn_checkReferrer();
    
    pn_clearPasswordCookie();

    echo "Logged out";
    }



function pn_showLinkHeader() {
    
    echo "<table width='100%' border=0><tr>".
        "<td>[<a href=\"server.php?action=show_data" .
        "\">Main</a>] [<a href=\"server.php?action=show_pages" .
        "\">Pages</a>]</td>".
        "<td align=right>[<a href=\"server.php?action=logout" .
        "\">Logout</a>]</td>".
        "</tr></table><br><br><br>";
    }



function pn_showData( $checkPassword = true ) {
    // these are global so they work in embeded function call below
    global $skip, $search, $order_by;

    if( $checkPassword ) {
        pn_checkPassword( "show_data" );
        }
    
    global $tableNamePrefix, $remoteIP;
    

    pn_showLinkHeader();



    $skip = pn_requestFilter( "skip", "/[0-9]+/", 0 );
    
    global $usersPerPage;
    
    $search = pn_requestFilter( "search", "/[A-Z0-9_@. \-]+/i" );

    $order_by = pn_requestFilter( "order_by", "/[A-Z_]+/i",
                                  "id" );
    
    $keywordClause = "";
    $searchDisplay = "";
    
    if( $search != "" ) {
        

        $keywordClause = "WHERE ( email LIKE '%$search%' " .
            "OR id LIKE '%$search%' ) ";

        $searchDisplay = " matching <b>$search</b>";
        }
    

    

    // first, count results
    $query = "SELECT COUNT(*) FROM $tableNamePrefix".
        "users $keywordClause;";

    $result = pn_queryDatabase( $query );
    $totalRecords = pn_mysqli_result( $result, 0, 0 );


    $orderDir = "DESC";

    if( $order_by == "email" ) {
        $orderDir = "ASC";
        }
    
             
    $query = "SELECT * ".
        "FROM $tableNamePrefix"."users $keywordClause".
        "ORDER BY $order_by $orderDir ".
        "LIMIT $skip, $usersPerPage;";
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    $startSkip = $skip + 1;
    
    $endSkip = $startSkip + $usersPerPage - 1;

    if( $endSkip > $totalRecords ) {
        $endSkip = $totalRecords;
        }



        // form for searching users and resetting tokens
?>
        <hr>
             <table border=0 width=100%><tr><td>
            <FORM ACTION="server.php" METHOD="post">
    <INPUT TYPE="hidden" NAME="action" VALUE="show_data">
    <INPUT TYPE="hidden" NAME="order_by" VALUE="<?php echo $order_by;?>">
    <INPUT TYPE="text" MAXLENGTH=40 SIZE=20 NAME="search"
             VALUE="<?php echo $search;?>">
    <INPUT TYPE="Submit" VALUE="Search">
    </FORM>
             </td>
             <td align=right>
             </td>
             </tr>
             </table>
        <hr>
<?php

    

    
    echo "$totalRecords user records". $searchDisplay .
        " (showing $startSkip - $endSkip):<br>\n";

    
    $nextSkip = $skip + $usersPerPage;

    $prevSkip = $skip - $usersPerPage;
    
    if( $prevSkip >= 0 ) {
        echo "[<a href=\"server.php?action=show_data" .
            "&skip=$prevSkip&search=$search&order_by=$order_by\">".
            "Previous Page</a>] ";
        }
    if( $nextSkip < $totalRecords ) {
        echo "[<a href=\"server.php?action=show_data" .
            "&skip=$nextSkip&search=$search&order_by=$order_by\">".
            "Next Page</a>]";
        }

    echo "<br><br>";
    
    echo "<table border=1 cellpadding=5>\n";

    function orderLink( $inOrderBy, $inLinkText ) {
        global $skip, $search, $order_by;
        if( $inOrderBy == $order_by ) {
            // already displaying this order, don't show link
            return "<b>$inLinkText</b>";
            }

        // else show a link to switch to this order
        return "<a href=\"server.php?action=show_data" .
            "&search=$search&skip=$skip&order_by=$inOrderBy\">$inLinkText</a>";
        }

    
    echo "<tr>\n";    
    echo "<tr><td>".orderLink( "id", "ID" )."</td>\n";
    echo "<td>".orderLink( "email", "Email" )."</td>\n";
    echo "<td>".orderLink( "pass_words", "Pass Words" )."</td>\n";
    echo "<td>".orderLink( "fake_last_name", "Fake Last Name" )."</td>\n";
    echo "<td>".orderLink( "credits", "Computing Credits" )."</td>\n";
    echo "<td>".orderLink( "current_page", "Current Page" )."</td>\n";
    echo "</tr>\n";


    for( $i=0; $i<$numRows; $i++ ) {
        $id = pn_mysqli_result( $result, $i, "id" );
        $email = pn_mysqli_result( $result, $i, "email" );
        $pass_words = pn_mysqli_result( $result, $i, "pass_words" );
        $fake_last_name = pn_mysqli_result( $result, $i, "fake_last_name" );
        $credits = pn_mysqli_result( $result, $i, "credits" );
        $current_page = pn_mysqli_result( $result, $i, "current_page" );

        $encodedEmail = urlencode( $email );

        
        echo "<tr>\n";

        echo "<td>$id</td>\n";
        echo "<td>".
            "<a href=\"server.php?action=show_detail&email=$encodedEmail\">".
            "$email</a></td>\n";
        echo "<td>$pass_words</td>\n";
        echo "<td>$fake_last_name</td>\n";
        echo "<td>$credits</td>\n";
        echo "<td>$current_page</td>\n";
        echo "</tr>\n";
        }
    echo "</table>";



    echo "<hr>";


    // form for force-creating a new user
?>
        <td>
        Create new User:<br>
            <FORM ACTION="server.php" METHOD="post">
    <INPUT TYPE="hidden" NAME="action" VALUE="add_user">
             Email:
    <INPUT TYPE="text" MAXLENGTH=80 SIZE=20 NAME="email"><br>
             Starting Computing Credits:
    <INPUT TYPE="text" MAXLENGTH=5 SIZE=5 NAME="credits" value="0"><br>
    <INPUT TYPE="Submit" VALUE="Create">
    </FORM>
        </td>
<?php


    
    echo "<hr><a href='server.php?action=export_users'>Export Users</a>";
    echo "<hr><a href='server.php?action=show_import_users'>Import Users</a>";

    echo "<hr>";
         
    echo "<a href=\"server.php?action=show_log\">".
        "Show log</a>";
    echo "<hr>";
    echo "Generated for $remoteIP\n";

    }








function pn_showDetail( $checkPassword = true ) {
    if( $checkPassword ) {
        pn_checkPassword( "show_detail" );
        }

    pn_showLinkHeader();
    
    
    global $tableNamePrefix;
    

    // two possible params... id or email

    $id = pn_requestFilter( "id", "/[0-9]+/i", -1 );
    $email = "";

    
    if( $id != -1 ) {
        $query = "SELECT email ".
            "FROM $tableNamePrefix"."users ".
            "WHERE id = '$id';";
        $result = pn_queryDatabase( $query );
        
        $email = pn_mysqli_result( $result, 0, "email" );
        }
    else {
        $email = pn_getEmailParam();
    
        $query = "SELECT id ".
            "FROM $tableNamePrefix"."users ".
            "WHERE email = '$email';";
        $result = pn_queryDatabase( $query );
        
        $id = pn_mysqli_result( $result, 0, "id" );
        }
    
    $query = "SELECT credits ".
            "FROM $tableNamePrefix"."users ".
            "WHERE id = '$id';";
    $result = pn_queryDatabase( $query );
    
    $credits = pn_mysqli_result( $result, 0, "credits" );
    
    
    echo "<center><table border=0><tr><td>";
    
    echo "<b>ID:</b> $id<br><br>";
    echo "<b>Email:</b> $email<br><br>";
    echo "<b>Computing Credits:</b> $credits<br><br>";
    echo "</td></tr></table>";

    // form for adding credits
?>
        <td>
        Add Computing Credits:<br>
            <FORM ACTION="server.php" METHOD="post">
    <INPUT TYPE="hidden" NAME="action" VALUE="add_credits">
    <INPUT TYPE="hidden" NAME="email" VALUE="<?php echo $email;?>">
    <INPUT TYPE="text" MAXLENGTH=5 SIZE=5 NAME="add" VALUE="0"><br>
    <INPUT TYPE="Submit" VALUE="Add">
    </FORM>
        </td>
<?php
             
             echo "<br>";
    
    if( pn_getUserConversationsLogged( $email ) ) {
        echo "Conversation logging is ON (turn ";
        echo "<a href='server.php?action=toggle_conversation_logging".
            "&id=$id&set=0'>".
            "OFF</a>)";
        }
    else {
        echo "Conversation logging is OFF (turn ";
        echo "<a href='server.php?action=toggle_conversation_logging".
            "&id=$id&set=1'>".
            "ON</a>)";
        }

             // form for deleting user
?>
        <hr>
        <FORM ACTION="server.php" METHOD="post">
        <INPUT TYPE="hidden" NAME="action" VALUE="delete_user">
        <INPUT TYPE="hidden" NAME="email" VALUE="<?php echo $email;?>">
    <INPUT TYPE="checkbox" NAME="confirm" VALUE=1> Confirm<br>      
    <INPUT TYPE="Submit" VALUE="Delete User">
    </FORM>
        <hr>
<?php

             
    $query = "SELECT id, page_name, ai_age, ".
             "length( conversation_buffer ) as buff_len, ".
             "length( conversation_log ) as log_len ".
             "FROM $tableNamePrefix"."owned_ai ".
             "WHERE user_id = '$id';";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows > 0 ) {
        echo "Owned AIs:<br><br>";
    
        for( $i=0; $i<$numRows; $i++ ) {
            $id = pn_mysqli_result( $result, $i, "id" );
            $page_name = pn_mysqli_result( $result, $i, "page_name" );
            $age = pn_mysqli_result( $result, $i, "ai_age" );
            $buff_len = pn_mysqli_result( $result, $i, "buff_len" );
            $log_len = pn_mysqli_result( $result, $i, "log_len" );
            
            echo "<a href='server.php?action=edit_page&name=$page_name'>$page_name</a> ".
                "($age) [buffer=$buff_len] ".
                "[<a href='server.php?action=show_live_conversation".
                "&id=$id'>log=$log_len</a>]<br>";
            }
        echo "<hr>";
        }

    $query = "SELECT id, page_name, log_time, length( conversation ) as log_len ".
             "FROM $tableNamePrefix"."conversation_logs ".
             "WHERE email = '$email';";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows > 0 ) {
        echo "Archived conversations:<br><br>";
    
        for( $i=0; $i<$numRows; $i++ ) {
            $id = pn_mysqli_result( $result, $i, "id" );
            $page_name = pn_mysqli_result( $result, $i, "page_name" );
            $time = pn_mysqli_result( $result, $i, "log_time" );
            $log_len = pn_mysqli_result( $result, $i, "log_len" );
            
            echo "$time <a href='server.php?action=show_conversation&id=$id'>$page_name</a> ".
                "($log_len)<br>";
            }
        echo "<hr>";
        }
    }



function pn_showConversation() {
    pn_checkPassword( "show_conversation" );
    pn_showLinkHeader();

    $id = pn_requestFilter( "id", "/[0-9]+/i", -1 );

    
    global $tableNamePrefix;
    $query = "SELECT conversation ".
             "FROM $tableNamePrefix"."conversation_logs ".
             "WHERE id = '$id';";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows > 0 ) {
        $c = pn_mysqli_result( $result, 0, "conversation" );

        echo "<pre style='white-space: pre-wrap;'>$c</pre><br>";
        }
    else {
        echo "Conversation not found for id = $id<br>";
        }
    
    }



function pn_showLiveConversation() {
    pn_checkPassword( "show_live_conversation" );
    pn_showLinkHeader();

    $id = pn_requestFilter( "id", "/[0-9]+/i", -1 );
    
    global $tableNamePrefix;
    $query = "SELECT conversation_log ".
             "FROM $tableNamePrefix"."owned_ai ".
             "WHERE id = '$id';";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows > 0 ) {
        $c = pn_mysqli_result( $result, 0, "conversation_log" );

        echo "<pre style='white-space: pre-wrap;'>$c</pre><br>";
        }
    else {
        echo "Conversation not found for id = $id<br>";
        }
    
    }


function pn_addCredits() {
    pn_checkPassword( "addCredits" );

    $add = pn_requestFilter( "add", "/\-?[0-9]+/i", 0 );

    if( $add == 0 ) {
        echo "Add failed<br>";
        }
    else {
        $email = pn_getEmailParam();

        global $tableNamePrefix;
        
        $query = "UPDATE $tableNamePrefix"."users ".
            "SET credits = credits + $add ".
            "WHERE email = '$email';";
        $result = pn_queryDatabase( $query );
        
        echo "Added $add credits for $email<br>";
        }
    
    pn_showDetail( false );
    }



function pn_getEmailParam() {
    $email = pn_requestFilter( "email",
                               "/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+/i", "" );
    return $email;
    }

    




function pn_showPageForm( $action, $name, $nameHidden, $body, $display_color,
                          $prompt_color, $dest_names,
                          $buttonName ) {

    $nameType = "text";
    if( $nameHidden ) {
        $nameType = "hidden";
        }

    $body = preg_replace( "/\r\n/", "&#13;", $body );

    $ai_name = "";
    $ai_cost = 0;
    $ai_response_label = "";
    $ai_longevity = 0;
    $ai_protocol = "";
    
    if( $name != "" ) {
        // existing page
        // pull AI values from table
        global $tableNamePrefix;
        
        $query = "SELECT ai_name, ai_cost, ai_response_label,".
            "ai_longevity, ai_protocol FROM $tableNamePrefix"."pages ".
            "WHERE name='$name';";
        
        $result = pn_queryDatabase( $query );
    
        $numRows = mysqli_num_rows( $result );
        
        if( $numRows == 1 ) {
        
            $ai_name = pn_mysqli_result( $result, 0, "ai_name" );
            $ai_cost = pn_mysqli_result( $result, 0, "ai_cost" );
            $ai_response_label =
                pn_mysqli_result( $result, 0, "ai_response_label" );
            $ai_longevity = pn_mysqli_result( $result, 0, "ai_longevity" );
            $ai_protocol = pn_mysqli_result( $result, 0, "ai_protocol" );
            }
        }
    
    
?>
    <FORM ACTION="server.php" METHOD="post">
        <INPUT TYPE="hidden" NAME="action" VALUE="<?php echo $action;?>">
        Name:
    <INPUT TYPE="<?php echo $nameType;?>" MAXLENGTH=80 SIZE=20 NAME="name"
        value='<?php echo $name;?>'><br>

    <textarea name="body" rows="10" cols="35" style="font-size: 18pt"><?php echo $body;?></textarea><br>
         Display Color:
    <INPUT TYPE="text" MAXLENGTH=10 SIZE=10 NAME="display_color"
        value='<?php echo $display_color;?>'>
         Prompt Color:
    <INPUT TYPE="text" MAXLENGTH=10 SIZE=10 NAME="prompt_color"
        value='<?php echo $prompt_color;?>'><br>
         Dest pages:
    <INPUT TYPE="text" MAXLENGTH=80 SIZE=40 NAME="dest_names"
        value='<?php echo $dest_names;?>'><br>
<table border=0 cellpadding=10><tr><td bgcolor="#eeeeee">
        AI Name:
    <INPUT TYPE="text" MAXLENGTH=30 SIZE=10 NAME="ai_name"
        value='<?php echo $ai_name;?>'>
         AI Cost:
    <INPUT TYPE="text" MAXLENGTH=10 SIZE=4 NAME="ai_cost"
        value='<?php echo $ai_cost;?>'><br>
         AI Response Label:
    <INPUT TYPE="text" MAXLENGTH=30 SIZE=10 NAME="ai_response_label"
        value='<?php echo $ai_response_label;?>'>
         AI Longevity:
    <INPUT TYPE="text" MAXLENGTH=10 SIZE=4 NAME="ai_longevity"
        value='<?php echo $ai_longevity;?>'><br>
        AI protocol:
    <INPUT TYPE="text" MAXLENGTH=30 SIZE=10 NAME="ai_protocol"
        value='<?php echo $ai_protocol;?>'><br>
</td></tr></table>               
    <INPUT TYPE="Submit" VALUE="<?php echo $buttonName;?>">
    </FORM>
<?php
     global $replacableUserStrings, $replacableSpecialStrings;
    
    if( count( $replacableUserStrings ) > 0 ||
        count( $replacableAIStrings ) >  0 ) {
        echo "<br>Variables: ";
        foreach( $replacableUserStrings as $s => $v ) {
            echo "$s ";
            }
        foreach( $replacableSpecialStrings as $s => $v ) {
            echo "$s ";
            }
        }
    }




function pn_showPages() {
    pn_checkPassword( "show_pages" );
    
    pn_showLinkHeader();


    // first, form for adding a new page

    echo "Create new Page:<br>";

    global $defaultPageTextColor, $defaultPagePromptColor;    
    
    pn_showPageForm( "add_page", "", false, "",
                     $defaultPageTextColor, $defaultPagePromptColor,
                     "", "Create" );

         
    
    global $tableNamePrefix;

    echo "<hr><br>Exising pages:<br><br>";
    
    
    $query = "SELECT name, dest_names ".
            "FROM $tableNamePrefix"."pages;";
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    global $requiredPages;

    $missingPages = $requiredPages;

    $linkedPages = array();
    
    for( $i=0; $i<$numRows; $i++ ) {
        $name = pn_mysqli_result( $result, $i, "name" );

        echo
        "<a href='server.php?action=edit_page&name=$name'>$name</a><br><br>";

        pn_arrayRemoveByValue( $missingPages, $name );

        $dest_names = pn_mysqli_result( $result, $i, "dest_names" );
         
        $destParts = preg_split( "/,/", $dest_names ); 

        foreach( $destParts as $p ) {
            if( $p != "" && array_search( $p, $linkedPages ) === FALSE ) {
                $linkedPages[] = $p;
                }
            }
        }

    $missingLinkedPages = $linkedPages;

    for( $i=0; $i<$numRows; $i++ ) {
        $name = pn_mysqli_result( $result, $i, "name" );

        pn_arrayRemoveByValue( $missingLinkedPages, $name );
        }

    // trigger dest names shouldn't be listed
    foreach( $missingLinkedPages as $name ) {
        if( preg_match( "/talk_AI/i", $name ) ||
            preg_match( "/purchase_AI/i", $name ) ) {
            pn_arrayRemoveByValue( $missingLinkedPages, $name );
            }        
        }

    
    if( count( $missingPages ) > 0 ) {
        echo "<hr><br>Missing required pages:<br><br>";

        foreach( $missingPages as $name ) {    
            echo
            "<a href='server.php?action=new_page&name=$name'>$name</a><br><br>";
            }
        }

    if( count( $missingLinkedPages ) > 0 ) {
        echo "<hr><br>Missing linked pages:<br><br>";

        foreach( $missingLinkedPages as $name ) {    
            echo
            "<a href='server.php?action=new_page&name=$name'>$name</a><br><br>";
            }
        }
    echo "<hr><a href='server.php?action=export_pages'>Export Pages</a>";
    echo "<hr><a href='server.php?action=show_import_pages'>Import Pages</a>";
    }



function pn_newPage() {
    pn_checkPassword( "new_page" );
    
    pn_showLinkHeader();

    $name = pn_parseNameParam();

    echo "Create new Page:<br>";

    global $defaultPageTextColor, $defaultPagePromptColor;
    
    
    pn_showPageForm( "add_page", "$name", false, "",
                     $defaultPageTextColor, $defaultPagePromptColor,
                     "", "Create" );
    }



function pn_editPage() {
    pn_checkPassword( "edit_page" );
    
    pn_showLinkHeader();
    
    
    global $tableNamePrefix;
    
    $name = pn_parseNameParam();


    
    $query = "SELECT display_text, display_color, prompt_color, dest_names ".
            "FROM $tableNamePrefix"."pages WHERE name='$name';";
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows == 1 ) {
        echo "Editing page <b>$name</b>:<br><br>";

        $dest_names = pn_mysqli_result( $result, 0, "dest_names" );
        

        pn_showPageForm( "update_page", "$name", true,
                         pn_mysqli_result( $result, 0, "display_text" ),
                         pn_mysqli_result( $result, 0, "display_color" ),
                         pn_mysqli_result( $result, 0, "prompt_color" ),
                         $dest_names,
                         "Update" );


        echo "<hr>Linked pages:<br><br>";
        $destParts = preg_split( "/,/", $dest_names );
        
        foreach( $destParts as $n ) {
            if( $n != "" &&
                ! preg_match( "/talk_AI/i", $n ) &&
                ! preg_match( "/purchase_AI/i", $n ) ) {
                
                if( pn_pageExists( $n ) ) {
                    
                    echo "<a href='server.php?".
                        "action=edit_page&name=$n'>$n</a><br><br>";
                    }
                else {
                    echo "<a href='server.php?".
                        "action=new_page&name=$n'>$n</a> [missing]<br><br>";
                    }
                }
            }

        echo "<hr>Pages that link here:<br><br>";
        $query = "SELECT name, dest_names ".
            "FROM $tableNamePrefix"."pages;";
        $result = pn_queryDatabase( $query );
        
        $numRows = mysqli_num_rows( $result );
        for( $i=0; $i<$numRows; $i++ ) {
            $otherName = pn_mysqli_result( $result, $i, "name" );
            $otherDest_names = pn_mysqli_result( $result, $i, "dest_names" );

            $dest = preg_split( "/,/", $otherDest_names );
            foreach( $dest as $d ) {
                if( $d == $name ) {
                    echo "<a href='server.php?".
                        "action=edit_page&name=$otherName'>".
                        "$otherName</a><br><br>";
                    }
                }
            }
        
                
?>
        <hr>
        <FORM ACTION="server.php" METHOD="post">
        <INPUT TYPE="hidden" NAME="action" VALUE="delete_page">
        <INPUT TYPE="hidden" NAME="name" VALUE="<?php echo $name;?>">
    <INPUT TYPE="checkbox" NAME="confirm" VALUE=1> Confirm<br>      
    <INPUT TYPE="Submit" VALUE="Delete Page">
    </FORM>
<?php
        
        }
    else {
        echo "Page not found";
        }
    }




function pn_pageExists( $name ) {
    global $tableNamePrefix;
    $query = "SELECT name ".
        "FROM $tableNamePrefix"."pages WHERE name='$name';";
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows == 1 ) {
        return true;
        }
    return false;
    }




function pn_exportPages() {
    pn_checkPassword( "export_pages" );

    pn_exportTable( "pages" );
    }

function pn_exportUsers() {
    pn_checkPassword( "export_users" );

    pn_exportTable( "users" );
    }



function pn_exportTable( $inTableName ) {

    global $tableNamePrefix;
    $query = "SELECT * ".
        "FROM $tableNamePrefix"."$inTableName;";
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );
    $numCols = mysqli_num_fields( $result );

    $out = "";
    
    for( $y=0; $y<$numRows; $y++ ) {
        for( $x=0; $x<$numCols; $x++ ) {
        
            $val = pn_mysqli_result( $result, $y, $x );

            $encVal = urlencode( $val );

            if( $x > 0 ) {
                $out = $out . "," . $encVal;
                }
            else {
                $out = $out . $encVal;
                }
            }
        if( $y != $numRows - 1 ) {
            // more coming
            $out = $out . "&\n";
            }
        }
    header('Content-Type: text/plain');
    echo $out;
    }

    

function pn_showImportPages() {
    pn_checkPassword( "show_import_pages" );
    
    pn_showLinkHeader();
?>
    Import pages:
        <FORM ACTION="server.php" METHOD="post">
        <INPUT TYPE="hidden" NAME="action" VALUE="import_pages">
    <textarea name="text" rows="20" cols="40"></textarea><br>
      
    <INPUT TYPE="Submit" VALUE="Import">
    </FORM>
<?php
    
    }


function pn_showImportUsers() {
    pn_checkPassword( "show_import_users" );
    
    pn_showLinkHeader();
?>
    Import pages:
        <FORM ACTION="server.php" METHOD="post">
        <INPUT TYPE="hidden" NAME="action" VALUE="import_users">
    <textarea name="text" rows="20" cols="40"></textarea><br>
      
    <INPUT TYPE="Submit" VALUE="Import">
    </FORM>
<?php
    
    }



function pn_mysqlEscape( $inString ) {
    global $pn_mysqlLink;
    return mysqli_real_escape_string( $pn_mysqlLink, $inString );
    }



function pn_importPages() {
    pn_checkPassword( "import_pages" );

    pn_importTable( "pages" );
    
    pn_showPages();
    }


function pn_importUsers() {
    pn_checkPassword( "import_users" );

    pn_importTable( "users" );
    
    pn_showData( false );
    }



function pn_importTable( $inTableName ) {
    
    global $tableNamePrefix;

    // no filtering
    $text = $_REQUEST[ "text" ];


    // remove all whitespace (various line ends)
    $text = preg_replace('/\s+/', '', $text );

    $lines = preg_split( "/&/", $text );

    $numImported = 0;
    
    foreach( $lines as $line ) {

        $parts = preg_split( "/,/", $line );

        $numParts = count( $parts );
        
        for( $i=0; $i < $numParts; $i++ ) {
            $parts[$i] = '"' . pn_mysqlEscape( urldecode( $parts[$i] ) ) . '"';
            }
        $valueLine = join( ",", $parts );
        
        $query = "REPLACE INTO $tableNamePrefix"."$inTableName ".
            "VALUES ( $valueLine );";
        pn_queryDatabase( $query );

        $numImported ++;
        }


    echo "Imported <b>$numImported</b> into table $inTableName<br>";
    }





function pn_echoPageText( $email, $inName ) {
    $text = pn_formatPageLines( $email, $inName );

    echo $text;
    }

    



function pn_getClientSequenceNumber() {
    global $tableNamePrefix;
    

    $email = pn_getEmailParam();

    if( $email == "" ) {
        $rawEmail = $_REQUEST[ "email" ];
        pn_log( "getClientSequenceNumber denied for bad email '$rawEmail'" );

        echo "DENIED";
        return;
        }
    
    
    $seq = pn_getClientSequenceNumberForEmail( $email );

    if( $seq == -1 ) {
        $rawEmail = $_REQUEST[ "email" ];
        pn_log( "getClientSequenceNumber not found for email '$rawEmail'" );

        echo "DENIED";
        return;
        }
    
    
    echo "$seq\n"."OK";
    }



// assumes already-filtered, valid email
// returns -1 if not found
function pn_getClientSequenceNumberForEmail( $inEmail ) {
    global $tableNamePrefix;
    
    $query = "SELECT client_sequence_number FROM $tableNamePrefix"."users ".
        "WHERE email = '$inEmail';";
    $result = pn_queryDatabase( $query );

    $numRows = mysqli_num_rows( $result );

    if( $numRows < 1 ) {
        return -1;
        }
    else {
        return pn_mysqli_result( $result, 0, "client_sequence_number" );
        }
    }



function pn_getPassWordsForEmail( $inEmail ) {
    global $tableNamePrefix;
    
    $query = "SELECT pass_words FROM $tableNamePrefix"."users ".
        "WHERE email = '$inEmail';";
    $result = pn_queryDatabase( $query );

    $numRows = mysqli_num_rows( $result );

    if( $numRows < 1 ) {
        return "";
        }
    else {
        return pn_mysqli_result( $result, 0, "pass_words" );
        }
    }




function pn_clientLogin() {
    $email = pn_checkAndUpdateClientSeqNumber();

    pn_standardResponseForPage( $email, "login" );
    }



function pn_clientPage() {
    $email = pn_checkAndUpdateClientSeqNumber();

    $pageName = pn_requestFilter( "carried_param", "/[A-Z0-9_]+/i", "" );

    $command = pn_requestFilter( "client_command", "/[0-9]+/i", "" );


    $user_id = pn_getUserID( $email );
    
    
    global $tableNamePrefix;
    
    $dest_names = "";
    
    $query = "SELECT dest_names ".
            "FROM $tableNamePrefix"."pages WHERE name='$pageName';";
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows == 1 ) {
        $dest_names = pn_mysqli_result( $result, 0, "dest_names" );
        }

    $destList = preg_split( "/,/", $dest_names );

    $newDestList = array();

    foreach( $destList as $n ) {
        if( $n == "talk_AI" ) {
            $query = "SELECT id from $tableNamePrefix"."owned_ai ".
                "WHERE user_id = '$user_id';";
            
            $result = pn_queryDatabase( $query );
    
            $numRows = mysqli_num_rows( $result );

            if( $numRows > 0 ) {
                
                for( $i=0; $i<$numRows; $i++ ) {
                    $id = pn_mysqli_result( $result, $i, "id" );

                    $newDestList[] = "talk_AI_$id";
                    }
                }
            }
        else {
            $newDestList[] = $n;
            }
        }

    $destList = $newDestList;
    

    
    if( count( $destList ) > 1 ) {
    
        if( $command != "" &&
            $command >= 1 && count( $destList ) > $command - 1 ) {
            
            $pickedName = $destList[ $command - 1 ];            

            if( preg_match( "/talk_AI_/", $pickedName ) ) {
                // special case
                // initiate talk

                pn_initiateTalkAI( $email, $pickedName );
                }
            else if( preg_match( "/purchase_AI/", $pickedName ) ) {
                // special case
                // show purchase confirmation page
                pn_showPurchaseConfirmation( $email, $pickedName );
                }
            else if( pn_pageExists( $pickedName ) ) {
                pn_standardResponseForPage( $email, $pickedName );
                }
            else {
                pn_standardBadChoiceForPage( $pageName, "PAGE NOT FOUND" );
                }    
            }
        else {
            pn_standardBadChoiceForPage( $pageName );
            }
        }
    else if( count( $destList ) == 1 ) {
        // only one option (probably on ENTER) so go there always
        pn_standardResponseForPage( $email, $destList[0] );
        }
    else {
        pn_log( "Page $pageName has no destinations specified, ".
                "$command chosen by user" );
        
        echo "DENIED";
        die();
        }
    
    
    }


function pn_getUserID( $email ) {
    return pn_getUserField( $email, "id", -1 );
    }



function pn_getEmail( $user_id ) {
    global $tableNamePrefix;
    
    
    $query = "SELECT email ".
            "FROM $tableNamePrefix"."users WHERE id='$user_id';";
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows == 1 ) {
        return pn_mysqli_result( $result, 0, "email" );
        }
    return "";
    }



function pn_getUserField( $email, $field_name, $defaultVal ) {
    global $tableNamePrefix;
    
    
    $query = "SELECT $field_name ".
            "FROM $tableNamePrefix"."users WHERE email='$email';";
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows == 1 ) {
        return pn_mysqli_result( $result, 0, "$field_name" );
        }
    return $defaultVal;
    }



function pn_setUserField( $email, $field_name, $inValue ) {
    global $tableNamePrefix;
    
    
    $query = "UPDATE ".
        "$tableNamePrefix"."users SET $field_name = '$inValue' ".
        "WHERE email='$email';";

    pn_queryDatabase( $query );
    }




function pn_getUserCredits( $email ) {
    return pn_getUserField( $email, "credits", 0 );
    }



function pn_spendUserCredits( $email, $credits ) {
    global $tableNamePrefix;
    
    
    $query = "UPDATE $tableNamePrefix"."users ".
        "SET credits = credits - $credits ".
        "WHERE email='$email';";
    $result = pn_queryDatabase( $query );
    }



function pn_getUserConversationsLogged( $email ) {
    return pn_getUserField( $email, "conversations_logged", 0 );
    }




function pn_getAIOwnedCount( $email, $aiPageName ) {
    $user_id = pn_getUserID( $email );

    global $tableNamePrefix;
    
    $query = "SELECT COUNT(*) FROM $tableNamePrefix"."owned_ai ".
        "WHERE user_id = '$user_id' AND page_name = '$aiPageName';";
    
    $result = pn_queryDatabase( $query );
    $ownedCount = pn_mysqli_result( $result, 0, 0 );

    return $ownedCount;
    }




function pn_getPromptColorForPage( $inPageName ) {
    global $defaultPagePromptColor;
    $prompt_color = $defaultPagePromptColor;

    global $tableNamePrefix;
    
    
    $query = "SELECT prompt_color ".
            "FROM $tableNamePrefix"."pages WHERE name='$inPageName';";
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows == 1 ) {
        $prompt_color = pn_mysqli_result( $result, 0, "prompt_color" );
        }
    return $prompt_color;
    }



function pn_standardHeaderForPage( $inPageName ) {

    // next action
    echo "page\n";
    // carried param
    echo "$inPageName\n";
    // no typed display prefix
    echo "{}\n";


    $prompt_color = pn_getPromptColorForPage( $inPageName );
    
    // use prompt color for what user types being added to bottom
    echo "$prompt_color\n";
    
    // don't clear
    echo "0\n";

    }



function pn_standardResponseForPage( $email, $inPageName ) {
    pn_standardHeaderForPage( $inPageName );
    pn_echoPageText( $email, $inPageName );
    }



function pn_standardBadChoiceForPage( $inPageName,
                                      $inMessage = "INVALID SELECTION" ) {
    pn_standardHeaderForPage( $inPageName );

    $prompt_color = pn_getPromptColorForPage( $inPageName );
    
    echo "$prompt_color\n";

    global $defaultPageCharMS;
    
    echo "[#FF0000] [$defaultPageCharMS] [0] [0] $inMessage";
    }




function pn_showPurchaseConfirmation( $email, $purchasePageName ) {

    $prefix = "purchase_";

    $aiPageName = substr( $purchasePageName, strlen( $prefix ) );
    
    
    // next action
    echo "purchase_ai\n";
    // carried param
    echo "$aiPageName\n";
    // no typed display prefix
    echo "{}\n";


    global $defaultPagePromptColor;
    
    $prompt_color = $defaultPagePromptColor;
    
    // use prompt color for what user types being added to bottom
    echo "$prompt_color\n";
    
    // don't clear
    echo "0\n";


    global $tableNamePrefix;
    
    $query = "SELECT * FROM $tableNamePrefix"."pages ".
        "WHERE name = '$aiPageName';";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );
    
    if( $numRows == 1 ) {
        $ai_cost = pn_mysqli_result( $result, 0, "ai_cost" );
        
        if( $ai_cost > pn_getUserCredits( $email ) ) {
            $pageText = "\n\n       INSUFFICIENT CREDITS!\n\n";
            $pageText = $pageText . "Press ENTER to go back.";
            }
        else {
            
            $pageText = "\n\n       CONFIRM OPERATION\n\n";

            $ai_name = pn_mysqli_result( $result, 0, "ai_name" );
            
            
            $pageText = $pageText . "About to spin up:  $ai_name\n";
            $pageText = $pageText . "Spin will spend:   $ai_cost credits\n";
            
            $pageText = $pageText . "You have:          %CREDITS% credits\n\n";

            $existCount = pn_getAIOwnedCount( $email, $aiPageName );

            if( $existCount > 0 ) {
                $instanceWord = "instances";
                if( $existCount == 1 ) {
                    $instanceWord = "instance";
                    }
                
                $pageText = $pageText .
                    "WARNING:  You already have $existCount $instanceWord of $ai_name spinning.\n\n";
                }
            
                
            
            $pageText = $pageText . "Type \"confirm\" to execute spin.\n\n";
            $pageText = $pageText . "Press ENTER to go back.\n";
            }
        }
    else {
        $pageText = "\n\n       MATRIX NOT FOUND!\n\n";
        $pageText = $pageText . "Press ENTER to go back.";
        }
    
    

    
    global $defaultPageCharMS, $defaultPageTextColor;
    
    $text = pn_formatTextAsLines( $email, $pageText, $defaultPageTextColor,
                                  $defaultPageCharMS,
                                  $prompt_color );
    echo $text;
    }


$lastErrorMessage = "";



function pn_showErrorPage( $email, $inMessage ) {
    global $lastErrorMessage;
    $lastErrorMessage = $inMessage;
    pn_log( "Showing error page for $email with '$lastErrorMessage'" );
    pn_standardResponseForPage( $email, "error" );
    }



function pn_purchaseAI() {
    $email = pn_checkAndUpdateClientSeqNumber();

    $aiPageName = pn_requestFilter( "carried_param", "/[A-Z0-9_]+/i", "" );

    $command = strtoupper(
        pn_requestFilter( "client_command", "/[A-Z]+/i", "" ) );


    if( $command != "CONFIRM" ) {        
        pn_standardResponseForPage( $email, "main" );
        return;
        }

    // they shouldn't be able to get here without enough credits
    // confirm page should bounce them
    // but check to make sure
    global $tableNamePrefix;
    
    $query = "SELECT * FROM $tableNamePrefix"."pages ".
        "WHERE name = '$aiPageName';";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );
    
    if( $numRows == 1 ) {
        $ai_cost = pn_mysqli_result( $result, 0, "ai_cost" );
        
        if( $ai_cost > pn_getUserCredits( $email ) ) {
            pn_showErrorPage( $email, "Not enough compute credits." );
            return;
            }
        else {
            pn_spendUserCredits( $email, $ai_cost );
            $user_id = pn_getUserID( $email );

            $query = "INSERT INTO $tableNamePrefix"."owned_ai ".
                "SET user_id = '$user_id',".
                "page_name = '$aiPageName',".
                "ai_age = '0',".
                "conversation_buffer = '',".
                "conversation_log = '';";
            
            pn_queryDatabase( $query );

            // show their owned list after purchase
            pn_standardResponseForPage( $email, "owned" );
            return;
            }
        }
    else {
        // ai_page not found?
        // should never happen
        // bounce them to MAIN
        pn_showErrorPage( $email, "Requested matrix not found." );
        return;
        }
    }



function pn_initiateTalkAI( $email, $pickedName ) {
    $prefix = "talk_AI_";

    $aiOwnedID = substr( $pickedName, strlen( $prefix ) );

    global $tableNamePrefix;
    
    $query = "SELECT page_name, conversation_buffer ".
        "FROM $tableNamePrefix"."owned_ai ".
        "WHERE id = '$aiOwnedID';";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );
    
    if( $numRows != 1 ) {
        // owned ai doesn't exist
        pn_showErrorPage( $email, "Requested matrix ($aiOwnedID) not found." );
        return;
        }
    
    $aiPageName = pn_mysqli_result( $result, 0, "page_name" );

    $conversation_buffer =
        pn_decryptBuffer(
            pn_mysqli_result( $result, 0, "conversation_buffer" ) );


    $query = "SELECT * FROM $tableNamePrefix"."pages ".
        "WHERE name = '$aiPageName';";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );
    
    if( $numRows != 1 ) {
        // underlying AI page doesn't exist?
        pn_showErrorPage( $email, "Requested matrix ($aiPageName) not found." );
        return;
        }
    
    $display_text =
        pn_mysqli_result( $result, 0, "display_text" );


    if( $conversation_buffer == "" ) {
        // empty so far
        // seed it with AI page text
        // but don't log it
        pn_addToConversationBuffer( $aiOwnedID, $display_text, false );
        }

    $ai_name = pn_mysqli_result( $result, 0, "ai_name" );

    $display_color = pn_mysqli_result( $result, 0, "display_color" );

    
    global $humanTypedPrefix;
    
    
    
    // next action
    echo "talk_ai\n";
    // carried param
    echo "$aiOwnedID\n";
    // Prefix what human types
    echo "{" . $humanTypedPrefix . "}\n";


    $prompt_color =
        pn_mysqli_result( $result, 0, "prompt_color" );
    
    // color for what user types being added to bottom with Human: prefix
    echo "$prompt_color\n";
    
    // DO clear
    echo "1\n";

    // use it for prompt too
    echo "$prompt_color\n";
    
    // no text lines... just a blank screen, waiting for them to type

    // actually, include some lines here explaining what is going on
    echo
    "\n[$display_color] [$defaultPageCharMS] [0] [0] ".
        "Matrix $ai_name initialized.";

    if( pn_getUserExitCount( $email ) < 2 ) {
        // show help to novice users
        echo
            "\n[$display_color] [$defaultPageCharMS] [0] [0] ".
            "Type   exit   to leave, or";
        echo
            "\n[$display_color] [$defaultPageCharMS] [0] [0] ".
            "       help   for more commands.";
        }
    
    echo
    "\n[$display_color] [$defaultPageCharMS] [0] [0] ".
        "Human types first:";
    }



function pn_getUserExitCount( $email ) {
    return pn_getUserField( $email, "num_times_exit_used", 0 );
    }



function pn_incrementUserExitCount( $email ) {
    global $tableNamePrefix;
    pn_queryDatabase( "UPDATE ".
                      "$tableNamePrefix"."users ".
                      "SET num_times_exit_used = num_times_exit_used + 1 ".
                      "WHERE email = '$email';" );
    }



function pn_isLineJunk( $inLine ) {
    if( strlen( count_chars( $inLine, 3 ) )  < 4 ) {
        return true;
        }
    return false;
    }




function pn_talkAI() {
    set_time_limit( 120 );
    
    $email = pn_checkAndUpdateClientSeqNumber();

    $aiOwnedID = pn_requestFilter( "carried_param", "/[0-9]+/i", "0" );

    global $tableNamePrefix;
    
    $query = "SELECT * FROM $tableNamePrefix"."owned_ai ".
        "WHERE id = '$aiOwnedID';";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );
    
    if( $numRows != 1 ) {
        // owned ai doesn't exist
        pn_showErrorPage( $email, "Requested matrix ($aiOwnedID) not found." );
        return;
        }
    
    $aiPageName = pn_mysqli_result( $result, 0, "page_name" );
    $ai_age = pn_mysqli_result( $result, 0, "ai_age" );
    
    

    $query = "SELECT * FROM $tableNamePrefix"."pages ".
        "WHERE name = '$aiPageName';";
    
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );
    
    if( $numRows != 1 ) {
        // underlying AI page doesn't exist?
        pn_showErrorPage( $email, "Requested matrix ($aiPageName) not found." );
        return;
        }

    $ai_response_label =
        pn_mysqli_result( $result, 0, "ai_response_label" );
    
    // no filtering, because we append this to a buffer
    // in a way that applies mysqlEscape later
    // let user type ANYTHING
    $clientCommand = $_REQUEST[ "client_command" ];


    // watch for special commands
    $special = strtolower( trim( $clientCommand ) );
    if( $special == "exit" ) {
        // exit back to owned page
        pn_incrementUserExitCount( $email );
        pn_standardResponseForPage( $email, "owned" );
        return;
        }
    else if( $special == "wipe" ) {
        pn_wipeConversationBuffer( $aiOwnedID );
        
        // exit back to wipe message page
        pn_standardResponseForPage( $email, "wipe_result" );
        return;
        }
    else if( $special == "help" ) {
        // exit to built-in commands page
        pn_standardResponseForPage( $email, "builtIn" );
        return;
        }
    
    
    
    global $humanTypedPrefix;
    
    $clientLine = "$humanTypedPrefix$clientCommand";

    // A space at the end of the prompt sent to the AI tends to produce
    // garbage completions.  Not sure why,
    // but we'll add the space back later, before presenting the results
    // to the user.
    $ai_response_label = trim( $ai_response_label );
    
    // append to buffer with blank lines between and prompt for ai
    $appendText = "\n\n$clientLine\n\n$ai_response_label";

    $newBuffer = pn_addToConversationBuffer( $aiOwnedID, $appendText );


    $ai_longevity = pn_mysqli_result( $result, 0, "ai_longevity" );
    $ai_protocol = pn_mysqli_result( $result, 0, "ai_protocol" );
    $prompt_color = pn_mysqli_result( $result, 0, "prompt_color" );
    $display_color = pn_mysqli_result( $result, 0, "display_color" );

    $corr = pn_getCorruptionFraction( $ai_age, $ai_longevity );

    $aiDone = false;

    if( $ai_age >= $ai_longevity ) {

        // delete the dead matrix

        // archive conversation first
        pn_archiveConversation( $aiOwnedID, "(MATRIX DEAD)" );
        
        $query = "DELETE FROM $tableNamePrefix"."owned_ai ".
            "WHERE id = '$aiOwnedID' ";
        pn_queryDatabase( $query );

        sleep( 3 );
        
        pn_standardResponseForPage( $email, "matrix_dead" );
        return;
        }

    
    $aiResponse = "";

    $responseCost = 0;

    $startTime = microtime( true );
    $timeoutCount = 0;
    
    while( ! $aiDone ) {

        $gennedLine = "";
        $tryCount = 0;
        
        while( pn_isLineJunk( $gennedLine ) ) {

            if( $tryCount > 5 ) {
                // spinning our wheels here... AI keeps generating
                // junk responses
                // must have backed ourselves into a corner in conversation

                if( $aiResponse == "" ) {
                    pn_log( "Stuck trying to generate first bit of response ".
                            "for buffer after $tryCount tries, ".
                            "wiping and starting over: ".
                            "'$newBuffer'" );
                    // probably need to start over, and clear older
                    // part of buffer
                    pn_wipeConversationBuffer( $aiOwnedID );

                    $newBuffer = pn_addToConversationBuffer( $aiOwnedID,
                                                             $appendText );
                    }
                else {
                    pn_log( "Stuck trying to generate continuation response ".
                            "for aiResponse after $tryCount tries, ".
                            "wiping response so far and starting over: ".
                            "'$aiResponse'" );
                    // we got stuck in the middle of a long, multi-part
                    // response, where the earlier parts were fine, but
                    // now we're looping on junk?

                    // just roll back entire long response, and try
                    // to start that part again, without throwing
                    // away whole buffer
                    
                    // trim response so far off end
                    $newBuffer = substr( $newBuffer,
                                         0, -strlen( $aiResponse ) );

                    // clear response so far
                    $aiResponse;
                    }
                $tryCount = 0;
                }
            
                
            // AI has generated repeating characters or nonsense
            // with no words.... like   ???   or _____  
            // or just an empty response.
            // try again!
            $gennedLine = "";
            
            $logJSON = false;
            if( $tryCount > 0 ) {
                // start logging json sent to AI server when
                // we are retrying, so that we can capture/reproduce what's
                // going on
                $logJSON = true;
                }

            // 8 second time-out
            $completion = pn_getAICompletion( $newBuffer, $ai_protocol,
                                              $logJSON, 8 );

            if( $completion == "UNKNOWN_PROTOCOL" ) {
                pn_showErrorPage( $email,
                                  "Protocol ($ai_protocol) not found." );
                return;
                }
            
            while( $completion == "FAILED" ) {
                $timeoutCount++;

                if( $timeoutCount > 1 ) {
                    // timed out twice
                    // ai back-end still spinning up
                    
                    // don't leave the user hanging here

                    // remove what was added to conversation buffer
                    pn_removeFromConvesationBuffer( $aiOwnedID, $appendText );
                    
                    
                    // explain situation
                    pn_standardResponseForPage( $email, "spinUp" );
                    return;
                    }
                
                sleep( 5 );
                // don't $logJSON after we get FAILED back
                $completion = pn_getAICompletion( $newBuffer, $ai_protocol );
                }

        
            $responseCost ++;
    
            // AI often continues conversation through multiple responses
            $gennedChatLines =
                preg_split(
                    '/$ai_response_label:|<\|endoftext\|>|'.
                    'Human:|Humans:|The Human:|Machine:/',
                    $completion );


            $gennedLine = "";
            
            $computerHasBeenCutOff = false;
            
            if( count( $gennedChatLines ) > 1 ) {
                $gennedLine = rtrim( $gennedChatLines[0] );

                // make sure first line doesn't contain multiple lines
                $gennedLine = pn_firstLineOnly( $gennedLine );

                $aiDone = true;
                }
            else {
                // only one line, without Computer or Human tags?
                
                // take it raw
                $gennedLine = rtrim( $completion );
                
                // only consider first line, if there's more than one
                $gennedLine = pn_firstLineOnly( $gennedLine );
                
                
                // watch out for it being cut-off...
                
                // does it end in proper ending punctiuation?

                $lastI = strlen( $gennedLine ) - 1;
                
                $lastChar = $gennedLine[ $lastI ];
                
                $aiDone = true;
                
                if( $lastChar != '.' &&
                    $lastChar != '!' &&
                    $lastChar != '?' &&
                    $lastChar != '"' ) {
                    
                    // cut off!
                    $aiDone = false;
                    }
                }
            
            
            if( pn_isLineJunk( $gennedLine ) ) {
                // log the retry
                pn_log( "Try $tryCount needs retry: ".
                        "Prompting AI with '$newBuffer', ".
                        "received completion '$completion'" );
                }
                
            
            $tryCount ++;
            }

        // if we got here, the AI generated at least a partial
        // response that is not just empty or repeating characters.
        
        // keep appending so that if we need to get more from AI
        // it can keep generating after what it has already generated
        $newBuffer = $newBuffer . $gennedLine;
        
        $aiResponse = $aiResponse . $gennedLine;
        }

    // response is complete and ready!


    $query = "UPDATE $tableNamePrefix"."owned_ai ".
        "SET ai_age = ai_age + $responseCost ".
        "WHERE id = '$aiOwnedID';";

    $result = pn_queryDatabase( $query );
    
    
    // clean up into a single line
    // this is probably not necessary (other code above probably limits
    // it to one line).
    $aiResponse = join( " ", preg_split( "/\n/", $aiResponse ) );

    // remove any non-ascii characters
    $aiResponse = preg_replace( '/[^\x20-\x7E]/', '', $aiResponse);

    // add a single space to front of response, to separate it from
    // the Computer: prompt (or whatever the prompt the AI uses).
    $aiResponse = " " . trim( $aiResponse );
    
    pn_addToConversationBuffer( $aiOwnedID, $aiResponse );

    // next action
    echo "talk_ai\n";
    // carried param
    echo "$aiOwnedID\n";
    // Prefix what human types
    echo "{" . $humanTypedPrefix . "}\n";
    
    // color for what user types being added to bottom with Human: prefix
    echo "$prompt_color\n";
    
    // DO NOT clear, mid conversation
    echo "0\n";

    // use it for prompt too
    echo "$prompt_color\n";

    global $defaultPageCharMS;

    $corrSkip = strlen( $ai_response_label );
    
    echo
    "\n[$display_color] [$defaultPageCharMS] [$corr] [$corrSkip] ".
        "$ai_response_label$aiResponse";


    $corrAfter = pn_getCorruptionFraction( $ai_age + $responseCost, $ai_longevity );

    if( $corr == 0 &&
        $corrAfter > 0 ) {
        // add a warning line, because corruption has just started.
        echo "\n[#FF0000] [$defaultPageCharMS] [0] [0] ".
            "CORRUPTION DETECTED - MATRIX DYING";
        }

    
    if( $timeoutCount > 0 ) {
        // log the retry total time
        $deltaTime = microtime( true ) - $startTime;
        
        pn_log( "talkAI response timed out $timeoutCount times and took $deltaTime total" );
        }
    
    }



function pn_isNotEmpty( $inString ) {
    if( $inString == "" ) {
        return false;
        }
    return true;
    }

function pn_firstLineOnly( $inString ) {
    $gennedLineParts = array_filter( explode( "\n", $inString ),
                                     "pn_isNotEmpty" );

    return $gennedLineParts[0];
    }



function pn_getCorruptionFraction( $ai_age, $ai_longevity ) {

    $ageLeft = $ai_longevity - $ai_age;
    
    if( $ageLeft < 10 ) {

        // 1/5 of characters corrupted by end of life
        return ( 10.0 - $ageLeft ) /  50.0;
        }
    else {
        return 0;
        }
    
    }




function pn_aiServerKeepAlive() {

    global $aiKeepAliveIntervalSeconds;

    global $tableNamePrefix;

    $query = "SELECT TIME_TO_SEC( ".
        "            TIMEDIFF( CURRENT_TIMESTAMP, ".
        "                      last_keep_alive_time ) ) as sec_since ".
        "FROM $tableNamePrefix"."server_globals;";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );
    
    if( $numRows < 1 ) {
        return;
        }
    
    $sec_since = pn_mysqli_result( $result, 0, "sec_since" );

    if( $sec_since > $aiKeepAliveIntervalSeconds ) {
        // specify a brief time-out here, just to cause it to spin up
        // without waiting for result.
        $startTime = microtime( true );
        
        pn_getAICompletion( "This is a test", "coreWeave", false, 0.1 );

        $deltaTime = microtime( true ) - $startTime;
        pn_log( "AI server keep-alive took $deltaTime seconds" );

        $query = "UPDATE $tableNamePrefix"."server_globals ".
            "SET last_keep_alive_time = CURRENT_TIMESTAMP;";
        
        pn_queryDatabase( $query );
        }
    }


function pn_registerAIUsed( $ai_protocol ) {
    global $tableNamePrefix;
        
    $query = "UPDATE $tableNamePrefix"."server_globals ".
        "SET last_keep_alive_time = CURRENT_TIMESTAMP;";
    
    pn_queryDatabase( $query );
    }




// returns "FAILED" if could not reach server
// returns "UNKNOWN_PROTOCOL" if could not reach server
function pn_getAICompletion( $prompt, $ai_protocol,
                             $logJSON=false, $timeout = 0 ) {

    if( $ai_protocol == "coreWeave" ) {
        pn_registerAIUsed( $ai_protocol );
        
        $jsonArray =
            array('instances' => array( $prompt ) );
        /*
          Example json:
          {
          "instances":  [ "\"Hey there,\" she said, \"" ]              
          }
        */

        $postBody = json_encode( $jsonArray );

        if( $logJSON ) {
            pn_log( "json encoding sent to AI:  $postBody" );
            }
        
        global $coreWeaveURL;
        
        $url = $coreWeaveURL;

        $httpArray = array(
            'header'  =>
            "Connection: close\r\n".
            "Content-type: application/json\r\n".
            "Content-Length: " . strlen($postBody) . "\r\n",
            'method'  => 'POST',
            'protocol_version' => 1.1,
            'content' => $postBody );
        if( $timeout != 0 ) {
            $httpArray[ "timeout" ] = $timeout;
            }
        
        $options = array( 'http' => $httpArray );
        $context  = stream_context_create( $options );
        $result = file_get_contents( $url, false, $context );

        $promptLen = strlen( $prompt );

        // debug printout
        //echo "<pre>chat plain:\n$prompt\n($promptLen long)\nURL $url\npost body=\n$postBody\nresult = $result</pre>";

        if( $result === FALSE ) {
            return "FAILED";
            }
        else {
        
            $a = json_decode( $result, true );
            $textGen = $a['predictions'][0];

            // coreweave transformer includes prompt in response
            $textGen = substr( $textGen, $promptLen );

            return $textGen;
            }
        }
    else {
        return "UNKNOWN_PROTOCOL";
        }
    }




// returns new buffer
function pn_addToConversationBuffer( $aiOwnedID, $inText, $inLog = true ) {
    global $tableNamePrefix;

    // enforce newline consistency
    // \r\n  becomes \n
    // html textarea, which we use to edit forms, inserts \r\n in display_text
    $inText = preg_replace( "/\r\n/", "\n", $inText );

    
    
    
    $query = "SELECT page_name, user_id, conversation_buffer ".
        "FROM $tableNamePrefix"."owned_ai ".
        "WHERE id = '$aiOwnedID';";
    
    $result = pn_queryDatabase( $query );
    
    $aiPageName = pn_mysqli_result( $result, 0, "page_name" );
    $user_id = pn_mysqli_result( $result, 0, "user_id" );
    $conversation_buffer =
        pn_decryptBuffer( 
            pn_mysqli_result( $result, 0, "conversation_buffer" ) );

    if( $inLog &&
        pn_getUserConversationsLogged( pn_getEmail( $user_id ) ) ) {

        $textAdded = pn_mysqlEscape( $inText );
        
        $query = "UPDATE $tableNamePrefix"."owned_ai ".
            "SET conversation_log = concat( conversation_log, '$textAdded' ) ".
            "WHERE id = '$aiOwnedID';";
        
        $result = pn_queryDatabase( $query );
        }


    $query = "SELECT display_text FROM $tableNamePrefix"."pages ".
        "WHERE name = '$aiPageName';";
    
    $result = pn_queryDatabase( $query );
    
    $display_text = pn_mysqli_result( $result, 0, "display_text" );

    global $aiBufferLimit;

    $newBuffer = $conversation_buffer . $inText;


    if( strlen( $newBuffer ) > $aiBufferLimit ) {
        // enforce newline consistency
        // \r\n  becomes \n
        // html textarea, which we use to edit forms, inserts \r\n in display_text
        $display_text = preg_replace( "/\r\n/", "\n", $display_text );
        
        // trim head off buffer, and stick display_text (initial prompt)
        // on there, to maintain some consistency over the long term
        $newBuffer = substr( $newBuffer,
                             -( 1000 - ( strlen( $display_text ) + 10 ) ) );

        // further trim so it starts with blank line, if possible
        // this will prevent conversational discontinuity between
        // the initial prompt and the trimmed conversation
        $newlinePos = strpos( $newBuffer, "\n\n" );

        if( $newlinePos !== FALSE ) {
            $newBuffer = substr( $newBuffer, $newlinePos );
            }
        else {
            // stick a blank line on there
            $newBuffer = "\n\n" . $newBuffer;
            }

        $newBuffer = $display_text . $newBuffer;
        }

    $toReturn = $newBuffer;
    

    // no need to escape string, base64 encoding makes it safe
    $newBuffer = pn_encryptBuffer( $newBuffer );

    
    $query = "UPDATE $tableNamePrefix"."owned_ai ".
        "SET conversation_buffer = '$newBuffer' ".
        "WHERE id = '$aiOwnedID';";

    $result = pn_queryDatabase( $query );


    return $toReturn;
    }



// removes string from end
// undoes las pn_addToConversationBuffer
function pn_removeFromConvesationBuffer( $aiOwnedID, $inText, $inLog = true ) {
    global $tableNamePrefix;

    // enforce newline consistency
    // \r\n  becomes \n
    // html textarea, which we use to edit forms, inserts \r\n in display_text
    $inText = preg_replace( "/\r\n/", "\n", $inText );

    
    
    $query = "SELECT user_id, conversation_buffer, conversation_log ".
        "FROM $tableNamePrefix"."owned_ai ".
        "WHERE id = '$aiOwnedID';";
    
    $result = pn_queryDatabase( $query );
    
    $user_id = pn_mysqli_result( $result, 0, "user_id" );
    $conversation_buffer =
        pn_decryptBuffer( 
            pn_mysqli_result( $result, 0, "conversation_buffer" ) );

    
    if( $inLog &&
        pn_getUserConversationsLogged( pn_getEmail( $user_id ) ) ) {

        $conversation_log = pn_mysqli_result( $result, 0, "conversation_log" );

        // trim chars off end
        $newLog = substr( $conversation_log,
                          0, -strlen( $inText ) );

        $newLog = pn_mysqlEscape( $newLog );
        
        $query = "UPDATE $tableNamePrefix"."owned_ai ".
            "SET conversation_log = '$newLog' ".
            "WHERE id = '$aiOwnedID';";
        
        $result = pn_queryDatabase( $query );
        }


    // trim chars off end
    $newBuffer = substr( $conversation_buffer,
                         0, -strlen( $inText ) );

    $toReturn = $newBuffer;

    

    // no need to escape string, base64 encoding makes it safe
    $newBuffer = pn_encryptBuffer( $newBuffer );

    
    $query = "UPDATE $tableNamePrefix"."owned_ai ".
        "SET conversation_buffer = '$newBuffer' ".
        "WHERE id = '$aiOwnedID';";

    $result = pn_queryDatabase( $query );


    return $toReturn;
    }



function pn_archiveConversation( $aiOwnedID, $inFinalStamp = "" ) {
    global $tableNamePrefix;
    
    $query = "SELECT page_name, user_id, conversation_log ".
        "FROM $tableNamePrefix"."owned_ai ".
        "WHERE id = '$aiOwnedID';";
    
    $result = pn_queryDatabase( $query );
    
    $aiPageName = pn_mysqli_result( $result, 0, "page_name" );
    
    $user_id = pn_mysqli_result( $result, 0, "user_id" );

    if( ! pn_getUserConversationsLogged( pn_getEmail( $user_id ) ) ) {
        // don't log, except for flagged users
        return;
        }
    
    
    $conversation_log = pn_mysqli_result( $result, 0, "conversation_log" );

    // stick final stamp, if any, on end
    $conversation_log = $conversation_log . "\n\n". $inFinalStamp;

    
    $conversation_log = pn_mysqlEscape( $conversation_log );
    

    $email = pn_getEmail( $user_id );
    
    $query = "INSERT INTO $tableNamePrefix"."conversation_logs ".
        "SET email = '$email', log_time = CURRENT_TIMESTAMP, page_name ='$aiPageName',".
        "conversation = '$conversation_log';";
    $result = pn_queryDatabase( $query );
    }



function pn_wipeConversationBuffer( $aiOwnedID ) {
    global $tableNamePrefix;

    $query = "SELECT page_name FROM $tableNamePrefix"."owned_ai ".
        "WHERE id = '$aiOwnedID';";
    
    $result = pn_queryDatabase( $query );
    
    $aiPageName = pn_mysqli_result( $result, 0, "page_name" );

    pn_archiveConversation( $aiOwnedID, "(BUFFER WIPED)" );
    

    $query = "SELECT display_text FROM $tableNamePrefix"."pages ".
        "WHERE name = '$aiPageName';";
    
    $result = pn_queryDatabase( $query );
    
    $display_text = pn_mysqli_result( $result, 0, "display_text" );    

    // enforce newline consistency
    // \r\n  becomes \n
    // html textarea, which we use to edit forms, inserts \r\n in display_text
    $display_text = preg_replace( "/\r\n/", "\n", $display_text );

    $display_text = pn_encryptBuffer( $display_text );

    // clear log here, since we saved it
    $query = "UPDATE $tableNamePrefix"."owned_ai ".
        "SET conversation_buffer = '$display_text', conversation_log = '' ".
        "WHERE id = '$aiOwnedID';";

    $result = pn_queryDatabase( $query );
    }






function pn_checkClientSeqHash( $email ) {
    global $sharedGameServerSecret;


    global $action;


    $sequence_number = pn_requestFilter( "sequence_number", "/[0-9]+/i", "0" );

    $hash_value = pn_requestFilter( "hash_value", "/[A-F0-9]+/i", "" );

    $hash_value = strtoupper( $hash_value );


    if( $email == "" ) {
        $rawEmail = $_REQUEST[ "email" ];

        pn_log( "checkClientSeqHash denied for bad email '$rawEmail'" );
        
        echo "DENIED";
        die();
        }
    
    $trueSeq = pn_getClientSequenceNumberForEmail( $email );

    if( $trueSeq > $sequence_number ) {
        pn_log( "checkClientSeqHash denied for stale sequence number ".
                "($email submitted $sequence_number trueSeq=$trueSeq" );

        echo "DENIED";
        die();
        }

    $correct = false;

    $pass_words = pn_getPassWordsForEmail( $email );

    $computedHashValue =
        strtoupper( pn_hmac_sha1( $pass_words, $sequence_number ) );

    
    
    if( $computedHashValue != $hash_value ) {
        pn_log( "checkClientSeqHash denied, hash check failed" );

        echo "DENIED";
        die();
        }

    
    return $trueSeq;
    }





// returns validated email
function pn_checkAndUpdateClientSeqNumber() {
    global $tableNamePrefix;

    $email = pn_getEmailParam();

    $trueSeq = pn_checkClientSeqHash( $email );

    // true client calls keep AI server alive
    pn_aiServerKeepAlive();
    
    // no locking is done here, because action is asynchronous anyway

    if( $trueSeq == -1 ) {
        // no record exists
        pn_log( "checkAndUpdateClientSeqNumber denied, ".
                "no record found for $email" );

        echo "DENIED";
        die();
        }
    else {
        $query = "UPDATE $tableNamePrefix". "users SET " .
            "client_sequence_number = client_sequence_number + 1 ".
            "WHERE email = '$email';";
        pn_queryDatabase( $query );
        }
    
    return $email;
    }




// convert a binary string into an ascii "1001011"-style string
function pn_getBinaryDigitsString( $inBinaryString ) {
    $binaryDigits = str_split( $inBinaryString );

    // string of 0s and 1s
    $binString = "";
    
    foreach( $binaryDigits as $digit ) {
        $binDigitString = decbin( ord( $digit ) );

        // pad with 0s
        $binDigitString =
            substr( "00000000", 0, 8 - strlen( $binDigitString ) ) .
            $binDigitString;

        $binString = $binString . $binDigitString;
        }

    // now have full string of 0s and 1s for $inBinaryString
    return $binString;
    } 



function pn_getSecureRandomBoundedInt( $inMaxVal, $inSecret ) {
    $bitsNeeded = ceil( log( $inMaxVal, 2 ) );

    $intVal = $inMaxVal + 1;

    while( $intVal > $inMaxVal ) {

        // get enough digits to generate an int from it
        $binString = "";
        while( strlen( $binString ) < $bitsNeeded ) {
            $randVal = rand();
            
            $hash_bin =
                pn_hmac_sha1_raw( $inSecret,
                                  uniqid( "$randVal", true ) );

            $binString = $binString . pn_getBinaryDigitsString( $hash_bin );
            }
        $binString = substr( $binString, 0, $bitsNeeded );
        $intVal = bindec( $binString );
        }
    
    return $intVal;
    }




function pn_generateRandomPasswordSequence( $email ) {

    global $tableNamePrefix;

    $name = "";

    $numberOfWords = 4;

    $words = array();

    global $passWordSelectionSecret;

    $query = "SELECT COUNT(*) from $tableNamePrefix"."random_nouns;";
    $result = pn_queryDatabase( $query );
    $totalNouns = pn_mysqli_result( $result, 0, 0 );

    
    while( count( $words ) < $numberOfWords ) {
                    
        $pick = pn_getSecureRandomBoundedInt(
            $totalNouns - 1,
            $email . $passWordSelectionSecret );
        
        $query = "SELECT noun from $tableNamePrefix"."random_nouns ".
            "limit $pick, 1;";
        $result = pn_queryDatabase( $query );
        $noun = pn_mysqli_result( $result, 0, 0 );

        $words[] = $noun;
        }

    
    return join( " ", $words );
    }




function pn_generateRandomLastName() {
    global $tableNamePrefix;


    $name = "";

    $numberOfWords = 4;

    $query =
        "SELECT last_name FROM $tableNamePrefix"."last_names ".
        "       ORDER BY RAND() LIMIT 1;";
    
    $result = pn_queryDatabase( $query );

    $name = pn_mysqli_result( $result, 0, 0 );
    
    return $name;
    }







function pn_replaceVarsInLine( $email, $inLine ) {
    if( $email == "" ) {
        // no user specified, can't find replacement values anyway
        return $inLine;
        }
    
    if( substr_count( $inLine, "%" ) == 0 ) {
        // no vars in this line
        return $inLine;
        }

    global $tableNamePrefix;
    
    $query = "SELECT * from $tableNamePrefix"."users ".
        "WHERE email = '$email';";

    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows != 1 ) {
        // user record not found
        return $inLine;
        }

    global $replacableUserStrings;
    
    foreach( $replacableUserStrings as $v => $c ) {

        $cValue =  pn_mysqli_result( $result, 0, "$c" );

        $inLine = preg_replace( "/$v/", $cValue, $inLine );
        }
    
    if( substr_count( $inLine, "%" ) == 0 ) {
        // no vars left in this line
        return $inLine;
        }
    //return $inLine;
    
    $user_id =  pn_mysqli_result( $result, 0, "id" );

    
    // else replace AI vars
    $query = "SELECT * from $tableNamePrefix"."owned_ai as owned_ai ".
        "INNER JOIN $tableNamePrefix"."pages AS pages ".
        "ON owned_ai.page_name = pages.name ".
        "WHERE owned_ai.user_id = '$user_id';";

    $after = 1;
    $listText = "";
    
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows > 0 ) {
        $after = $numRows + 1;

        for( $i=0; $i<$numRows; $i++ ) {
            $age = pn_mysqli_result( $result, $i, "ai_age" );
            $ai_name = pn_mysqli_result( $result, $i, "ai_name" );
            $ai_longevity = pn_mysqli_result( $result, $i, "ai_longevity" );

            $fractionLeft = 1.0 - $age / $ai_longevity;
            $percentLeft = round( $fractionLeft * 100 );
            $menuNumber = $i + 1;

            $listText = $listText . " $menuNumber. $ai_name ($percentLeft%)\n";
            }
        }

    
    $inLine = preg_replace( "/%AI_OWNED_LIST%/", $listText, $inLine );    
    $inLine = preg_replace( "/%AI_OWNED_LIST_AFTER%/", $after, $inLine );    

    global $lastErrorMessage;
    $inLine = preg_replace( "/%ERROR_MESSAGE%/",
                            $lastErrorMessage, $inLine );    
        
    return $inLine;
    }




// includes prompt_color as first line
function pn_formatPageLines( $email, $inPageName ) {
    global $tableNamePrefix, $defaultPageCharMS;
    
    $query = "SELECT display_text, prompt_color, display_color ".
            "FROM $tableNamePrefix"."pages WHERE name='$inPageName';";
    $result = pn_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    if( $numRows == 1 ) {

        $display_text = pn_mysqli_result( $result, 0, "display_text" );
        
        $display_color = pn_mysqli_result( $result, 0, "display_color" );

        $prompt_color = pn_mysqli_result( $result, 0, "prompt_color" );

        $lines = preg_split( "/\n/", $display_text );

        $result = pn_formatTextAsLines( $email, $display_text, $display_color,
                                        $defaultPageCharMS, $prompt_color );
        
        return $result;
        }
    else {
        return "";
        }    
    }



function pn_formatTextAsLines( $email, $text, $display_color, $char_ms,
                               $prompt_color ) {
    $lines = preg_split( "/\n/", $text );
    
    $result = "$prompt_color";
    
    foreach( $lines as $line ) {
        $line = pn_replaceVarsInLine( $email, $line );

        // it might come out of the replacement process as multiple lines
        $subLines = preg_split( "/\n/", $line );

        foreach( $subLines as $s ) {
            $result = $result .
                "\n[$display_color] [$char_ms] [0] [0] $s";
            }
        }
        
    return $result;
    }



// returns base64 ciphertext
function pn_encryptBuffer( $inText ) {
    global $bufferEncryptionSecret;

    $key = pn_hmac_sha1_raw( $bufferEncryptionSecret,
                             $bufferEncryptionSecret );
    
    $ivlen = openssl_cipher_iv_length( $cipher="AES-128-CBC" );
    $iv = openssl_random_pseudo_bytes( $ivlen );
    $ciphertext_raw = openssl_encrypt( $inText, $cipher, $key,
                                       $options=OPENSSL_RAW_DATA, $iv );

    $ciphertext = base64_encode( $iv . $ciphertext_raw );
    return $ciphertext;
    }



function pn_decryptBuffer( $inBase64CipherText ) {
    global $bufferEncryptionSecret;

    $key = pn_hmac_sha1_raw( $bufferEncryptionSecret,
                             $bufferEncryptionSecret );
    
    $c = base64_decode( $inBase64CipherText );
    $ivlen = openssl_cipher_iv_length( $cipher="AES-128-CBC" );
    $iv = substr( $c, 0, $ivlen );
    $ciphertext_raw = substr( $c, $ivlen );
    $original_plaintext = openssl_decrypt( $ciphertext_raw, $cipher, $key,
                                           $options=OPENSSL_RAW_DATA, $iv );
    return $original_plaintext;
    }



function getHTTPHeaders() {
    $headers = [];
    foreach( $_SERVER as $name => $value ) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(
                 str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
    return $headers;
    }



function pn_purchase() {
    $headerArray = getHTTPHeaders();

    $logAll = false;

    $email = "";
    $credits = 0;
    $paymentSource = "";

    // FastSpring provides user with a response
    $print_response = "";
    
    
    if( array_key_exists( "Stripe-Signature", $headerArray ) ) {
        $paymentSource = "Stripe";
        
        // a payment from Stripe

        $jsonBody = file_get_contents( 'php://input' );
        
        global $stripeWebhookSecret;

        $sig = $headerArray[ "Stripe-Signature" ];
        $sigParts = preg_split( "/,/", $sig );

        $badSig = true;
        
        if( count( $sigParts ) == 2 ) {
            $timeParts = preg_split( "/=/", $sigParts[0] );
            $valParts = preg_split( "/=/", $sigParts[1] );

            if( $valParts[0] == "v1" ) {
                $signed_payload = $timeParts[1] . "." . $jsonBody;

                $hmac = pn_hmac_sha256( $stripeWebhookSecret, $signed_payload );

                if( strtolower( $hmac ) == strtolower( $valParts[1] ) ) {
                    $badSig = false;
                    }
                }
            }

        
        if( $badSig ) {
            pn_log( "Bad signature from Stripe" );
            $logAll = true;
            }
        else {
            // good signature
            $a = json_decode( $jsonBody, true );
            $cents = $a['data']['object']['amount'];

            global $creditsPerPenny;

            $credits = $cents * $creditsPerPenny;
            $email = $a['data']['object']['billing_details']['email'];

            if( $credits == 0 ) {
                pn_log( "Stripe order specifies no credits?" );
                $logAll = true;
                }
            }
        }
    else {
        // a payment from FastSpring
        $paymentSource = "FastSpring";
        
        global $fastSpringPrivateKey, $fastSpringTagCreditMap;

        
        // security protocol has changed
        // (ticketServer code is actually out of date now
        ksort( $_REQUEST );
        $hashparam = 'security_request_hash';
        $data = '';
        foreach( $_REQUEST as $key => $val ) {
            if( $key != $hashparam ) {
                $data .= $val;
                }
            }

        if( md5( $data . $fastSpringPrivateKey ) != $_REQUEST[$hashparam] ) {
            pn_log( "FastSpring sale security check failed, from $remoteIP" );
        
            $logAll = true;
            }
        else {
            $print_response = "(Check email for details)";
            
            $email = pn_requestFilter( "email",
                                       "/[A-Z0-9._%+-]+@[A-Z0-9.-]+/i",
                                       "" );
            
            $tags = pn_requestFilter( "tags", "/[A-Z0-9_,-]+/i", "" );
            
            $separateTags = preg_split( "/,/", $tags );
            
            $credits = 0;
            
            // find a tag specifying how many credits they bought
            foreach( $separateTags as $t ) {
                if( array_key_exists( $t,
                                      $fastSpringTagCreditMap  ) ) {
                    
                    $credits = $fastSpringTagCreditMap[ $t ];
                    }
                }

            if( $credits == 0 ) {
                pn_log( "FastSpring order specifies no credits? ".
                        "(tags = $tags)" );
                $logAll = true;
                }
            }
        }

    if( $email != "" && $credits != 0 ) {
                    
        $bonus = 0;
        
        if( $credits > 0 ) {
            global $creditPurchaseBonusMap;
            
            if( array_key_exists( $credits, $creditPurchaseBonusMap ) ) {
                $bonus = $creditPurchaseBonusMap[ $credits ];
                }
            }
        
        $totalNewCredits = $credits + $bonus;
        
        if( $totalNewCredits > 0 ) {
            global $tableNamePrefix;

            $id = pn_getUserID( $email );
            
            if( $id == -1 ) {
                // don't check for duplicate email here
                // we already know that email doesn't exist
                
                $pass_words = pn_generateRandomPasswordSequence( $email );
                $fake_last_name = pn_generateRandomLastName();
                
                $query =
                    "INSERT INTO $tableNamePrefix"."users ".
                    "SET email = '$email', pass_words = '$pass_words', ".
                    "fake_last_name = '$fake_last_name', ".
                    "credits = '$totalNewCredits', ".
                    "current_page = '', client_sequence_number = 0, ".
                    "num_times_exit_used = 0, conversations_logged = 0 ;";
                
                $result = pn_queryDatabase( $query );
                
                pn_log( "Creating user account for $email with ".
                        "$totalNewCredits starting credits ".
                        "(payment source: $paymentSource)" );
                }
            else {
                $query = "UPDATE $tableNamePrefix"."users ".
                    "SET credits = credits + $totalNewCredits ".
                    "WHERE id = '$id';";
                $result = pn_queryDatabase( $query );

                pn_log( "Adding $totalNewCredits credits for $email ".
                        "(payment source: $paymentSource)" );
                }


            $query = "SELECT credits, pass_words ".
                "FROM $tableNamePrefix"."users ".
                "WHERE email = '$email';";

            $result = pn_queryDatabase( $query );
            $numRows = mysqli_num_rows( $result );

            if( $numRows == 1 ) {

                $credits = pn_mysqli_result( $result, 0, "credits" );
                $pass_words = pn_mysqli_result( $result, 0, "pass_words" );
        
                // send them an email

                global $terminalURL;
                
                pn_mail( $email,
                         "PROJECT DECEMBER account details",
                         "Your PROJECT DECEMBER account now has $credits ".
                         "Compute Credits.\n\n".
                         "You can log in with these details:\n\n".
                         "email:  $email\n".
                         "secret words:  $pass_words\n\n\n".
                         "Go here to log in:\n".
                         "$terminalURL\n\n\n".
                         "Enjoy!\n".
                         "Jason\n\n",
                         true );
                }
            }
        }

    if( $print_response != "" ) {
        echo $print_response;
        }
    
    
    if( $logAll ) {
        // log for debugging
        $headerString = "";
        foreach( $headerArray as $name => $value ) {
            $headerString .= "\n$name: $value";
            }
        
        $entityBody = file_get_contents( 'php://input' );

        $url = $_SERVER['REQUEST_URI'];
        
        pn_log( "Purchase through url $url with headers ".
                "$headerString and body: $entityBody" );
        }
    }


    







$pn_mysqlLink;


// general-purpose functions down here, many copied from seedBlogs

/**
 * Connects to the database according to the database variables.
 */  
function pn_connectToDatabase() {
    global $databaseServer,
        $databaseUsername, $databasePassword, $databaseName,
        $pn_mysqlLink;
    
    
    $pn_mysqlLink =
        mysqli_connect( $databaseServer, $databaseUsername, $databasePassword )
        or pn_operationError( "Could not connect to database server: " .
                              mysqli_error( $pn_mysqlLink ) );
    
    mysqli_select_db( $pn_mysqlLink, $databaseName )
        or pn_operationError( "Could not select $databaseName database: " .
                              mysqli_error( $pn_mysqlLink ) );
    }


 
/**
 * Closes the database connection.
 */
function pn_closeDatabase() {
    global $pn_mysqlLink;
    
    mysqli_close( $pn_mysqlLink );
    }


/**
 * Returns human-readable summary of a timespan.
 * Examples:  10.5 hours
 *            34 minutes
 *            45 seconds
 */
function pn_secondsToTimeSummary( $inSeconds ) {
    if( $inSeconds < 120 ) {
        if( $inSeconds == 1 ) {
            return "$inSeconds second";
            }
        return "$inSeconds seconds";
        }
    else if( $inSeconds < 3600 ) {
        $min = number_format( $inSeconds / 60, 0 );
        return "$min minutes";
        }
    else {
        $hours = number_format( $inSeconds / 3600, 1 );
        return "$hours hours";
        }
    }


/**
 * Returns human-readable summary of a distance back in time.
 * Examples:  10 hours
 *            34 minutes
 *            45 seconds
 *            19 days
 *            3 months
 *            2.5 years
 */
function pn_secondsToAgeSummary( $inSeconds ) {
    if( $inSeconds < 120 ) {
        if( $inSeconds == 1 ) {
            return "$inSeconds second";
            }
        return "$inSeconds seconds";
        }
    else if( $inSeconds < 3600 * 2 ) {
        $min = number_format( $inSeconds / 60, 0 );
        return "$min minutes";
        }
    else if( $inSeconds < 24 * 3600 * 2 ) {
        $hours = number_format( $inSeconds / 3600, 0 );
        return "$hours hours";
        }
    else if( $inSeconds < 24 * 3600 * 60 ) {
        $days = number_format( $inSeconds / ( 3600 * 24 ), 0 );
        return "$days days";
        }
    else if( $inSeconds < 24 * 3600 * 365 * 2 ) {
        // average number of days per month
        // based on 400 year calendar cycle
        // we skip a leap year every 100 years unless the year is divisible by 4
        $months = number_format( $inSeconds / ( 3600 * 24 * 30.436875 ), 0 );
        return "$months months";
        }
    else {
        // same logic behind computing average length of a year
        $years = number_format( $inSeconds / ( 3600 * 24 * 365.2425 ), 1 );
        return "$years years";
        }
    }



/**
 * Queries the database, and dies with an error message on failure.
 *
 * @param $inQueryString the SQL query string.
 *
 * @return a result handle that can be passed to other mysql functions.
 */
function pn_queryDatabase( $inQueryString ) {
    global $pn_mysqlLink;
    
    if( gettype( $pn_mysqlLink ) != "resource" ) {
        // not a valid mysql link?
        pn_connectToDatabase();
        }
    
    $result = mysqli_query( $pn_mysqlLink, $inQueryString );
    
    if( $result == FALSE ) {

        $errorNumber = mysqli_errno( $pn_mysqlLink );
        
        // server lost or gone?
        if( $errorNumber == 2006 ||
            $errorNumber == 2013 ||
            // access denied?
            $errorNumber == 1044 ||
            $errorNumber == 1045 ||
            // no db selected?
            $errorNumber == 1046 ) {

            // connect again?
            pn_closeDatabase();
            pn_connectToDatabase();

            $result = mysqli_query( $pn_mysqlLink, $inQueryString )
                or pn_operationError(
                    "Database query failed:<BR>$inQueryString<BR><BR>" .
                    mysqli_error( $pn_mysqlLink ) );
            }
        else {
            // some other error (we're still connected, so we can
            // add log messages to database
            pn_fatalError( "Database query failed:<BR>$inQueryString<BR><BR>" .
                           mysqli_error( $pn_mysqlLink ) );
            }
        }

    return $result;
    }



/**
 * Replacement for the old mysql_result function.
 */
function pn_mysqli_result( $result, $number, $field=0 ) {
    mysqli_data_seek( $result, $number );
    $row = mysqli_fetch_array( $result );
    return $row[ $field ];
    }



/**
 * Checks whether a table exists in the currently-connected database.
 *
 * @param $inTableName the name of the table to look for.
 *
 * @return 1 if the table exists, or 0 if not.
 */
function pn_doesTableExist( $inTableName ) {
    // check if our table exists
    $tableExists = 0;
    
    $query = "SHOW TABLES";
    $result = pn_queryDatabase( $query );

    $numRows = mysqli_num_rows( $result );


    for( $i=0; $i<$numRows && ! $tableExists; $i++ ) {

        $tableName = pn_mysqli_result( $result, $i, 0 );
        
        if( $tableName == $inTableName ) {
            $tableExists = 1;
            }
        }
    return $tableExists;
    }



function pn_log( $message ) {
    global $enableLog, $tableNamePrefix, $pn_mysqlLink;

    if( $enableLog ) {
        $slashedMessage = mysqli_real_escape_string( $pn_mysqlLink, $message );
    
        $query = "INSERT INTO $tableNamePrefix"."log VALUES ( " .
            "'$slashedMessage', CURRENT_TIMESTAMP );";
        $result = pn_queryDatabase( $query );
        }
    }



/**
 * Displays the error page and dies.
 *
 * @param $message the error message to display on the error page.
 */
function pn_fatalError( $message ) {
    //global $errorMessage;

    // set the variable that is displayed inside error.php
    //$errorMessage = $message;
    
    //include_once( "error.php" );

    // for now, just print error message
    $logMessage = "Fatal error:  $message";
    
    echo( $logMessage );

    pn_log( $logMessage );
    
    die();
    }



/**
 * Displays the operation error message and dies.
 *
 * @param $message the error message to display.
 */
function pn_operationError( $message ) {
    
    // for now, just print error message
    echo( "ERROR:  $message" );
    die();
    }


/**
 * Recursively applies the addslashes function to arrays of arrays.
 * This effectively forces magic_quote escaping behavior, eliminating
 * a slew of possible database security issues. 
 *
 * @inValue the value or array to addslashes to.
 *
 * @return the value or array with slashes added.
 */
function pn_addslashes_deep( $inValue ) {
    return
        ( is_array( $inValue )
          ? array_map( 'pn_addslashes_deep', $inValue )
          : addslashes( $inValue ) );
    }



/**
 * Recursively applies the stripslashes function to arrays of arrays.
 * This effectively disables magic_quote escaping behavior. 
 *
 * @inValue the value or array to stripslashes from.
 *
 * @return the value or array with slashes removed.
 */
function pn_stripslashes_deep( $inValue ) {
    return
        ( is_array( $inValue )
          ? array_map( 'pn_stripslashes_deep', $inValue )
          : stripslashes( $inValue ) );
    }



/**
 * Filters a $_REQUEST variable using a regex match.
 *
 * Returns "" (or specified default value) if there is no match.
 */
function pn_requestFilter( $inRequestVariable, $inRegex, $inDefault = "" ) {
    if( ! isset( $_REQUEST[ $inRequestVariable ] ) ) {
        return $inDefault;
        }

    return pn_filter( $_REQUEST[ $inRequestVariable ], $inRegex, $inDefault );
    }


/**
 * Filters a value  using a regex match.
 *
 * Returns "" (or specified default value) if there is no match.
 */
function pn_filter( $inValue, $inRegex, $inDefault = "" ) {
    
    $numMatches = preg_match( $inRegex,
                              $inValue, $matches );

    if( $numMatches != 1 ) {
        return $inDefault;
        }
        
    return $matches[0];
    }



// this function checks the password directly from a request variable
// or via hash from a cookie.
//
// It then sets a new cookie for the next request.
//
// This avoids storing the password itself in the cookie, so a stale cookie
// (cached by a browser) can't be used to figure out the password and log in
// later. 
function pn_checkPassword( $inFunctionName ) {
    $password = "";
    $password_hash = "";

    $badCookie = false;
    
    
    global $accessPasswords, $tableNamePrefix, $remoteIP, $enableYubikey,
        $passwordHashingPepper;

    $cookieName = $tableNamePrefix . "cookie_password_hash";

    $passwordSent = false;
    
    if( isset( $_REQUEST[ "passwordHMAC" ] ) ) {
        $passwordSent = true;

        // already hashed client-side on login form
        // hash again, because hash client sends us is not stored in
        // our settings file
        $password = pn_hmac_sha1( $passwordHashingPepper,
                                  $_REQUEST[ "passwordHMAC" ] );
        
        
        // generate a new hash cookie from this password
        $newSalt = time();
        $newHash = md5( $newSalt . $password );
        
        $password_hash = $newSalt . "_" . $newHash;
        }
    else if( isset( $_COOKIE[ $cookieName ] ) ) {
        pn_checkReferrer();
        $password_hash = $_COOKIE[ $cookieName ];
        
        // check that it's a good hash
        
        $hashParts = preg_split( "/_/", $password_hash );

        // default, to show in log message on failure
        // gets replaced if cookie contains a good hash
        $password = "(bad cookie:  $password_hash)";

        $badCookie = true;
        
        if( count( $hashParts ) == 2 ) {
            
            $salt = $hashParts[0];
            $hash = $hashParts[1];

            foreach( $accessPasswords as $truePassword ) {    
                $trueHash = md5( $salt . $truePassword );
            
                if( $trueHash == $hash ) {
                    $password = $truePassword;
                    $badCookie = false;
                    }
                }
            
            }
        }
    else {
        // no request variable, no cookie
        // cookie probably expired
        $badCookie = true;
        $password_hash = "(no cookie.  expired?)";
        }
    
        
    
    if( ! in_array( $password, $accessPasswords ) ) {

        if( ! $badCookie ) {
            
            echo "Incorrect password.";

            pn_log( "Failed $inFunctionName access with password:  ".
                    "$password" );
            }
        else {
            echo "Session expired.";
                
            pn_log( "Failed $inFunctionName access with bad cookie:  ".
                    "$password_hash" );
            }
        
        die();
        }
    else {
        
        if( $passwordSent && $enableYubikey ) {
            global $yubikeyIDs, $yubicoClientID, $yubicoSecretKey,
                $passwordHashingPepper;
            
            $yubikey = $_REQUEST[ "yubikey" ];

            $index = array_search( $password, $accessPasswords );
            $yubikeyIDList = preg_split( "/:/", $yubikeyIDs[ $index ] );

            $providedID = substr( $yubikey, 0, 12 );

            if( ! in_array( $providedID, $yubikeyIDList ) ) {
                echo "Provided Yubikey does not match ID for this password.";
                die();
                }
            
            
            $nonce = pn_hmac_sha1( $passwordHashingPepper, uniqid() );
            
            $callURL =
                "https://api2.yubico.com/wsapi/2.0/verify?id=$yubicoClientID".
                "&otp=$yubikey&nonce=$nonce";
            
            $result = trim( file_get_contents( $callURL ) );

            $resultLines = preg_split( "/\s+/", $result );

            sort( $resultLines );

            $resultPairs = array();

            $messageToSignParts = array();
            
            foreach( $resultLines as $line ) {
                // careful here, because = is used in base-64 encoding
                // replace first = in a line (the key/value separator)
                // with #
                
                $lineToParse = preg_replace( '/=/', '#', $line, 1 );

                // now split on # instead of =
                $parts = preg_split( "/#/", $lineToParse );

                $resultPairs[$parts[0]] = $parts[1];

                if( $parts[0] != "h" ) {
                    // include all but signature in message to sign
                    $messageToSignParts[] = $line;
                    }
                }
            $messageToSign = implode( "&", $messageToSignParts );

            $trueSig =
                base64_encode(
                    hash_hmac( 'sha1',
                               $messageToSign,
                               // need to pass in raw key
                               base64_decode( $yubicoSecretKey ),
                               true) );
            
            if( $trueSig != $resultPairs["h"] ) {
                echo "Yubikey authentication failed.<br>";
                echo "Bad signature from authentication server<br>";
                die();
                }

            $status = $resultPairs["status"];
            if( $status != "OK" ) {
                echo "Yubikey authentication failed: $status";
                die();
                }

            }
        
        // set cookie again, renewing it, expires in 24 hours
        $expireTime = time() + 60 * 60 * 24;
    
        setcookie( $cookieName, $password_hash, $expireTime, "/" );
        }
    }
 



function pn_clearPasswordCookie() {
    global $tableNamePrefix;

    $cookieName = $tableNamePrefix . "cookie_password_hash";

    // expire 24 hours ago (to avoid timezone issues)
    $expireTime = time() - 60 * 60 * 24;

    setcookie( $cookieName, "", $expireTime, "/" );
    }
 
 







function pn_hmac_sha1( $inKey, $inData ) {
    return hash_hmac( "sha1", 
                      $inData, $inKey );
    } 

 
function pn_hmac_sha1_raw( $inKey, $inData ) {
    return hash_hmac( "sha1", 
                      $inData, $inKey, true );
    } 



function pn_hmac_sha256( $inKey, $inData ) {
    return hash_hmac( "sha256", 
                      $inData, $inKey );
    } 

 
 
 
 
// decodes a ASCII hex string into an array of 0s and 1s 
function pn_hexDecodeToBitString( $inHexString ) {
    $digits = str_split( $inHexString );

    $bitString = "";

    foreach( $digits as $digit ) {
        $index = hexdec( $digit );

        $binDigitString = decbin( $index );

        // pad with 0s
        $binDigitString =
            substr( "0000", 0, 4 - strlen( $binDigitString ) ) .
            $binDigitString;

        $bitString = $bitString . $binDigitString;
        }

    return $bitString;
    }
 


function pn_arrayRemoveByValue( &$inArray, $inValue ) {
    if( ( $key = array_search( $inValue, $inArray ) ) !== false ) {
        unset( $inArray[$key] );
        }
    }





function pn_mail( $inEmail,
                  $inSubject,
                  $inBody,
                  // true for transactional emails that should use
                  // a different SMTP
                  $inTrans = false ) {
    
    global $useSMTP, $siteEmailAddress, $siteEmailDomain;

    if( $useSMTP ) {
        require_once "Mail.php";

        global $smtpHost, $smtpPort, $smtpUsername, $smtpPassword;

        $messageID = "<" . uniqid() . "@$siteEmailDomain>";
        
        $headers = array( 'From' => $siteEmailAddress,
                          'To' => $inEmail,
                          'Subject' => $inSubject,
                          'Date' => date( "r" ),
                          'Message-Id' => $messageID );
        $smtp;

        if( $inTrans ) {
            global $smtpHostTrans, $smtpPortTrans,
                $smtpUsernameTrans, $smtpPasswordTrans;

            $smtp = Mail::factory( 'smtp',
                                   array ( 'host' => $smtpHostTrans,
                                           'port' => $smtpPortTrans,
                                           'auth' => true,
                                           'username' => $smtpUsernameTrans,
                                           'password' => $smtpPasswordTrans ) );
            }
        else {
            $smtp = Mail::factory( 'smtp',
                                   array ( 'host' => $smtpHost,
                                           'port' => $smtpPort,
                                           'auth' => true,
                                           'username' => $smtpUsername,
                                           'password' => $smtpPassword ) );
            }
        

        $mail = $smtp->send( $inEmail, $headers, $inBody );


        if( PEAR::isError( $mail ) ) {
            pn_log( "Email send failed:  " .
                    $mail->getMessage() );
            return false;
            }
        else {
            return true;
            }
        }
    else {
        // raw sendmail
        $mailHeaders = "From: $siteEmailAddress";
        
        return mail( $inEmail,
                     $inSubject,
                     $inBody,
                     $mailHeaders );
        }
    }


 
?>
