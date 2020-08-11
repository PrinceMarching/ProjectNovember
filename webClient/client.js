var canvas = document.createElement("canvas");
var ctx = canvas.getContext("2d");

var baseW = 640;
var baseH = 480;



var drawScale = 1;


var fontSpacingH = baseFontSpacingH * drawScale;
var fontSpacingV = baseFontSpacingV * drawScale;


function reportWindowSize() {
	baseAspect = baseW / baseH;
	windowAspect = window.innerWidth / window.innerHeight;
	
	if( windowAspect >= baseAspect ) {
		// empty bars on sides	
		drawScale = window.innerHeight / baseH;
		}
	else {
		// empty bars on top/bottom
		drawScale = window.innerWidth / baseW;
	}
	
		// whole multiples, to avoid weird aliasing
	if( drawScale > 1 ) {
		drawScale = Math.floor( drawScale );
	}
	else {
		// whole divisions 1/2, 1/3, 1/4, etc.
		invDrawScale = Math.ceil( 1 / drawScale );
		drawScale = 1 / invDrawScale;
	}
	
	canvas.height = baseH * drawScale;
	canvas.width = baseW * drawScale;
	
	fontSpacingH = baseFontSpacingH * drawScale;
	fontSpacingV = baseFontSpacingV * drawScale;
	redrawNow();
}


window.onresize = reportWindowSize;
window.onfocus = reportWindowSize;

// do one resize call at start
reportWindowSize();



document.body.appendChild(canvas);

canvas.style = "position: absolute; top: 0px; left: 0px; right: 0px; bottom: 0px; margin: auto; border:0px";


var scanLinesLoaded = false;
var vignetteLoaded = false;

function drawString( inString, inX, inY, inCTX,
					 inColor = "#FFFFFF", inCorruptionFlags = [], 
					 // if inv font, non-corrupted characters are drawn
					 // with index-2 font (inverted)
					 inInvFont = false ) {
	if( ! getFontLoaded() ) {
		return;
	}
	
	var font = getColoredFont( inColor );
	
	var corruptionFont = font;
	if( inCorruptionFlags.length > 0 ) {
		corruptionFont = getColoredFont( inColor, 1 );
	}

	var invFont = font;
	if( inInvFont ) {
		invFont = getColoredFont( inColor, 2 );
	}

	var drawIndex = 0;
	var a = Array.from( inString );
	var x = inX;
	a.forEach(
		function( c ) {
			var i = c.charCodeAt( 0 );
			
			var f = font[ i ];
			let cor = false;

			if( inCorruptionFlags.length > drawIndex ) {
				if( inCorruptionFlags[drawIndex] ) {
					f = corruptionFont[ i ];
					cor = true;
				}
			}
			
			if( inInvFont && ! cor ) {
				f = invFont[ i ];
			}

			inCTX.drawImage( f, x, inY, 
							 f.width * drawScale, f.height * drawScale );
			x += fontSpacingH;
			drawIndex ++;
		}
	);
}



function getMSTime() {
	var d = new Date();
	return d.getTime();
}


var cursorFlashStartTime = getMSTime();
var flashMS = 500;
var lastWasDraw = false;



function resetCursorFlash() {
	cursorFlashStartTime = getMSTime();
	lastWasDraw = false;
}


var promptColor = "#FFFF00";


function drawCursor( inX, inY, inCTX ) {
	trigger = 
		Math.floor( ( ( getMSTime() - cursorFlashStartTime ) / flashMS ) ) % 2;
	if( trigger == 0 ) {
		if( ! lastWasDraw ) {
			// schedule redraw to keep cursor flashing
			lastWasDraw = true;
			timedRedraw( flashMS );
		}
		drawString( "|", inX - 4 * drawScale, inY, inCTX, promptColor );
		drawString( "|", inX - 2 * drawScale, inY, inCTX, promptColor );
		drawString( "|", inX + 1 * drawScale, inY, inCTX, promptColor );
		drawString( "|", inX + 4 * drawScale, inY, inCTX, promptColor );
		drawString( "|", inX + 6 * drawScale, inY, inCTX, promptColor );

		if( liveTypedCommand.length > 0 && 
			liveTypedCursorOffset < liveTypedCommand.length ) {
			// cursor over a character
			// draw inverted character on top
			// inv font with black color
			drawString( liveTypedCommand.substring( liveTypedCursorOffset,
													liveTypedCursorOffset + 1 ),
						inX, inY, inCTX, "#000000", [], true );
		}
	}
	else {
		// else skip drawing
		if( lastWasDraw ) {
			// schedule redraw to keep cursor flashing
			lastWasDraw = false;
			timedRedraw( flashMS );
		}
	}
}


