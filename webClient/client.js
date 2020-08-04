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
					 inColor = "#FFFFFF", inCorruptionFlags = [] ) {
	if( ! getFontLoaded() ) {
		return;
	}
	
	var font = getColoredFont( inColor );
	
	var corruptionFont = font;
	if( inCorruptionFlags.length > 0 ) {
		corruptionFont = getColoredFont( inColor, 1 );
	}

	var drawIndex = 0;
	var a = Array.from( inString );
	var x = inX;
	a.forEach(
		function( c ) {
			var i = c.charCodeAt( 0 );
			
			var f = font[ i ];
			
			if( inCorruptionFlags.length > drawIndex ) {
				if( inCorruptionFlags[drawIndex] ) {
					f = corruptionFont[ i ];
				}
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



function drawCursor( inX, inY, inCTX ) {
	trigger = 
		Math.floor( ( ( getMSTime() - cursorFlashStartTime ) / flashMS ) ) % 2;
	if( trigger == 0 ) {
		if( ! lastWasDraw ) {
			// schedule redraw to keep cursor flashing
			lastWasDraw = true;
			timedRedraw( flashMS );
		}
		drawString( "|", inX - 4 * drawScale, inY, inCTX, "#FFFF00" );
		drawString( "|", inX + 1 * drawScale, inY, inCTX, "#FFFF00" );
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


function longLineSplitter( inString, maxLength ){
    var strs = [];
    while( inString.length > maxLength ) {
        var pos = inString.substring( 0, maxLength).lastIndexOf(' ');
        pos = pos <= 0 ? maxLength : pos;
        strs.push( inString.substring( 0, pos ) );
        var i = inString.indexOf( ' ', pos ) + 1;
        if( i < pos || i > pos + maxLength )
            i = pos;
        inString = inString.substring( i );
    }
    strs.push( inString);
    return strs;
}


var lineBuffer = [];
var lineBufferColor = [];

var lineBufferCorruptionFlags = [];


var linesToAdd = [];

var linesToAddProgress = [];

var linesToAddColor = [];
var linesToAddCorruptionFlags = [];

var liveTypedCommand = "";

function addLineToBuffer( inString, inColor, inCorruptionChance = 0.0,
						  inCorruptionSkip = 0 ) {
	
	var charsWide = canvas.width / fontSpacingH - 1;
	var newLines = longLineSplitter( inString, charsWide );
	linesToAdd = linesToAdd.concat( newLines );
	
	var charsAdded = 0;
	
	newLines.forEach(
		function( line ) {
			linesToAddProgress.push( 0 );
			linesToAddColor.push( inColor );
			var corruptionFlags = [];
			for( i =0; i<line.length; i++ ) {
				if( charsAdded >= inCorruptionSkip && 
					Math.random() < inCorruptionChance ) {
					corruptionFlags.push( true );
					}
				else {
					corruptionFlags.push( false );
				}
				charsAdded++;
			}
			linesToAddCorruptionFlags.push( corruptionFlags );
		}
	);
}



function clearLineBuffers() {
	lineBuffer = [];
	lineBufferColor = [];
	
	lineBufferCorruptionFlags = [];
	
	
	linesToAdd = [];
	
	linesToAddProgress = [];
	
	linesToAddColor = [];
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


var stringToDraw = "Hey there, punk!";
var stringToDrawProgress = 0;

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
		
		commandLines = splitCommandLines( inCanvas );

		drawY = inCanvas.height - 2 * fontSpacingV;
		
		
		
		drawY += scrollUp * fontSpacingV;
		
		if( commandLines.length > 1 ) {
			drawY -= fontSpacingV * ( commandLines.length - 1 );
		}
		drawString( ">", 10, drawY, inCTX, "#FFFF00" );
		
		lineY = drawY;
		lineI = 0;
		
		commandLines.forEach(
			function( line ) {
				drawString( line, 10 + fontSpacingH, lineY, inCTX, "#FFFF00" );
				if( lineI == commandLines.length - 1 ) {
					// last line of current command
					// put cursor at end
					drawCursor( 10 + fontSpacingH + fontSpacingH *
								line.length, lineY, inCTX ); 
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
	  ( getMSTime() - charPrintingStartTime ) > charPrintingStepMS ) {
		charPrintingStartTime = getMSTime();

		timedRedraw( charPrintingStepMS );

		if( linesToAddProgress[0] == 0 ) {
			lineBuffer.push( linesToAdd[0].substring( 0, 1 ) );
			lineBufferColor.push( linesToAddColor[0] );
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


function doKeyPress( e ) {
	if( e.keyCode >= 32 && e.keyCode <= 126 ) {
		scrollUp = 0;
		e.preventDefault();
		resetCursorFlash();
		c = String.fromCharCode( e.keyCode );
		liveTypedCommand = liveTypedCommand.concat( c );
	}
	else if( e.keyCode == 13 ) {
		scrollUp = 0;
		resetCursorFlash();

		
		var lowerCommand = liveTypedCommand.toLowerCase();
		
		if( lowerCommand == "export" ) {
			exportAll();
		}
		else if( lowerCommand == "clear" ) {
			clearLineBuffers();
		}
		else if( lowerCommand.startsWith( "corruption=" ) ) {
			var c = parseInt( lowerCommand.split( "=" )[1] );
			if( c >= 0 && c <= 10 ) {
				currentCorruption = c / 10;
				addLineToBuffer( 
					"--:CORRUPTION LEVEL ".
						concat( c ).concat( ":--" ), 
					"#FF0000", 0 );
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
			
			addLineToBuffer( liveTypedCommand, lineColor, c, cSkip );
			
			playSoundObjectSequence( beepSoundObj, liveTypedCommand.length,
									 charPrintingStepMS );
		}
		liveTypedCommand = "";		
	}
	redrawNow();
}



function exportAll() {
	var canvasSub = document.createElement( 'canvas' ),
    ctxSub = canvasSub.getContext( '2d' );

    canvasSub.width = canvas.width;
    canvasSub.height = fontSpacingV * ( lineBuffer.length + 1 );

	drawFrameContents( ctxSub, canvasSub, true );
	url = canvasSub.toDataURL( "image/png" );
	window.open( url, '_blank');
}



function doKeyDown( e ) {
	if( e.keyCode == 8 ) {
		scrollUp = 0;
		e.preventDefault();
		resetCursorFlash();
		if( liveTypedCommand.length > 0 ) {
			liveTypedCommand = 
				liveTypedCommand.substring( 0, liveTypedCommand.length - 1 );
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
	redrawNow();
}




window.addEventListener( 'paste', (event) => {
    let paste = 
		( event.clipboardData || window.clipboardData ).getData('text');
	
	if( paste != "" ) {
		resetCursorFlash();
		liveTypedCommand = liveTypedCommand.concat( paste );
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





