require( '../../css/games.css' );

$( function()
{
	$( '.btnGame' ).on( 'click', function( e )
	{
	   e.preventDefault();
	   e.stopPropagation();
	   
	   document.location   = $( this ).attr( 'data-url' );
	});
});