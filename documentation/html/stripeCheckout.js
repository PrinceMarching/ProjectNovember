
	
var publishableKey = "pk_live_51HFoSwLc2fQJ3IRBCJ0eqIwT2W6QOOhlOAhAe2Z8Fob5F1fCrJDyluhYh5tPRxPFPurOaNfagom7Nre4hTOFpDLC002NJC9MLU";

//publishableKey = "pk_live_IRTvo1yCjcLPyuRxNfFUPNQb";


var stripe = Stripe( publishableKey );

function stripeCheckout( email, num_credits, priceID ) {
	// default arguments
	if( num_credits === undefined ) {
		num_credits = 0;
	}
	if( email === undefined ) {
		email = "";
	}

	// assume they are buying credits
	// bounce them back to credits page for same values
	backURL = 'https://projectdecember.net/credits.php?email='.
		concat( email ).concat( "&num_credits=" ).concat( num_credits );

	// default arguments
	// if price_id NOT present, we came from buy.php
	// so go back there as backURL
	if( priceID === undefined ) {
		// base account purchase price ID
		priceID = "price_1HFpXiLc2fQJ3IRBSuwRoTGA";
		backURL = 'https://projectdecember.net/buy.php';
	}

	stripe
		.redirectToCheckout({
			lineItems: [
				// Replace with the ID of your price
				{price: priceID, quantity: 1},
			],
			mode: 'payment',
			successUrl: 'https://projectdecember.net/success.php',
			cancelUrl: backURL,
			customerEmail: email,
		})
		.then(function(result) {
			// If `redirectToCheckout` fails due to a browser or network
			// error, display the localized error message to your customer
			// using `result.error.message`.
		});
	}
