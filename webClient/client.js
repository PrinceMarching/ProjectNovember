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
}


window.onresize = reportWindowSize;

// do one resize call at start
reportWindowSize();



document.body.appendChild(canvas);

canvas.style = "position: absolute; top: 0px; left: 0px; right: 0px; bottom: 0px; margin: auto; border:0px";


var scanLinesLoaded = false;


function drawString( inString, inX, inY, inCTX,
					 inColor = "#FFFFFF" ) {
	if( ! getFontLoaded() ) {
		return;
	}
	
	font = getColoredFont( inColor );

	a = Array.from( inString );
	x = inX;
	a.forEach(
		function( c ) {
			i = c.charCodeAt( 0 );
			f = font[ i ];
			inCTX.drawImage( f, x, inY, 
							 f.width * drawScale, f.height * drawScale );
			x += fontSpacingH;
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
		drawString( "]", inX - 4 * drawScale, inY, inCTX, "#FF0000" );
		drawString( "]", inX + 1 * drawScale, inY, inCTX  );
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

var linesToAdd = [];

var linesToAddProgress = [];

var linesToAddColor = [];


var liveTypedCommand = "";

function addLineToBuffer( inString, inColor ) {
	charsWide = canvas.width / fontSpacingH - 1;
	newLines = longLineSplitter( inString, charsWide );
	linesToAdd = linesToAdd.concat( newLines );

	newLines.forEach(
		function( line ) {
			linesToAddProgress.push( 0 );
			linesToAddColor.push( inColor );
		}
	);
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
var charPrintingStepMS = 50;



function drawFrame() {
	drawFrameContents( ctx, canvas );
}



function drawFrameContents( inCTX, inCanvas ) {
	inCTX.fillStyle = '#000';
	inCTX.fillRect( 0, 0, inCanvas.width, inCanvas.height );

	if( ! fontLoaded || ! scanLinesLoaded ) {
		redrawNow();
		return;
		}

	linesToShow = inCanvas.height / fontSpacingV;

	// leave room for live command at bottom
	linesToShow - 1;
	
	linesToSkip = lineBuffer.length - linesToShow;
	if( linesToSkip < 0 ) {
		linesToSkip = 0;
	}

	commandLines = longLineSplitter( liveTypedCommand, 
									 inCanvas.width / fontSpacingH - 3 );
	

	drawY = inCanvas.height - 2 * fontSpacingV;

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
	
	drawY -= fontSpacingV;

	for (var i = lineBuffer.length - 1; i >= linesToSkip; i--) {
		drawString( lineBuffer[i], 10, drawY, inCTX, lineBufferColor[i] );
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
			linesToAddProgress[0] ++;
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
			}
		}
	}
	

	scanLinesCover = scanLinesImg.height * drawScale;
	
	scanLinesCoverTotal = 0;

	while( scanLinesCoverTotal < inCanvas.height ) {
		
		inCTX.drawImage( scanLinesImg, 0, scanLinesCoverTotal, 
						 scanLinesImg.width * drawScale,
						 scanLinesImg.height * drawScale );
		scanLinesCoverTotal += scanLinesCover;
	}
}




function doKeyPress( e ) {
	if( e.keyCode >= 32 && e.keyCode < 126 ) {
		e.preventDefault();
		resetCursorFlash();
		c = String.fromCharCode( e.keyCode );
		liveTypedCommand = liveTypedCommand.concat( c );
	}
	else if( e.keyCode == 13 ) {
		resetCursorFlash();

		isExport = false;
		if( liveTypedCommand == "export" ) {
			isExport = true;
		}
		else {

			var randomColor = Math.floor(Math.random()*16777215).toString(16);
			addLineToBuffer( liveTypedCommand, "#".concat( randomColor ) );
			playSoundObjectSequence( beepSoundObj, liveTypedCommand.length,
									 charPrintingStepMS );
			}
		liveTypedCommand = "";
		
		if( isExport ) {
			exportAll();
		}
	}
	redrawNow();
}



function exportAll() {
	var canvasSub = document.createElement( 'canvas' ),
    ctxSub = canvasSub.getContext( '2d' );

    canvasSub.width = canvas.width;
    canvasSub.height = fontSpacingV * ( lineBuffer.length + 3 );

	drawFrameContents( ctxSub, canvasSub );
	url = canvasSub.toDataURL( "image/png" );
	window.open( url, '_blank');
}



function doKeyDown( e ) {
	if( e.keyCode == 8 ) {
		e.preventDefault();
		resetCursorFlash();
		if( liveTypedCommand.length > 0 ) {
			liveTypedCommand = 
				liveTypedCommand.substring( 0, liveTypedCommand.length - 1 );
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
	}
} );





var nextBeep = 0;
var beep = new Audio('beep.wav');





var scanLinesImg = new Image();
scanLinesImg.onload = function() {
	
	scanLinesLoaded = true;
};
scanLinesImg.src = 'scanLines.png';







beepSoundObj = loadSoundObject( "beep2.wav" );





