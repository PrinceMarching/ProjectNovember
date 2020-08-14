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
    ?>




<center>
<br>
<noscript>Please enable JavaScript<br><br></noscript>

<font size=5>PROJECT DECEMBER is availble now for $5.00</font>
<br>
<br>

<table border=0 cellspacing=30>

<tr>

<td>
<a id="myLink" title="Buy with PayPal"
 href="https://sites.fastspring.com/jasonrohrer/instant/projectdecember?referrer=<?php echo $referrer;?>"><img border=0 width=250 height=52 src="payButtons/payPal.png"></a>
</td>
<td>
<a id="myLink" title="Buy with Credit Card"
 href="#" onclick="stripeCheckout();return false;"><img border=0 width=250 height=52 src="payButtons/creditCard.png"></a>
</td>
<?tr>

<tr>
<td>
<a id="myLink" title="Buy with Apple Pay"
 href="#" onclick="stripeCheckout();return false;"><img border=0 width=250 height=52 src="payButtons/applePay.png"></a>
</td>
<td>
<a id="myLink" title="Buy with Google Pay"
 href="#" onclick="stripeCheckout();return false;"><img border=0 width=250 height=52 src="payButtons/googlePay.png"></a>
</td>
</tr>

<tr>
<td>
<a id="myLink" title="Buy with Amazon Pay"
 href="https://sites.fastspring.com/jasonrohrer/instant/projectdecember?referrer=<?php echo $referrer;?>"><img border=0 width=250 height=52 src="payButtons/amazonPay.png"></a>
</td>
<td>
<a id="myLink" title="Buy with other methods"
 href="#" onclick="stripeCheckout();return false;"><img border=0 width=250 height=52 src="payButtons/otherPay.png"></a>
</td>
</tr>

</table>


<br>

<table border=0 width=500 cellspacing=0 cellpadding=0><tr><td>
<font size=4>After your payment is completed, your login details will be sent
to your email address immediately.  Your purchase includes 1500
complementary compute credits, which can be used to spin up
and enjoy the friendly personality matrices of your choice.</font>
</td>
</tr>
</table>



</center>

</body>

</html>