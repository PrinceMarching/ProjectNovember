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



$requiredPages = array( "intro", "email_prompt", "pass_words_prompt", "login" );


$replacableStrings = array( "%LAST_NAME%" => "fake_last_name",
                            "%CREDITS%" => "credits" );




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
else if( $action == "delete_user" ) {
    pn_deleteUser();
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
        pn_doesTableExist( $tableNamePrefix . "random_nouns" ) &&
        pn_doesTableExist( $tableNamePrefix . "last_names" ) &&
        pn_doesTableExist( $tableNamePrefix . "users" ) &&
        pn_doesTableExist( $tableNamePrefix . "pages" ) &&
        pn_doesTableExist( $tableNamePrefix . "owned_ai" ) &&
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

        // this table contains general info about the server
        // use INNODB engine so table can be locked
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
            "conversation_buffer TEXT NOT NULL,".
            // for use with client connections
            "client_sequence_number INT NOT NULL );";

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
            "ai_age INT UNSIGNED NOT NULL );";
        
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
        "current_page = '';";


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
    $body = $body = $_REQUEST[ "body" ];
    
    global $pn_mysqlLink;
    
    $slashedBody = mysqli_real_escape_string( $pn_mysqlLink, $body );


    $dest_names = pn_requestFilter( "dest_names", "/[A-Z0-9,]+/i", "" );

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


             // form for deleting user
?>
        <hr>
        <FORM ACTION="server.php" METHOD="post">
        <INPUT TYPE="hidden" NAME="action" VALUE="delete_user">
        <INPUT TYPE="hidden" NAME="email" VALUE="<?php echo $email;?>">
    <INPUT TYPE="checkbox" NAME="confirm" VALUE=1> Confirm<br>      
    <INPUT TYPE="Submit" VALUE="Delete User">
    </FORM>
<?php

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

    global $replacableStrings;
    
    if( count( $replacableStrings ) > 0 ) {
        echo "<br>Variables: ";
        foreach( $replacableStrings as $s => $v ) {
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
            if( array_search( $p, $linkedPages ) === FALSE ) {
                $linkedPages[] = $p;
                }
            }
        }

    $missingLinkedPages = $linkedPages;

    for( $i=0; $i<$numRows; $i++ ) {
        $name = pn_mysqli_result( $result, $i, "name" );

        pn_arrayRemoveByValue( $missingLinkedPages, $name );
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
            if( $n != "" ) {
                
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

    if( count( $destList ) > 1 ) {
    
        if( $command != "" &&
            $command >= 1 && count( $destList ) > $command - 1 ) {
            
            $pickedName = $destList[ $command - 1 ];            

            if( pn_pageExists( $pickedName ) ) {
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



function pn_getPromptColorForPage( $inPageName ) {
    global $defaultPagePromptColor;
    $prompt_color = $defaultPagePromptColor;

    global $tableNamePrefix;
    
    
    $query = "SELECT prompt_color ".
            "FROM $tableNamePrefix"."pages WHERE name='$name';";
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


    $prompt_color = pn_getPromptColorForPage();
    
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

    $prompt_color = pn_getPromptColorForPage();
    
    echo "$prompt_color\n";

    global $defaultPageCharMS;
    
    echo "[#FF0000] [$defaultPageCharMS] [0] [0] $inMessage";
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

    global $replacableStrings;
    
    foreach( $replacableStrings as $v => $c ) {

        $cValue =  pn_mysqli_result( $result, 0, "$c" );

        $inLine = preg_replace( "/$v/", $cValue, $inLine );
        }
    
    
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

        $result = "$prompt_color";

        foreach( $lines as $line ) {
            $line = pn_replaceVarsInLine( $email, $line );
            
            $result = $result .
                "\n[$display_color] [$defaultPageCharMS] [0] [0] $line";
            }
        
        return $result;
        }
    else {
        return "";
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


 
?>
