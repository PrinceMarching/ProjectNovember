var fontLoaded = false;
var fontArray = [];


var baseFontSpacingH = 20;
var baseFontSpacingV = 40;


var fontImg = new Image();
fontImg.onload = function() {
	fontLoaded = true;
};
fontImg.src = 'font_32_64.png';


// returned font is array of images indexed by ascii character indices
function getColoredFont( inColor ) {
	if( !( inColor in fontArray ) ) {
		fontColored = getColoredImage( fontImg, inColor );

		fontArray[ inColor ] = []
		for( y=0; y<8; y++ ) {
			for( x=0; x<16; x++ ) {
				fontArray[ inColor ].push( 
					getClippedRegion( fontColored, 
									  x * 32, y * 64 + 32, 32, 32 ) );
			}
		}
	}
	return fontArray[ inColor ];
}



function getFontLoaded() {
	return fontLoaded;
}