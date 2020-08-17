
	
var publishableKey = "pk_live_51HFoSwLc2fQJ3IRBCJ0eqIwT2W6QOOhlOAhAe2Z8Fob5F1fCrJDyluhYh5tPRxPFPurOaNfagom7Nre4hTOFpDLC002NJC9MLU";

//publishableKey = "pk_live_IRTvo1yCjcLPyuRxNfFUPNQb";


var stripe = Stripe( publishableKey );

function stripeCheckout() {
	stripe
		.redirectToCheckout({
			lineItems: [
				// Replace with the ID of your price
				{price: 'price_1HFpXiLc2fQJ3IRBSuwRoTGA', quantity: 1},
			],
			mode: 'payment',
			successUrl: 'https://projectdecember.net/success.php',
			cancelUrl: 'https://projectdecember.net/buy.php',
		})
		.then(function(result) {
			// If `redirectToCheckout` fails due to a browser or network
			// error, display the localized error message to your customer
			// using `result.error.message`.
		});
	}