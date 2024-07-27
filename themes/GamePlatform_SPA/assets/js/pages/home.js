require( '../../vendor/theme/scripts.js' );

$( function()
{
	$( '.btnGame' ).on( 'click', function( e )
	{
	   e.preventDefault();
	   e.stopPropagation();
	   
	   document.location   = $( this ).attr( 'data-url' );
	});
});