function longLineSplitter( inString, maxLength ) {
    var strs = [];
    while( inString.length > maxLength ) {
		let pos = -1;
		let targetLength = maxLength;
		let maxPossibleLenth = inString.length;

		// look at longer and longer string until we find a space
		while( pos == -1 && targetLength < maxPossibleLenth ) {
			pos = inString.substring( 0, targetLength ).lastIndexOf(' ');
			
			if( pos == -1 ) {
				targetLength ++;
			}
		}

		if( pos == -1 ) {
			pos = inString.length - 1;
			}

		// leave trailing space at end
		
		strs.push( inString.substring( 0, pos + 1 ) );
        
		if( pos + 1 != inString.length ) {
			// keep working on rest
			inString = inString.substring( pos + 1 );
			}
		else {
			// done
			inString = "";
		}
    }
	if( inString != "" || strs.length == 0 ) {
		// don't push a left-over empty string
		// unless it's our only string (asked to split an empty string)
		strs.push( inString );
	}
    return strs;
}


// original lines as added, without wrapping applied
// used for text export
var origLineBuffer = [];

var lineBuffer = [];
var lineBufferColor = [];
var lineBufferCharMS = []

var lineBufferCorruptionFlags = [];


var linesToAdd = [];

var linesToAddProgress = [];

var linesToAddColor = [];
var linesToAddCharMS = [];
var linesToAddCorruptionFlags = [];

var liveTypedCommand = "";

// relative to beginning of what has been typed
// a 0 or positive value
var liveTypedCursorOffset = 0;


function addLineToBuffer( inString, inColor, inMSPerChar, 
						  inCorruptionChance = 0.0,
						  inCorruptionSkip = 0 ) {
	
	var origStringArray = inString.split( "" );
	
	var charsWide = canvas.width / fontSpacingH - 1;
	var newLines = longLineSplitter( inString, charsWide );
	linesToAdd = linesToAdd.concat( newLines );
	
	var charsAdded = 0;
	
	newLines.forEach(
		function( line ) {
			linesToAddProgress.push( 0 );
			linesToAddColor.push( inColor );
			linesToAddCharMS.push( inMSPerChar );
			var corruptionFlags = [];
			for( i =0; i<line.length; i++ ) {
				// never corrupt spaces
				if( inCorruptionChance > 0 && 
					charsAdded >= inCorruptionSkip && 
					origStringArray[ charsAdded ] != ' ' &&
					Math.random() < inCorruptionChance ) {
					
					corruptionFlags.push( true );
					origStringArray[ charsAdded ] = '#';
				}
				else {
					corruptionFlags.push( false );
				}
				charsAdded++;
			}
			linesToAddCorruptionFlags.push( corruptionFlags );
		}
	);
	
	origLineBuffer.push( origStringArray.join( "" ) );
}



function clearLineBuffers() {
	origLineBuffer = [];

	lineBuffer = [];
	lineBufferColor = [];
	lineBufferCharMS = []

	lineBufferCorruptionFlags = [];
	
	
	linesToAdd = [];
	
	linesToAddProgress = [];
	
	linesToAddColor = [];
	linesToAddCharMS = [];
	linesToAddCorruptionFlags = [];
}




function redrawNow() {
	window.requestAnimationFrame(drawFrame);
}




function timedRedraw( inMSFromNow ) {
	setTimeout( redrawNow, inMSFromNow );
}




