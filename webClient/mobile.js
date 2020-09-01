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
		//x -= canvas.width / 2;
		//y -= canvas.height / 2;
		x += 5;
		y += 5
		d.style.position = "absolute";
		d.style.left = x+'px';
		d.style.top = y+'px';
		//d.style.left = 10;
		//d.style.top = 10;
		
		if( !fieldPresent ) {
			d.innerHTML = 
				"<input id='hiddenInput' type='text' name='hiddenInput'>";

			//a.style.visibility = 'hidden';
			}
		var a = document.getElementById( "hiddenInput" );

		var c = 'red';

		a.style.width=canvas.width - 10;
		a.style.height=canvas.height - 10;
		a.style.color= c;
		a.style.backgroundColor= c;
		a.style.borderColor= c;
		a.style.outline= 'none';
		a.style.borderStyle= 'solid';
		a.style.resize = 'none';
		a.style.caretColor = 'transparent';

		canvas.addEventListener( "click", canvasMobileClick );
		fieldPresent = true;
	}
}



function canvasMobileClick() {
	var a = document.getElementById( "hiddenInput" );
	//a.style.visibility = 'visible';
	a.value = "";
	a.focus();
	//a.style.visibility = 'hidden';
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
