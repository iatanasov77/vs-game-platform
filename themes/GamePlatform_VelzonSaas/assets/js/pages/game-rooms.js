//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// bin/game-platform fos:js-routing:dump --format=json --target=public/shared_assets/js/fos_js_routes_application.json
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
var routes  = require( '../../../../../public/shared_assets/js/fos_js_routes_application.json' );
import { VsPath } from '@/js/includes/fos_js_routes.js';

$( function()
{
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