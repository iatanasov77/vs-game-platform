( function( $ ) {
    $.fn.moveTo = function( selector ) {
        return this.each( function() {
            /* CLONE NOT WORK AS EXPECTED
             *----------------------------
            var cl = $( this ).clone();
            $( cl ).appendTo( selector );
            
            $( this ).remove();
            //$( this ).replaceWith( cl );
            */
            
            $( this ).appendTo( selector );
        });
    };
})( jQuery );
