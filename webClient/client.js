var canvas = document.createElement("canvas");
var ctx = canvas.getContext("2d");
canvas.width = 640;
canvas.height = 480;
document.body.appendChild(canvas);

canvas.style = "position: absolute; top: 0px; left: 0px; right: 0px; bottom: 0px; margin: auto; border:0px";



var fontLoaded = false;
var fontArray = [];
var fontSpacingH = 20;
var fontSpacingV = 40;

function drawString( inString, inX, inY ) {
	if( ! fontLoaded ) {
		return;
	}
	a = Array.from( inString );
	x = inX
	a.forEach(
		function( c ) {
			i = c.charCodeAt( 0 );
			f = fontArray[ i ];
			ctx.drawImage( f, x, inY );
			x += fontSpacingH;
		}
	);
}



var cursorFlashCount = 0;
var flashFrames = 40;
function drawCursor( inX, inY ) {
	cursorFlashCount ++;
	if( Math.floor( cursorFlashCount / flashFrames ) % 2 == 0 ) {
		drawString( "|", inX - 2, inY );
		drawString( "|", inX + 3, inY );
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

function drawFrame() {
	ctx.fillStyle = '#000';
	ctx.fillRect( 0, 0, 640, 480 );

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
	drawString( ">", 10, drawY );
	
	lineY = drawY;
	lineI = 0;
	
	commandLines.forEach(
		function( line ) {
			drawString( line, 10 + fontSpacingH, lineY );
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
	if( linesToAdd.length > 0 ) {
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
	window.requestAnimationFrame(drawFrame);
}




function doKeyPress( e ) {
	if( e.keyCode >= 32 && e.keyCode < 126 ) {
		cursorFlashCount = 0;
		c = String.fromCharCode( e.keyCode );
		liveTypedCommand = liveTypedCommand.concat( c );
	}
	else if( e.keyCode == 13 ) {
		cursorFlashCount = 0;
		addLineToBuffer( liveTypedCommand );
		liveTypedCommand = "";
	}
}



function doKeyDown( e ) {
	if( e.keyCode == 8 ) {
		cursorFlashCount = 0;
		if( liveTypedCommand.length > 0 ) {
			liveTypedCommand = 
				liveTypedCommand.substring( 0, liveTypedCommand.length - 1 );
		}
	}
}




var fontImg = new Image();
fontImg.onload = function() {
	
	for( y=0; y<8; y++ ) {
		for( x=0; x<16; x++ ) {
			fontArray.push( 
				getClippedRegion( fontImg, x * 32, y * 64, 32, 64 ) );
		}
	}
	fontLoaded = true;
};
fontImg.src = 'font_32_64.png';







function getClippedRegion( image, x, y, width, height) {
    var canvasSub = document.createElement( 'canvas' ),
    ctxSub = canvasSub.getContext( '2d' );

    canvasSub.width = width;
    canvasSub.height = height;

    //                   source region         dest. region
    ctxSub.drawImage(image, x, y, width, height,  0, 0, width, height);

    return canvasSub;
}