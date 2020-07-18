#include "minorGems/game/game.h"
#include "minorGems/game/gameGraphics.h"
#include "minorGems/game/drawUtils.h"
#include "minorGems/util/stringUtils.h"



const char *getWindowTitle() {
    return "Project November";
    }




char doesOverrideGameImageSize() {
    return true;
    }


static int gameImageW = 640;
static int gameImageH = 480;


void getGameImageSize( int *outWidth, int *outHeight ) {
    *outWidth = gameImageW;
    *outHeight = gameImageH;
    }



char shouldNativeScreenResolutionBeUsed() {
    return true;
    }



char isNonIntegerScalingAllowed() {
    return true;
    }





const char *getAppName() {
    return "ProjectNovember";
    }


int getAppVersion() {
    return 1.0;
    }


const char *getLinuxAppName() {
    return "ProjectNovemberApp";
    }



char *getCustomRecordedGameData() {
    return stringDuplicate( "" );
    }


char showMouseDuringPlayback() {
    return true;
    }


char *getHashSalt() {
    return stringDuplicate( "another_loss" );
    }



const char *getFontTGAFileName() {
    return "font_32_64.tga";
    }



void drawString( const char *inString, char inForceCenter ) {
    // do nothing, for now
    }



void initDrawString( int inWidth, int inHeight ) {
    // do nothing, for now
    }


void freeDrawString() {
    // nothing
    }


char isDemoMode() {
    return false;
    }


const char *getDemoCodeSharedSecret() {
    return "fundamental_right";
    }


const char *getDemoCodeServerURL() {
    return "http://FIXME/demoServer/server.php";
    }



void initFrameDrawer( int inWidth, int inHeight, int inTargetFrameRate,
                      const char *inCustomRecordedGameData,
                      char inPlayingBack ) {
    // nothing for now
    setViewCenterPosition( 0, 0 );
    setViewSize( gameImageW );
    loadingComplete();
    }



void freeFrameDrawer() {
    }





void drawFrame( char inUpdate ) {

    setDrawColor( 1, 0, 0, 1 );
    doublePair pos = { 0, 0 };
    drawSquare( pos, 10 );
    }



void pointerMove( float inX, float inY ) {
    }

void pointerDown( float inX, float inY ) {
    }

void pointerDrag( float inX, float inY ) {
    }

void pointerUp( float inX, float inY ) {
    }



void keyDown( unsigned char inASCII ) {
    }

void keyUp( unsigned char inASCII ) {
    }


// key codes
#include "minorGems/graphics/openGL/KeyboardHandlerGL.h"

void specialKeyDown( int inKeyCode ) {
    }

void specialKeyUp( int inKeyCode ) {
    }






char getUsesSound() {
    return false;
    }



void hintBufferSize( int inLengthToFillInBytes ) {
    }

void freeHintedBuffers() {
    }


void getSoundSamples( Uint8 *inBuffer, int inLengthToFillInBytes ) {
    }