redrawNow();
window.addEventListener( "keypress", doKeyPress, false );
window.addEventListener( "keydown", doKeyDown, false );



// kick things off by fetching intro text from server
getIntroText();




var charPrintingStartTime = 0;
var charPrintingStepMS = 25;



function drawFrame() {
	drawFrameContents( ctx, canvas );
}



function splitCommandLines( inCanvas ) {
	commandLines = longLineSplitter( liveTypedCommand, 
									 inCanvas.width / fontSpacingH - 3 );
	return commandLines;
}



var scrollUp = 0;

var hidePrompt = true;

var email = "";
var passWords = "";



function drawFrameContents( inCTX, inCanvas, inIsExport ) {
	inCTX.fillStyle = '#000';
	inCTX.fillRect( 0, 0, inCanvas.width, inCanvas.height );

	if( ! fontLoaded || ! scanLinesLoaded || ! vignetteLoaded ) {
		redrawNow();
		return;
		}

	linesToShow = inCanvas.height / fontSpacingV;

	// leave room for live command at bottom
	linesToShow - 1;
	
	linesToSkip = ( lineBuffer.length - linesToShow ) - scrollUp;
	if( linesToSkip < 0 ) {
		linesToSkip = 0;
	}


	drawY = inCanvas.height;
	
	if( inIsExport ) {
		//center vertically a bit
		drawY -= fontSpacingV / 2.5;
	}
	else if( ! inIsExport ) {
		// hide prompt and what user is typing down at bottom during export

		let fullCommandLength = liveTypedCommand.length;

		commandLines = splitCommandLines( inCanvas );

		drawY = inCanvas.height - 2 * fontSpacingV;
		
		
		
		drawY += scrollUp * fontSpacingV;
		
		if( commandLines.length > 1 ) {
			drawY -= fontSpacingV * ( commandLines.length - 1 );
		}
		
		if( ! hidePrompt )
		drawString( ">", 10, drawY, inCTX, promptColor );
		
		lineY = drawY;
		lineI = 0;
		
		let charDrawnI = 0;
		let numLines = commandLines.length;
		commandLines.forEach(
			function( line ) {
				if( ! hidePrompt )
				drawString( line, 10 + fontSpacingH, lineY, inCTX, 
							promptColor );
				let oldCharDrawnI = charDrawnI;
				charDrawnI += line.length;
				
				if( numLines == 1
					||
					( lineI == numLines - 1 
					  && liveTypedCursorOffset == fullCommandLength )
					||
					( charDrawnI > liveTypedCursorOffset &&
					  oldCharDrawnI <= liveTypedCursorOffset ) ) {
					// cursor on this line!
					let cursorLineOffset = 
						liveTypedCursorOffset - oldCharDrawnI;
					
					if( ! hidePrompt )
					drawCursor( 10 + fontSpacingH + fontSpacingH *
								cursorLineOffset, 
								lineY, inCTX ); 
				}
				lineI ++;
				lineY += fontSpacingV;
			}
		);
	}
	
	drawY -= fontSpacingV;

	for (var i = lineBuffer.length - 1; i >= linesToSkip; i--) {
		drawString( lineBuffer[i], 10, drawY, inCTX, lineBufferColor[i],
					lineBufferCorruptionFlags[i] );
		drawY -= fontSpacingV;
	}

	// add one character from one line to add
	if( linesToAdd.length > 0 && 
	  ( getMSTime() - charPrintingStartTime ) > linesToAddCharMS[0] ) {
		charPrintingStartTime = getMSTime();

		timedRedraw( charPrintingStepMS );

		if( linesToAddProgress[0] == 0 ) {
			lineBuffer.push( linesToAdd[0].substring( 0, 1 ) );
			lineBufferColor.push( linesToAddColor[0] );
			lineBufferCharMS.push( linesToAddCharMS[0] );
			lineBufferCorruptionFlags.push( linesToAddCorruptionFlags[0] );
			linesToAddProgress[0] ++;
			if( scrollUp > 0 ) {
				// keep scroll position locked as more text is added
				// so what user is looking at remains stable.
				scrollUp ++;
			}
		}
		else {
			addIndex = lineBuffer.length - 1;
			charToAdd = linesToAddProgress[0];
			lineBuffer[ addIndex ] =
				lineBuffer[ addIndex ].concat( 
					linesToAdd[0].substring( charToAdd, charToAdd + 1 ) );
			linesToAddProgress[0]++;
			if( linesToAddProgress[0] >= linesToAdd[0].length ) {
				linesToAdd.shift();
				linesToAddProgress.shift();
				linesToAddColor.shift();
				linesToAddCharMS.shift();
				linesToAddCorruptionFlags.shift();
			}
		}
	}
	

	scanLinesCover = scanLinesImg.height * drawScale;
	
	scanLinesCoverTotal = 0;

	numScanlinesDrawn = 0;
	while( scanLinesCoverTotal < inCanvas.height ) {
		
		inCTX.drawImage( scanLinesImg, 0, scanLinesCoverTotal, 
						 scanLinesImg.width * drawScale,
						 scanLinesImg.height * drawScale );
		scanLinesCoverTotal += scanLinesCover;
		numScanlinesDrawn ++;
	}
	

	// stretch vignette to cover canvas vertically
	inCTX.globalAlpha = 0.50;
	
	inCTX.drawImage( vignetteImg, 0, 0, 
					 vignetteImg.width * drawScale,
					 inCanvas.height );
	inCTX.globalAlpha = 1.0;
}



