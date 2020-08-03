var fontLoaded = false;
var fontArray = [];


var baseFontSpacingH = 17;
var baseFontSpacingV = 40;


fontPaths = [ 'font_computer.png', 'font_corrupt.png' ];
fontEachLoaded = [ false, false ];
var fontImgs = [];


fontIndex = 0;
fontPaths.forEach(
	function( path ) {
		im = new Image();
		im.fontIndex = fontIndex;
		console.log( im.fontIndex );
		fontIndex++;
		im.onload = function() {
			fontEachLoaded[ im.fontIndex ] = true;
			console.log( path.concat( " is loaded" ) );
			
			// FIXME:  why is this not different per image?
			console.log( im.fontIndex );
			// check if they're all loaded now, and set main flag
			fontLoaded = true;
			fontEachLoaded.forEach( 
				function( flag ) {
					if( ! flag ) {
						fontLoaded = false;
					}
				}
			)
			console.log( "ind = ".concat( im.fontIndex ).concat( " fontLoaded = ".concat( fontLoaded ) ) );
		};
		im.src = path;
		fontImgs.push( im );
		fontArray.push( [] );
	}
)
	


var gridSpacing = 32;


// returned font is array of images indexed by ascii character indices
function getColoredFont( inColor, inFontNumber = 0 ) {
	if( !( inColor in fontArray[ inFontNumber ] ) ) {
		fontColored = getColoredImage( fontImgs[ inFontNumber ], inColor );

		fontArray[inFontNumber][ inColor ] = []
		for( y=0; y<8; y++ ) {
			for( x=0; x<16; x++ ) {
				fontArray[inFontNumber][ inColor ].push( 
					getClippedRegion( fontColored, 
									  x * gridSpacing, 
									  y * gridSpacing, 
									  gridSpacing, 
									  gridSpacing ) );
			}
		}
	}
	console.log( "Got font ".concat( inFontNumber ).concat( " of color " ).concat( inColor ) );
	return fontArray[inFontNumber][ inColor ];
}



function getFontLoaded() {
	return fontLoaded;
}