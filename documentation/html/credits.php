<?php include_once( "head.php" );?>

<script src="https://js.stripe.com/v3/"></script>
<script src="stripeCheckout.js"></script>

<?php
    $referrer = "";
    
    if( isset( $_SERVER['HTTP_REFERER'] ) ) {
        // pass it through without a regex filter
        // because we can't control its safety in the end anyway
        // (user can just edit URL sent to FastSpring).
        $referrer = urlencode( $_SERVER['HTTP_REFERER'] );
        }


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

$numCredits = pnc_requestFilter( "num_credits", "/[0-9]+/i", "400" );
$email = pnc_requestFilter( "email",
                            "/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+/i", "" );

// default, 400 credits
$priceID = "price_1HFpXiLc2fQJ3IRBSuwRoTGA";
$fsPage = "pn400";

if( $numCredits == 900 ) {
    $priceID = "price_1HHaMALc2fQJ3IRBBet527ew";
    $fsPage = "pn900";
    }
else if( $numCredits == 2000 ) {
    $priceID = "price_1HHaNSLc2fQJ3IRBwYEUOf4F";
    $fsPage = "pn2000";
    }
else if( $numCredits == 5000 ) {
    $priceID = "price_1HIHLDLc2fQJ3IRBhbeB2F7y";
    $fsPage = "pn5000";
    }
else {
    // don't allow any other $numCredits through, since they aren't supported
    // map all other values to 400
    $numCredits = 400;
    }

$stripeParams = " '$email', $numCredits, '$priceID' ";
    ?>




<center>
<br>
<noscript>Please enable JavaScript<br><br></noscript>

<font size=5>Adding <b><?php echo $numCredits;?></b> Compute Credits to the<br>PROJECT DECEMBER account for <b><?php echo $email;?></b>:</font>
<br>
<br>

<table border=0 cellspacing=30>

<tr>

<td>
<a id="myLink" title="Buy with PayPal"
 href="https://sites.fastspring.com/jasonrohrer/instant/<?php echo $fsPage;?>?referrer=<?php echo $referrer;?>"><img border=0 width=250 height=52 src="payButtons/payPal.png"></a>
</td>
<td>
<a id="myLink" title="Buy with Credit Card"
 href="#" onclick="stripeCheckout(<?php echo $stripeParams;?>);return false;"><img border=0 width=250 height=52 src="payButtons/creditCard.png"></a>
</td>
<?tr>

<tr>
<td>
<a id="myLink" title="Buy with Apple Pay"
 href="#" onclick="stripeCheckout(<?php echo $stripeParams;?>);return false;"><img border=0 width=250 height=52 src="payButtons/applePay.png"></a>
</td>
<td>
<a id="myLink" title="Buy with Google Pay"
 href="#" onclick="stripeCheckout(<?php echo $stripeParams;?>);return false;"><img border=0 width=250 height=52 src="payButtons/googlePay.png"></a>
</td>
</tr>

<tr>
<td>
<a id="myLink" title="Buy with Amazon Pay"
 href="https://sites.fastspring.com/jasonrohrer/instant/<?php echo $fsPage;?>?referrer=<?php echo $referrer;?>"><img border=0 width=250 height=52 src="payButtons/amazonPay.png"></a>
</td>
<td>
<a id="myLink" title="Buy with other methods"
 href="#" onclick="stripeCheckout(<?php echo $stripeParams;?>);return false;"><img border=0 width=250 height=52 src="payButtons/otherPay.png"></a>
</td>
</tr>

</table>


<br>


</center>

</body>

</html>