var currentCorruption = 0;



function insertStringInTypedCommand( inString ) {
	let beforeCursor = liveTypedCommand.substring( 0, liveTypedCursorOffset );
	let afterCursor = liveTypedCommand.substring( liveTypedCursorOffset );
	
	liveTypedCommand = beforeCursor.concat( inString ).concat( afterCursor );
	liveTypedCursorOffset += inString.length;
	}



function doKeyPress( e ) {
	if( hidePrompt ) {
		return;
	}

	if( e.keyCode >= 32 && e.keyCode <= 126 ) {
		scrollUp = 0;
		e.preventDefault();
		resetCursorFlash();
		c = String.fromCharCode( e.keyCode );
		insertStringInTypedCommand( c );
	}
	else if( e.keyCode == 13 ) {
		scrollUp = 0;
		resetCursorFlash();

		
		var lowerCommand = liveTypedCommand.toLowerCase();


		if( email == "" ) {
			// still need to collect email for login
			email = lowerCommand;
			getPassWordsPrompt();
		}
		else if( passWords == "" ) {
			// clean up extra spaces
			passWords = lowerCommand.trim().split( /\s+/ ).join( " " );
			startLoginA();
		}
		else if( lowerCommand == "export" ) {
			exportAll();
		}
		else if( lowerCommand == "clear" ) {
			clearLineBuffers();
		}
		else if( true ) {
			// avoid following cases for now
			// can re-enable for testing later
			
			if( nextTypedDisplayPrefix != "" ) {
				// don't add command to buffer if there's
				// no display prefix
				let displayCommand = 
					nextTypedDisplayPrefix.concat( liveTypedCommand );
				
				addLineToBuffer( displayCommand, nextTypedDisplayColor, 
								 charPrintingStepMS, 0, 0 );
			}
			triggerNextAction( liveTypedCommand );
		}
		else if( lowerCommand.startsWith( "corruption=" ) ) {
			var c = parseInt( lowerCommand.split( "=" )[1] );
			if( c >= 0 && c <= 10 ) {
				currentCorruption = c / 10;
				addLineToBuffer( 
					"--:CORRUPTION LEVEL ".
						concat( c ).concat( ":--" ), 
					"#FF0000", charPrintingStepMS, 0 );
			}
		}
		else {

			var randomColor = Math.floor(Math.random()*16777215).toString(16);
			randomColor = "#".concat( randomColor );
			
			var lineColor = randomColor;

			var c = currentCorruption;
			var cSkip = 0;

			if( liveTypedCommand.startsWith( "Human:" ) ) {
				lineColor = "#88FF88";
				c = 0;
			}
			else if( liveTypedCommand.startsWith( "Computer:" ) ) {
				lineColor = "#FF8888";
				cSkip = 9;
			}
			
			addLineToBuffer( liveTypedCommand, lineColor, 
							 charPrintingStepMS, c, cSkip );
			
			// silent for now, when text added.
			// this isn't working reliably anyway.
			if( false )
			playSoundObjectSequence( beepSoundObj, liveTypedCommand.length,
									 charPrintingStepMS );
		}
		liveTypedCommand = "";
		liveTypedCursorOffset = 0;
	}
	redrawNow();
}



