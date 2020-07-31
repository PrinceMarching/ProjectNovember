



function getClippedRegion( image, x, y, width, height) {
    var canvasSub = document.createElement( 'canvas' ),
    ctxSub = canvasSub.getContext( '2d' );

    canvasSub.width = width;
    canvasSub.height = height;

    //                   source region         dest. region
    ctxSub.drawImage(image, x, y, width, height,  0, 0, width, height);

    return canvasSub;
}



function getColoredImage( image, inColor ) {
    var canvasSub = document.createElement( 'canvas' ),
    ctxSub = canvasSub.getContext( '2d' );

    canvasSub.width = image.width;
    canvasSub.height = image.height;

    ctxSub.drawImage(image, 0, 0);

	ctxSub.globalAlpha=1.0
	ctxSub.globalCompositeOperation="source-in";
	ctxSub.fillStyle = inColor;
	ctxSub.fillRect( 0, 0, image.width, image.height );

    return canvasSub;
}
