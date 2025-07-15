import { Injectable } from '@angular/core';
import * as CryptoJS from 'crypto-js';

const { context } = require( '../context' );
const SECRET_KEY = 'your-secret-key';

@Injectable({
    providedIn: 'root'
})
export class LocalStorageService
{
    constructor() {}

    // Set item in local storage
    setItem( key: string, value: any ): void
    {
        try {
            const jsonValue = context.isProduction ?
                                CryptoJS.AES.encrypt( JSON.stringify( value ), SECRET_KEY ).toString() :
                                JSON.stringify( value );
            
            localStorage.setItem( key, jsonValue );
        } catch ( error ) {
            console.error( 'Error saving to local storage', error );
        }
    }
    
    // Get item from local storage
    getItem<T>( key: string ): T | null
    {
        try {
            let retValue = null;
            
            if ( context.isProduction ) {
                const encryptedValue = localStorage.getItem( key );
                if ( encryptedValue) {
                    const bytes = CryptoJS.AES.decrypt( encryptedValue, SECRET_KEY );
                    retValue = JSON.parse( bytes.toString( CryptoJS.enc.Utf8 ) );
                }
            } else {
                const value = localStorage.getItem( key );
                retValue = value ? JSON.parse( value ) : null;
            }
            
            return retValue;
        } catch (error) {
            console.error( 'Error reading from local storage', error );
            return null;
        }
    }
    
    // Remove item from local storage
    removeItem( key: string ): void
    {
        localStorage.removeItem( key );
    }
    
    // Clear all local storage
    clear(): void
    {
        localStorage.clear();
    }
}
