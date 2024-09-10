/**
 * For Shared Multiple Applications Read:
 * ======================================
 * https://medium.com/disney-streaming/combining-multiple-angular-applications-into-a-single-one-e87d530d6527
 */

import { enableProdMode } from '@angular/core';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';

const {context} = require( '../application/context' );

import { BackgammonModule } from './backgammon.module';

if ( context.isProduction ) {
    enableProdMode();
}

platformBrowserDynamic().bootstrapModule( BackgammonModule )
                        .catch( ( err: any ) => console.error( err ) );
