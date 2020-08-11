var fontLoaded = false;
var fontArray = [];


var baseFontSpacingH = 17;
var baseFontSpacingV = 40;


fontPaths = [ 'font_computer.png', 'font_corrupt.png', 
			  'font_computer_inv.png' ];
fontEachLoaded = [ false, false, false ];
var fontImgs = [];


fontIndex = 0;
fontPaths.forEach(
	function( path ) {
		var im = new Image();
		im.fontIndex = fontIndex;
		fontIndex++;
		im.onload = function() {
			fontEachLoaded[ im.fontIndex ] = true;
			
			// check if they're all loaded now, and set main flag
			fontLoaded = true;
			fontEachLoaded.forEach( 
				function( flag ) {
					if( ! flag ) {
						fontLoaded = false;
					}
				}
			)
		};
		im.src = path;
		fontImgs.push( im );
		fontArray.push( [] );
	}
)
	


var gridSpacing = 32;


// returned font is array of images indexed by ascii character indices
function getColoredFont( inColor, inFontNumber ) {
	
	if( inFontNumber === undefined ) {
		inFontNumber = 0;
	}

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
	return fontArray[inFontNumber][ inColor ];
}



function getFontLoaded() {
	return fontLoaded;
}