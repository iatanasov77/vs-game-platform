/**
 * For Shared Multiple Applications Read:
 * ======================================
 * https://medium.com/disney-streaming/combining-multiple-angular-applications-into-a-single-one-e87d530d6527
 */

import { enableProdMode } from '@angular/core';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';

const {context} = require( '../context' );

import { BridgeBeloteModule } from './bridge-belote.module';

if ( context.isProduction ) {
    enableProdMode();
}

platformBrowserDynamic().bootstrapModule(BridgeBeloteModule)
                        .catch( ( err: any ) => console.error( err ) );
