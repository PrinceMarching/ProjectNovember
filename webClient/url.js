

var urlDebug = false;


function getURLAndCall( inURL, inResponseCall ) {
	var http = new XMLHttpRequest();
	
	if( urlDebug )
		console.log( "Starting to get URL: ".concat( inURL ) );

	http.open( "GET", inURL );
	http.send();

	http.onreadystatechange = function () {
		if( http.readyState === XMLHttpRequest.DONE ) {
			
			if( urlDebug )
				console.log( 
					inURL.concat( " got response: " ).
						concat( http.responseText ) );
			
			inResponseCall( http.responseText );
		}
	};
}