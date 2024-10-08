window.MessagePublished = false;

const url           = window.clientSettings.socketPublisherUrl;
const currentUser   = window.currentUser;
var socket          = ;

function sendMessage( message )
{
    if ( socket && socket.readyState === socket.OPEN) {
        socket.send( message );
    }
}

function connectToTopic()
{
    socket = new WebSocket( "ws://127.0.0.1:8080/" );
}

$( function()
{
    if ( connection.isOpen == false ) {
        connectToTopic();
    }
        
    $( '#FormClient1' ).on( 'submit', function( e ) {
        e.preventDefault();
        
        var form    = $( this );
        let dto = {
            user: form.find( 'input[name="user"]' ).val(),
            message: form.find( 'textarea[name="message"]' ).val()
        }
        
        if ( ! window.MessagePublished ) {
            sendMessage( dto );
        }
    });
    
    $( '#FormClient2' ).on( 'submit', function( e ) {
        e.preventDefault();
        
        var form    = $( this );
        let dto = {
            user: form.find( 'input[name="user"]' ).val(),
            message: form.find( 'textarea[name="message"]' ).val()
        }
        
        if ( ! window.MessagePublished ) {
            sendMessage( dto );
        }
    });
});
