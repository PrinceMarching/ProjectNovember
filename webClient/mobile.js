// from this answer here:
// https://stackoverflow.com/questions/11381673/detecting-a-mobile-browser
function isOnMobile() {
	return true;
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


var fieldPresent = false;
var onMobile = false;

function setupMobileTextInput() {
	if( isOnMobile() ) {
		onMobile = true;
		
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



function canvasMobileClick() {
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
		mobileTextSubmit( e );
		return;
	}

	liveTypedCommand = e.target.value;
	liveTypedCursorOffset = liveTypedCommand.length;
	resetCursorFlash();
	redrawNow();
	
	console.log( "input ".concat( inputCount ) );
	inputCount ++;
	}



function mobileTextSubmit( e ) {
	e.keyCode = 13;
	doKeyPress( e );
	var a = document.getElementById( "hiddenInput" );
	// leave focused for now
	//a.blur();
	a.value = "";

	console.log( "submit ".concat( inputCount ) );
	inputCount ++;
	}



function setTextInMobileInputField( inText ) {
	var a = document.getElementById( "hiddenInput" );
	a.value = inText;
}



// text, email, number
function setMobileInputType( inType ) {
	if( !onMobile ) {
		return;
	}
	
	var a = document.getElementById( "hiddenInput" );

	a.type = inType;
	
}
