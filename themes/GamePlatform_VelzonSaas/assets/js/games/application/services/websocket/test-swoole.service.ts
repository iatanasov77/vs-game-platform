import { Injectable } from '@angular/core';

// Action Interfaces
import {
    ActionDto,
    ActionNames,
    TestMessageActionDto,
    TestMessageDto
} from '@vankosoft/game-platform';

declare global {
    interface Window {
        gamePlatformSettings: any
    }
}

@Injectable({
    providedIn: 'root'
})
export class TestSwooleService
{
    socket: WebSocket | undefined;
    url: string = '';
    connectTime = new Date();
    
    constructor()
    {
        
    }
    
    connect(): void
    {
        // alert( 'Called Websocket Swoole Connect !!!' );
        
        if ( this.socket ) {
            this.socket.close();
        }
        
        this.url = window.gamePlatformSettings.socketSwooleTestUrl;
        const url = this.url;
        
        this.socket = new WebSocket( url );
        this.socket.onmessage   = this.onMessage.bind( this );
        this.socket.onerror     = this.onError.bind( this );
        this.socket.onopen      = this.onOpen.bind( this );
        this.socket.onclose     = this.onClose.bind( this );
    }
    
    onOpen(): void
    {
        // alert( 'Websocket Swoole Connection Opened !!!' );
        
        const now = new Date();
        const ping = now.getTime() - this.connectTime.getTime();
    }
    
    onMessage( message: MessageEvent<string> ): void
    {
        if ( ! message.data.length  ) {
            return;
        }
        
        const action = JSON.parse( message.data ) as ActionDto;
        
        switch ( action.actionName ) {
            case ActionNames.testMessage: {
                const dto = JSON.parse( message.data ) as TestMessageActionDto;
                alert( `Test Message: ${dto.message.message}` );
                
                break;
            }
            
            default:
                // alert( `Message Data: ${ message.data }` );
                throw new Error( `Action not implemented ${action.actionName}` );
        }
    }
    
    onError( event: Event ): void
    {
        console.error( 'Error', { event } );
    }
    
    onClose( event: CloseEvent ): void
    {
        //alert( event.code );
        console.log( 'Close', { event } );
    }
    
    sendMessage( message: string ): void
    {
        if ( this.socket && this.socket.readyState === this.socket.OPEN ) {
            this.socket.send( message );
        }
    }
    
    sendTestMessage( messageDto: TestMessageDto ): void
    {
        // alert( 'sendTestMessage' );
        
        const action: TestMessageActionDto = {
            actionName: ActionNames.testMessage,
            message: messageDto
        };
        this.sendMessage( JSON.stringify( action ) );
    }
}