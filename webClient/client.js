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


function drawString( inString, inX, inY, inColor = "#FFFFFF" ) {
	if( ! getFontLoaded() ) {
		return;
	}
	
	font = getColoredFont( inColor );

	a = Array.from( inString );
	x = inX
	a.forEach(
		function( c ) {
			i = c.charCodeAt( 0 );
			f = font[ i ];
			ctx.drawImage( f, x, inY, 
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
function drawCursor( inX, inY ) {
	trigger = 
		Math.floor( ( ( getMSTime() - cursorFlashStartTime ) / flashMS ) ) % 2;
	if( trigger == 0 ) {
		drawString( "|", inX - 2 * drawScale, inY, "#FF0000" );
		drawString( "|", inX + 3 * drawScale, inY );
	}
	// else skip drawing
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

var linesToAdd = [];

var linesToAddProgress = [];

var liveTypedCommand = "";

function addLineToBuffer( inString ) {
	charsWide = canvas.width / fontSpacingH - 1;
	newLines = longLineSplitter( inString, charsWide );
	linesToAdd = linesToAdd.concat( newLines );

	newLines.forEach(
		function( line ) {
			linesToAddProgress.push( 0 );
		}
	);
}





window.requestAnimationFrame(drawFrame);
window.addEventListener( "keypress", doKeyPress, false );
window.addEventListener( "keydown", doKeyDown, false );


var stringToDraw = "Hey there, punk!";
var stringToDrawProgress = 0;

var charPrintingStartTime = 0;
var charPrintingStepMS = 50;


function drawFrame() {
	ctx.fillStyle = '#000';
	ctx.fillRect( 0, 0, canvas.width, canvas.height );

	if( ! fontLoaded || ! scanLinesLoaded ) {
		window.requestAnimationFrame(drawFrame);
		}

	linesToShow = canvas.height / fontSpacingV;

	// leave room for live command at bottom
	linesToShow - 1;
	
	linesToSkip = lineBuffer.length - linesToShow;
	if( linesToSkip < 0 ) {
		linesToSkip = 0;
	}

	commandLines = longLineSplitter( liveTypedCommand, 
									 canvas.width / fontSpacingH - 3 );
	

	drawY = canvas.height - 2 * fontSpacingV;

	if( commandLines.length > 1 ) {
		drawY -= fontSpacingV * ( commandLines.length - 1 );
	}
	drawString( ">", 10, drawY, "#FFFF00" );
	
	lineY = drawY;
	lineI = 0;
	
	commandLines.forEach(
		function( line ) {
			drawString( line, 10 + fontSpacingH, lineY, "#FFFF00" );
			if( lineI == commandLines.length - 1 ) {
				// last line of current command
				// put cursor at end
				drawCursor( 10 + fontSpacingH + fontSpacingH *
							line.length, lineY ); 
			}
			lineI ++;
			lineY += fontSpacingV;
		}
	);
	
	drawY -= fontSpacingV;

	for (var i = lineBuffer.length - 1; i >= linesToSkip; i--) {
		drawString( lineBuffer[i], 10, drawY );
		drawY -= fontSpacingV;
	}

	// add one character from one line to add
	if( linesToAdd.length > 0 && 
	  ( getMSTime() - charPrintingStartTime ) > charPrintingStepMS ) {
		charPrintingStartTime = getMSTime();
		
		if( linesToAddProgress[0] == 0 ) {
			lineBuffer.push( linesToAdd[0].substring( 0, 1 ) );
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
			}
		}
	}
	
	ctx.drawImage( scanLinesImg, 0, 0, 
				   scanLinesImg.width * drawScale,
				   scanLinesImg.height * drawScale );

	window.requestAnimationFrame(drawFrame);
}




function doKeyPress( e ) {
	if( e.keyCode >= 32 && e.keyCode < 126 ) {
		e.preventDefault();
		cursorFlashStartTime = getMSTime();
		c = String.fromCharCode( e.keyCode );
		liveTypedCommand = liveTypedCommand.concat( c );
	}
	else if( e.keyCode == 13 ) {
		cursorFlashStartTime = getMSTime();
		addLineToBuffer( liveTypedCommand );
		playSoundObjectSequence( beepSoundObj, liveTypedCommand.length,
								 charPrintingStepMS );
		liveTypedCommand = "";
	}		
}



function doKeyDown( e ) {
	if( e.keyCode == 8 ) {
		e.preventDefault();
		cursorFlashStartTime = getMSTime();
		if( liveTypedCommand.length > 0 ) {
			liveTypedCommand = 
				liveTypedCommand.substring( 0, liveTypedCommand.length - 1 );
		}
	}
}






var nextBeep = 0;
var beep = new Audio('beep.wav');





var scanLinesImg = new Image();
scanLinesImg.onload = function() {
	
	scanLinesLoaded = true;
};
scanLinesImg.src = 'scanLines.png';







beepSoundObj = loadSoundObject( "beep2.wav" );





