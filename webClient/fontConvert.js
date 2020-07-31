// for processing fonts found here:
//  https://damieng.com/blog/tag/pixel-fonts/

var fontArray = [];


var fontImg = new Image();
fontImg.onload = function() {
	
	for( y=0; y<3; y++ ) {
		for( x=0; x<32; x++ ) {
			fontArray.push( 
				getClippedRegion( fontImg, 
								  x * 8, y * 8, 8, 8 ) );
		}
	}
	
	var canvasSub = document.createElement( 'canvas' ),
    ctxSub = canvasSub.getContext( '2d' );

	cellSize = 16;
    canvasSub.width = cellSize * 16;
    canvasSub.height = cellSize * 16;

	ctxSub.fillStyle = '#000';
	ctxSub.fillRect( 0, 0, canvasSub.width, canvasSub.height );

	for( y = 0; y < 16; y++ ) {
		for( x = 0; x < 16; x++ ) {
			i = y * 16 + x;
			
			if( i>=33 && i<=126 ) {
				fontI = i - 33;
				ctxSub.drawImage( fontArray[ fontI ], 
								  // center in cell
								  x * cellSize + cellSize / 4, 
								  y * cellSize + cellSize / 4, 
								  8, 8 );
			}
		}
	}
	url = canvasSub.toDataURL( "image/png" );
	window.open( url, '_blank');
};

fontImg.src = 'art/fontSource.png';
