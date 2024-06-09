const {context} = require( './context' );

export function RestangularConfigFactory ( RestangularProvider: any ) {
    RestangularProvider.setBaseUrl( context.backendURL );
    
    RestangularProvider.addResponseInterceptor( ( data: any, operation: any ) => {
        function populateHref( _data: any ) {
            if ( _data['@id'] ) {
                _data.href = _data['@id'].substring( 5 );
            }
        }
        populateHref( data );
        
        if ( ['get', 'getList'].includes( operation ) ) {
            const collectionResponse = data['hydra:member'];
            //console.log( data );
            
            if ( collectionResponse ) {
                collectionResponse.metadata = {};
                
                for ( const key in data ) {
                    if ( 'hydra:member' !== key && data.hasOwnProperty( key ) ) {
                        collectionResponse.metadata[key] = data[key];
                    }
                }
                
                for ( const element of collectionResponse ) {
                    populateHref( element );
                }
                
                return collectionResponse;
            }
        }
        
        return data;
    });
}
