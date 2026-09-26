//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// bin/game-platform fos:js-routing:dump --format=json --target=public/shared_assets/js/fos_js_routes_application.json
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
var routes  = require( '../../../../../public/shared_assets/js/fos_js_routes_application.json' );
import { VsPath } from '@/js/includes/fos_js_routes.js';

$( function()
{
    $( '#CreateGameRoom' ).on( 'click', function()
    {
        $.ajax({
            type: "GET",
            url: $( this ).attr( 'data-url' ),
            success: function( response )
            {
                $( '#CreateGameRoomFormContainer' ).html( response );
                
                /** Bootstrap 5 Modal Toggle */
                const myModal = new bootstrap.Modal( '#create-game-room-modal', {
                    keyboard: false
                });
                myModal.show( $( '#create-game-room-modal' ).get( 0 ) );
            },
            error: function()
            {
                alert( "SYSTEM ERROR!!!" );
            }
        });
    });
    
    $( '#btnSaveGameRoom' ).on( 'click', function ( e )
    {
        $( '#CreateGameRoomForm' ).submit();
    });
    
    $( '#ClearGameSessions' ).on( 'click', function()
    {
        
    });
    
    $( '.btnDeleteGameRoom' ).on( 'click', function()
    {
        $.ajax({
            type: "GET",
            url: $( this ).attr( 'data-url' ),
            success: function( response )
            {
                document.location = document.location;
            },
            error: function()
            {
                alert( "SYSTEM ERROR!!!" );
            }
        });
    });
});