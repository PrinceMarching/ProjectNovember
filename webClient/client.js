var canvas = document.createElement("canvas");
var ctx = canvas.getContext("2d");
canvas.width = 640;
canvas.height = 480;
document.body.appendChild(canvas);

canvas.style = "position: absolute; top: 0px; left: 0px; right: 0px; bottom: 0px; margin: auto; border:0px";


var fontArray = [];
var fontSpacing = 20;

function drawString( inString, inX, inY ) {
	a = Array.from( inString );
	x = inX
	a.forEach(
		function( c ) {
			i = c.charCodeAt( 0 );
			f = fontArray[ i ];
			ctx.drawImage( f, x, y );
			x += fontSpacing;
		}
	);
}


window.requestAnimationFrame(drawFrame);

var stringToDraw = "Hey there, punk!";
var stringToDrawProgress = 0;

function drawFrame() {
	ctx.fillStyle = '#000';
	ctx.fillRect( 0, 0, 640, 480 );
	drawString( stringToDraw.substring( 0, stringToDrawProgress ) , 10, 10 );
	stringToDrawProgress ++;
	window.requestAnimationFrame( drawFrame );
}


var fontImg = new Image();
fontImg.onload = function() {
	
	for( y=0; y<8; y++ ) {
		for( x=0; x<16; x++ ) {
			fontArray.push( 
				getClippedRegion( fontImg, x * 32, y * 64, 32, 64 ) );
		}
	}
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