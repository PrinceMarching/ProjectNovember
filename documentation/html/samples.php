<?php

include_once( "head.php" );


/**
 * Filters a $_REQUEST variable using a regex match.
 *
 * Returns "" (or specified default value) if there is no match.
 */
function pnc_requestFilter( $inRequestVariable, $inRegex, $inDefault = "" ) {
    if( ! isset( $_REQUEST[ $inRequestVariable ] ) ) {
        return $inDefault;
        }

    return pnc_filter( $_REQUEST[ $inRequestVariable ], $inRegex, $inDefault );
    }


/**
 * Filters a value  using a regex match.
 *
 * Returns "" (or specified default value) if there is no match.
 */
function pnc_filter( $inValue, $inRegex, $inDefault = "" ) {
    
    $numMatches = preg_match( $inRegex,
                              $inValue, $matches );

    if( $numMatches != 1 ) {
        return $inDefault;
        }
        
    return $matches[0];
    }

$page = pnc_requestFilter( "page", "/[0-9]+/i", "1" );



if( $page == 1 ) {
    $fileName = "romanceNovels.jpg";
    }
else if( $page == 2 ) {
    $fileName = "sample1.jpg";
    }
else if( $page == 3 ) {
    $fileName = "laurenConvo.jpg";
    }
else if( $page == 4 ) {
    $fileName = "sample3.jpg";
    }
else if( $page == 5 ) {
    $fileName = "sample4.jpg";
    }
else if( $page == 6 ) {
    $fileName = "sample5.jpg";
    }
else if( $page == 7 ) {
    $fileName = "sample6.jpg";
    }

$fileName = "samples/$fileName";

$size = getimagesize( $fileName );

$w = 0;
$h = 0;

if( $size ) {
    $w = $size[0];
    $h = $size[1];
    }

?>


<center>

<table width=300 border=0 cellspacing=0 cellpadding=0>
<tr>
    <td align=left>
<?php

if( $page > 1 ) {
    $prevPage = $page - 1;
    
    echo "[<a href='samples.php?page=$prevPage'>previous</a>]";
    }

echo "</td><td align=right>";

if( $page < 6 ) {
    $nextPage = $page + 1;
    
    echo "[<a href='samples.php?page=$nextPage'>next</a>]";
    }

?>
</td></tr></table>
<br>


<?php

if( $page == 2 ) {
?>

<table border=0 cellspacing=30>
<tr>
<td align=center valign=top>Part 1:<img src="samples/sample1.jpg" width=640 height=1120 border=0></td>
<td align=center valign=top>Part 2:<img src="samples/sample2.jpg" width=640 height=1000 border=0></td>
</tr>
</table>

<?php
    }
else {
    echo "<img src='$fileName' width=$w height=$h border=0>";
    }
?>



</center>

</body>

</html>