function openURLNewTab( url ) {
	window.open( url );
	console.log( "Opening url: ".concat( url ) );
}


function exportAll() {
	var canvasSub = document.createElement( 'canvas' ),
    ctxSub = canvasSub.getContext( '2d' );

    canvasSub.width = canvas.width;
    canvasSub.height = fontSpacingV * ( lineBuffer.length + 1 );

	drawFrameContents( ctxSub, canvasSub, true );
	url = canvasSub.toDataURL( "image/png" );

	openURLNewTab( url );


	// now export raw text
	stringToEncode = origLineBuffer.join( "\n\n" );
	b64 = btoa( stringToEncode );
	textURL = "data:text/plain;base64,".concat( b64 );

	openURLNewTab( textURL );
}



function doKeyDown( e ) {
	if( e.keyCode == 8 ) {
		scrollUp = 0;
		e.preventDefault();
		resetCursorFlash();
		if( liveTypedCommand.length > 0 ) {
			let beforeCursor = 
				liveTypedCommand.substring( 0, liveTypedCursorOffset );
			let afterCursor = 
				liveTypedCommand.substring( liveTypedCursorOffset );
			
			liveTypedCommand = "";
			
			if( beforeCursor.length > 0 ) {
				let numToEat = 1;
				if( e.ctrlKey ) {
					// eat a whole word
					let numSpacesEaten = 0;
					numToEat = 0;
					while( beforeCursor.length > numToEat &&
						   beforeCursor.substring( beforeCursor.length - 
												   numToEat - 1,
												   beforeCursor.length -
												   numToEat ) == " " ) {
						numToEat ++;
						numSpacesEaten ++;
					}
					if( numSpacesEaten == 0 ) {
						// eat word
						while( beforeCursor.length > numToEat &&
							   beforeCursor.substring( beforeCursor.length - 
													   numToEat,
													   1 + beforeCursor.length -
													   numToEat ) != " " ) {
							numToEat ++;
						}
					}
												 
				}
				liveTypedCommand = 
					beforeCursor.substring( 0, beforeCursor.length - numToEat );
				liveTypedCursorOffset -= numToEat;
			}
			liveTypedCommand = liveTypedCommand.concat( afterCursor );
		}
	}
	else if( e.keyCode == 38 ||
		   e.keyCode == 33 ) {
		
		// allow one blank line at very top.
		visibleLines = canvas.height / fontSpacingV - 2;
		

		if( e.keyCode == 38 ) {
			// up arrow
			scrollUp ++;
		}
		else {
			// page up
			scrollUp += visibleLines;
			}
		
		
		commandLines = splitCommandLines( canvas );

		numCommandLines = commandLines.length;

		if( scrollUp > lineBuffer.length + numCommandLines - visibleLines ) {
			scrollUp = lineBuffer.length + numCommandLines - visibleLines;
			if( scrollUp < 0 ) {
				scrollUp = 0;
			}
		}
	}
	else if( e.keyCode == 40 || e.keyCode == 34 ) {
		
		if( e.keyCode == 40 ) {
			// down arrow
			scrollUp --;
		}
		else {
			// allow one blank line at very top.
			visibleLines = canvas.height / fontSpacingV - 2;
			scrollUp -= visibleLines;
		}

		if( scrollUp < 0 ) {
			scrollUp = 0;
		}
	}
	else if( e.keyCode == 37 ) {
		// left arrow
		if( e.ctrlKey ) {
			// jump back over run of spaces
			let numSpacesJumped = 0;
			while( liveTypedCursorOffset > 0 &&
				   liveTypedCommand.substring( liveTypedCursorOffset - 1,
											   liveTypedCursorOffset )
				   == " " ) {
				liveTypedCursorOffset--;
				numSpacesJumped ++;
			}
			if( numSpacesJumped < 1 && liveTypedCursorOffset > 0 ) {
				// jump by whole word
				let spacePos = 
					liveTypedCommand.
					substring( 0, liveTypedCursorOffset ).
					lastIndexOf( " " );
				if( spacePos != -1 ) {
					liveTypedCursorOffset = spacePos;
				}
				else {
					// jump all the way to beginning
					liveTypedCursorOffset = 0;
				}
			}
		}
		else {
			// single char
			liveTypedCursorOffset --;
		}
		if( liveTypedCursorOffset < 0 ) {
			liveTypedCursorOffset = 0;
		}
		resetCursorFlash();
	}
	else if( e.keyCode == 39 ) {
		// right arrow
		if( e.ctrlKey ) {
			let numSpacesJumped = 0;
			while( liveTypedCursorOffset < liveTypedCommand.length &&
				   liveTypedCommand.substring( liveTypedCursorOffset,
											   liveTypedCursorOffset + 1 )
				   == " " ) {
				liveTypedCursorOffset++;
				numSpacesJumped ++;
				}

			if( numSpacesJumped < 2 && 
				liveTypedCursorOffset < liveTypedCommand.length ) {
				// jump by whole word
				let spacePos = 
					liveTypedCommand.
					substring( liveTypedCursorOffset + 1 ).
					indexOf( " " );
				if( spacePos != -1 ) {
					liveTypedCursorOffset += spacePos + 1;
				}
				else {
					// jump all the way to end
					liveTypedCursorOffset = liveTypedCommand.length;
				}
			}
			else if( numSpacesJumped >= 2 ) {
				// back off to mimic behavior of left arrow and delete
				liveTypedCursorOffset--;
			}
		}
		else {
			liveTypedCursorOffset++;
		}
		if( liveTypedCursorOffset > liveTypedCommand.length ) {
			liveTypedCursorOffset = liveTypedCommand.length;
			}
		resetCursorFlash();
	}
	else if( e.keyCode == 35 ) {
		// end
		liveTypedCursorOffset = liveTypedCommand.length;
		resetCursorFlash();
		}
	else if( e.keyCode == 36 ) {
		// home
		liveTypedCursorOffset = 0;
		resetCursorFlash();
		}
	
	redrawNow();
}




