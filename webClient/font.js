var fontLoaded = false;
var fontArray = [];


var baseFontSpacingH = 17;
var baseFontSpacingV = 40;


var fontImg = new Image();
fontImg.onload = function() {
	fontLoaded = true;
};
fontImg.src = 'font_computer.png';

var gridSpacing = 32;


// returned font is array of images indexed by ascii character indices
function getColoredFont( inColor ) {
	if( !( inColor in fontArray ) ) {
		fontColored = getColoredImage( fontImg, inColor );

		fontArray[ inColor ] = []
		for( y=0; y<8; y++ ) {
			for( x=0; x<16; x++ ) {
				fontArray[ inColor ].push( 
					getClippedRegion( fontColored, 
									  x * gridSpacing, 
									  y * gridSpacing, 
									  gridSpacing, 
									  gridSpacing ) );
			}
		}
	}
	return fontArray[ inColor ];
}



function getFontLoaded() {
	return fontLoaded;
}