
	
var publishableKey = "pk_live_51HFoSwLc2fQJ3IRBOgRdnkYfUxxom8YwK15lqsVBqwqJYv04rIjIObmBsWY4XStPO3wIbimBrXizwPKKXin95m9900";

//publishableKey = "pk_live_IRTvo1yCjcLPyuRxNfFUPNQb";


var stripe = Stripe( publishableKey );

function stripeCheckout() {
	stripe
		.redirectToCheckout({
			lineItems: [
				// Replace with the ID of your price
				{price: 'prod_HpUW2A9Pn0dJjz', quantity: 1},
			],
			mode: 'payment',
			successUrl: 'https://your-website.com/success',
			cancelUrl: 'https://your-website.com/canceled',
		})
		.then(function(result) {
			// If `redirectToCheckout` fails due to a browser or network
			// error, display the localized error message to your customer
			// using `result.error.message`.
		});
	}