window.addEventListener( 'paste', (event) => {
    let paste = 
		( event.clipboardData || window.clipboardData ).getData('text');
	
	if( paste != "" ) {
		resetCursorFlash();
		insertStringInTypedCommand( paste );
		redrawNow();
	}
} );





var nextBeep = 0;
var beep = new Audio('beep.wav');





var scanLinesImg = new Image();
scanLinesImg.onload = function() {
	
	scanLinesLoaded = true;
};
scanLinesImg.src = 'scanLines.png';



var vignetteImg = new Image();
vignetteImg.onload = function() {
	
	vignetteLoaded = true;
};
vignetteImg.src = 'screenVignette.png';







beepSoundObj = loadSoundObject( "beep2.wav" );




function getServerActionAndCall( inAction, inResponseCall ) {
	var fullURL = serverURL.concat( "?action=" ).concat( inAction );
	getURLAndCall( fullURL, inResponseCall );
}



function stripCurly( inString ) {
	return inString.slice( 1, -1 );
}


// includes prompt color and text lines
function addResponseLines( inResponseLines ) {
	let lines = inResponseLines.split( "\n" );
	
	if( lines.length <= 1 ) {
		return;
	}
	
	promptColor = lines[0];
	lines.shift();
	
	lines.forEach( 
		function( s ) {
			let lineWords = s.split( " " );
			
			if( lineWords.length > 4 ) {
				let color = stripCurly( lineWords[0] );
				let ms = stripCurly( lineWords[1] );
				let corruptionFract = stripCurly( lineWords[2] );
				let corruptionSkip = stripCurly( lineWords[3] );
				
				lineWords.shift();
				lineWords.shift();
				lineWords.shift();
				lineWords.shift();
				
				// now join remainder as text of line
				let text = lineWords.join( " " );
				addLineToBuffer( text, color, ms, 
								 corruptionFract, corruptionSkip );
			}
		}
	);	
}



