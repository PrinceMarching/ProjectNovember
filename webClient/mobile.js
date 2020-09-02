// from this answer here:
// https://stackoverflow.com/questions/11381673/detecting-a-mobile-browser
function isOnMobile() {
	if( navigator.userAgent.match(/Android/i) 
        || navigator.userAgent.match(/webOS/i) 
        || navigator.userAgent.match(/iPhone/i)  
        || navigator.userAgent.match(/iPad/i)  
        || navigator.userAgent.match(/iPod/i) 
        || navigator.userAgent.match(/BlackBerry/i) 
        || navigator.userAgent.match(/Windows Phone/i) ) {
		return true;
	}
	else {
		return false;
	}
}


function isOnIOS() {
	if( navigator.userAgent.match(/iPhone/i)  
        || navigator.userAgent.match(/iPad/i)  
        || navigator.userAgent.match(/iPod/i) ) {
		return true;
	}
	else {
		return false;
	}
}


var fieldPresent = false;
var onMobile = false;
var onIOS = false;


function setupMobileTextInput() {
	if( onMobile || isOnMobile() ) {
		onMobile = true;
		
		if( onIOS || isOnIOS() ) {
			onIOS = true;
			}

		var d = document.getElementById('content');
		
		var x = canvas.offsetLeft;
		var y = canvas.offsetTop;
		x += 5;
		// place in bottom 1/6 of canvas
		// if text field fills entire canvas space, then iOS
		// focuses into the middle in a very strange way
		y += 5 * canvas.height / 6;
		d.style.position = "absolute";
		d.style.left = x+'px';
		d.style.top = y+'px';
		
		if( !fieldPresent ) {
			d.innerHTML = 
				"<input id='hiddenInput' type='text' name='hiddenInput'>";
			}
		var a = document.getElementById( "hiddenInput" );

		var c = 'black';

		a.style.width=canvas.width - 10;
		// place in bottom 1/6 of canvas, so iOS focuses in right spot
		a.style.height=canvas.height / 6 - 10;
		a.style.color= c;
		a.style.backgroundColor= c;
		a.style.borderColor= c;
		a.style.outline= 'none';
		a.style.borderStyle= 'solid';
		a.style.resize = 'none';
		a.style.caretColor = 'transparent';

		// larger font causes iOS to zoom in on a larger area
		a.style.fontSize = "30pt";

		canvas.addEventListener( "click", canvasMobileClick );
		fieldPresent = true;
	}
}



var buttonsShowing = false;

function showMobileButtons() {
	if( !onMobile ) {
		return;
	}
	var b = document.getElementById( 'mobileButtons' );
	b.style.position = 'absolute';
	b.style.left = canvas.offsetLeft + canvas.width + 5;
	b.style.top = canvas.offsetTop;
	b.height = canvas.height;
		
	if( !buttonsShowing ) {
		b.innerHTML = "<table border=0 "+
			"cellspacing=0 cellpadding=0 height=100%>" +
			"<tr><td id='tb1'><button id='b1'>&#8607;</button></td></tr>" +
			"<tr><td id='tb2'><button id='b2'>&#8593;</button></td></tr>" +
			"<tr><td id='tb3'><button id='b3'>&#8595;</button></td></tr>" +
			"<tr><td id='tb4'><button id='b4'>&#8609;</button></td></tr>" +
			"</table>";
	}

	for( var i =1; i<=4; i++ ) {
		let idT = "tb" + i;
		let idB = "b" + i;
		let t = document.getElementById( idT );
		t.height = canvas.height / 4;
		
		let tb = document.getElementById( idB );
		tb.style.height = canvas.height / 4 - 5;
		tb.style.width = canvas.width / 16;
		tb.style.fontSize = "20pt";
		
		tb.addEventListener( "click", mobileButtonClick );
		
	}
	buttonsShowing = true;
}



function mobileButtonClick( e ) {
	var visibleLines = getVisibleLines();
	
	if( e.srcElement.id == 'b1' ) {
		// page up
		scrollUp += visibleLines;
	}
	else if( e.srcElement.id == 'b2' ) {
		// up
		scrollUp += 1;
	}
	else if( e.srcElement.id == 'b3' ) {
		// down
		scrollUp -= 1;
	}
	else if( e.srcElement.id == 'b4' ) {
		// page down
		scrollUp -= visibleLines;
	}
	capScrollUp();
	redrawNow();
}



function hideMobileButtons() {
	if( !onMobile ) {
		return;
	}	
	if( buttonsShowing ) {
		var b = document.getElementById( 'mobileButtons' );
		b.innerHTML = "";
	}
	buttonsShowing = false;
}




var someInputSeen = false;

function canvasMobileClick() {
	// watch out for change event on iOS when field comes into focus
	someInputSeen = false;

	var a = document.getElementById( "hiddenInput" );
	a.value = "";
	a.focus();
	a.addEventListener( "input", mobileTextInput );
	a.addEventListener( "keypress", mobileTextInput );
	a.addEventListener( "change", mobileTextSubmit );
	console.log( "FOCUSED" );
	// jump right back to bottom
	scrollUp = 0;
}



var inputCount = 0;
function mobileTextInput( e ) {
	if( responseEnterOnly ) {
		// any input on mobile good enough when we're waiting for ENTER key
		someInputSeen = true;
		mobileTextSubmit( e );
		return;
	}

	liveTypedCommand = e.target.value;
	liveTypedCursorOffset = liveTypedCommand.length;

	if( liveTypedCommand.length > 0 ) {
		someInputSeen = true;
		
		// jump back to bottom if they have been scrolling
		scrollUp = 0;
		}

	resetCursorFlash();
	redrawNow();
	
	console.log( "input ".concat( inputCount ) );
	inputCount ++;
	}



function mobileTextSubmit( e ) {
	if( ! someInputSeen ) {
		return;
	}
	e.keyCode = 13;
	doKeyPress( e );
	var a = document.getElementById( "hiddenInput" );
	// leave focused for now
	//a.blur();
	a.value = "";

	console.log( "submit ".concat( inputCount ) );
	inputCount ++;
	}



var lastInputType = "email";

// text, email, number
// force to override iOS work-around
function setMobileInputType( inType, inForce = false ) {
	if( !onMobile ) {
		return;
	}

	if( false && onIOS && ! inForce ) {
		// iOS screen keyboard won't switch keyboard type
		// when focused field switches type
		// force all to be "text" to avoid getting stuck on a special-purpose
		// keyboard
		inType = "text";
		}
	
	var a = document.getElementById( "hiddenInput" );

	a.type = inType;

	if( onIOS && lastInputType != inType ) {
		// changing type on iOS
		// de-focus field to hide keyboard
		// different keyboard shown when user clicks to re-focus
		a.blur();
		}
	
	lastInputType = inType;
}



function hideMobileKeyboard() {
	var a = document.getElementById( "hiddenInput" );
	a.value = "";
	a.blur();
	}
