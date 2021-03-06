// WebAudio API stuff



// Fix up prefixing
window.AudioContext = window.AudioContext || window.webkitAudioContext;
var aContext = new AudioContext();




// current time, for now
// set to end time of any playing sound
var audioPlayingUntilTime = aContext.currentTime;



// returns a sound object
function loadSoundObject( inURL ) {
	var request = new XMLHttpRequest();
	request.open('GET', inURL, true);
	request.responseType = 'arraybuffer';
	
	var newSoundObj = { loaded: false, buffer: null };

	// Decode asynchronously
	request.onload = function() {
		aContext.decodeAudioData( request.response, function(buffer) {
			newSoundObj.buffer = buffer;
			newSoundObj.loaded = true;
		} );
	}
	request.send();

	return newSoundObj;
}



// plays immediately after it's loaded
function loadSoundObjectAndPlay( inURL, inPlayingCallback ) {
	var request = new XMLHttpRequest();
	request.open('GET', inURL, true);
	request.responseType = 'arraybuffer';
	
	var newSoundObj = { loaded: false, buffer: null };

	// Decode asynchronously
	request.onload = function() {
		aContext.decodeAudioData( request.response, function(buffer) {
			newSoundObj.buffer = buffer;
			newSoundObj.loaded = true;
			
			playSoundObjectAtTime( newSoundObj, aContext.currentTime );
		
			if( inPlayingCallback != undefined ) {
				inPlayingCallback();
			}
		} );
	}
	
	// at least play callback on error
	request.onerror = function() {
		console.log( "Failed to load sound URL ".concat( inURL ) );
		if( inPlayingCallback != undefined ) {
			inPlayingCallback();
		}
	}

	request.send();
}




function playSoundObjectAtTime( inSoundObj, inTime ) {
	if( ! inSoundObj.loaded ) {
		return;
	}
	// creates a sound source
	var source = aContext.createBufferSource(); 
	
	// tell the source which sound to play
	source.buffer = inSoundObj.buffer;
	
	// connect the source to the context's destination (the speakers)
	source.connect( aContext.destination );
	// play the source at specified time
	source.start( inTime );

    let newEndTime = source.buffer.duration + inTime;
	
	if( newEndTime > audioPlayingUntilTime ) {
		audioPlayingUntilTime = newEndTime;
	}
}


function playSoundObjectSequence( inSoundObj, inPlayCount, inSpacingMS ) {
	var startTime = aContext.currentTime;
	for( i=0; i<inPlayCount; i++ ) {
		playSoundObjectAtTime( inSoundObj, startTime );
		startTime += inSpacingMS / 1000.0;
	}
}



function isSoundPlaying() {
	return aContext.currentTime < audioPlayingUntilTime;
}