// sets prompt color, adds lines to buffer, and then calls a function with
// no parameters after
function getServerActionAndAddLines( inAction, inAfterCall ) {
	var callback = function( inText ) {
		addResponseLines( inText );
		inAfterCall();
	}
	
	getServerActionAndCall( inAction, callback );
}
	

function getIntroText() {
	getServerActionAndAddLines( "get_intro_text", getEmailPrompt );
}


function getEmailPrompt() {
	hidePrompt = true;
	getServerActionAndAddLines( "get_email_prompt", readyForEmail );
}


function getPassWordsPrompt() {
	hidePrompt = true;
	getServerActionAndAddLines( "get_pass_words_prompt", readyForPassWords );
}



function unhidePrompt() {
	hidePrompt = false;
	resetCursorFlash();
	redrawNow();
}


function readyForEmail() {
	unhidePrompt();
}


function readyForPassWords() {
	unhidePrompt();
}


var nextServerAction = "";
var nextCarriedParam = "";

var nextTypedDisplayPrefix = "";
var nextTypedDisplayColor = "";

function sendNextServerAction() {

}


var serverSequenceNumber = 0;

function startLoginA() {
	hidePrompt = true;
	var fullURL = serverURL.
		concat( "?action=get_client_sequence_number&email=" ).
		concat( email );
	getURLAndCall( fullURL, startLoginB );
	}


function startLoginB( inResponse ) {
	// split by whitespace
	let parts = inResponse.split( /\s+/ );
	
	serverSequenceNumber = parseInt( parts[0] );
	
	var fullURL = serverURL.
		concat( "?action=login&email=" ).
		concat( email ).
		concat( getSeqAndHash() );
	getURLAndCall( fullURL, parseStandardResponse );
}



function parseStandardResponse( inResponse ) {
	let parts = inResponse.split( "\n" );

	if( parts[0] == "DENIED" ) {
		addLineToBuffer( "DENIED", "#FF0000", charPrintingStepMS, 
						 0, 0 );
		email = "";
		passWords = "";
		getEmailPrompt();
		return;
	}
	
	nextServerAction = parts[0];
	nextCarriedParam = parts[1];
	// remove curly braces
	nextTypedDisplayPrefix = parts[2].slice( 1, -1 );
	nextTypedDisplayColor = parts[3];
	let clearFlag = parts[4];
	if( clearFlag == 1 ) {
		clearLineBuffers();
		}
	parts.shift();
	parts.shift();
	parts.shift();
	parts.shift();
	parts.shift();

	let lines = parts.join( "\n" );
	addResponseLines( lines );

	unhidePrompt();
}




function triggerNextAction( inWhatUserTyped ) {
	let encoded = encodeURIComponent( inWhatUserTyped );
	
	var fullURL = serverURL.
		concat( "?action=" ).
		concat( nextServerAction ).
		concat( "&carried_param=" ).
		concat( nextCarriedParam ).
		concat( "&client_command=" ).
		concat( encoded ).
		concat( "&email=" ).
		concat( email ).
		concat( getSeqAndHash() );
	
	getURLAndCall( fullURL, parseStandardResponse );
	hidePrompt = true;
}



// returns &sequence_number=[int]&hash_value=[hash value]  as string
// increments sequence number
function getSeqAndHash() {
	let s = serverSequenceNumber;
	let hash = hex_hmac_sha1( passWords, s.toString() );
	
	serverSequenceNumber++;
	
	return "&sequence_number=".
		concat( s ).
		concat( "&hash_value=" ).
		concat( hash );
}