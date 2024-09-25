window.MessagePublished = false;

function publishMessage( form, dto )
{
    window.MessagePublished = true;
    
    $.ajax({
        type: "POST",
        url: form.attr( 'action' ),
        data: JSON.stringify( dto ),
        dataType: 'json',
        success: function( response )
        {
            window.MessagePublished = false;
        },
        error: function()
        {
            window.MessagePublished = false;
            alert( "SYSTEM ERROR!!!" );
        }
    });    
}

$( function()
{
    const url           = window.clientSettings.socketPublisherUrl;
    const currentUser   = window.currentUser;
    //alert( url );
    
    var conn = new ab.Session( url,
        function() {
            conn.subscribe( 'chat', function( topic, data ) {
                let message = data.user + ': ' + data.message;
                let output;
                
                if ( currentUser == data.user ) {
                    output = '<span class="float-end">' + message + '</span><br /><br />';
                } else {
                    output = '<span class="float-start">' + message + '</span><br /><br />';
                }
                
                $( '#ChatConsole' ).append( output ).animate( {scrollTop: $( '#ChatConsole' ).prop( "scrollHeight" ) }, 0 );
            });
        },
        function() {
            console.warn( 'WebSocket connection closed' );
        },
        {'skipSubprotocolCheck': true}
    );

    $( '#FormClient1' ).on( 'submit', function( e ) {
        e.preventDefault();
        
        var form    = $( this );
        let dto = {
            user: form.find( 'input[name="user"]' ).val(),
            message: form.find( 'textarea[name="message"]' ).val()
        }
        
        if ( ! window.MessagePublished ) {
            publishMessage( form, dto );
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
            publishMessage( form, dto );
        }
    });
});