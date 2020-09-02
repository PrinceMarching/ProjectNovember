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



// text, email, number
// force to override iOS work-around
function setMobileInputType( inType, inForce = false ) {
	if( !onMobile ) {
		return;
	}

	if( onIOS && ! inForce ) {
		// iOS screen keyboard won't switch keyboard type
		// when focused field switches type
		// force all to be "text" to avoid getting stuck on a special-purpose
		// keyboard
		inType = "text";
		}
	
	var a = document.getElementById( "hiddenInput" );

	a.type = inType;
	
}



function hideMobileKeyboard() {
	var a = document.getElementById( "hiddenInput" );
	a.value = "";
	a.blur();
	}
