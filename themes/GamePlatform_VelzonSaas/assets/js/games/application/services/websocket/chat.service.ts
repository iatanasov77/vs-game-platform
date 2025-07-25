import { Injectable, Inject, OnDestroy } from '@angular/core';

import ChatMessageDto from '../../dto/chat/chatMessageDto';
import ChatUsersDto from '../../dto/chat/joinedChatDto';
import LeftChatDto from '../../dto/chat/leftChatDto';
import { AppStateService } from '../../state/app-state.service';

declare global {
    interface Window {
        gamePlatformSettings: any
    }
}

@Injectable({
    providedIn: 'root'
})
export class ChatService implements OnDestroy
{
    socket: WebSocket | undefined;
    
    constructor( @Inject( AppStateService ) private appState: AppStateService ) {}
    
    connect()
    {
        this.cleanup();
        this.socket = new WebSocket( this.getUri() );
        this.socket.onmessage = this.onMessage.bind( this );
        this.socket.onerror = this.onError.bind( this );
        this.socket.onopen = this.onOpen.bind( this );
        this.socket.onclose = this.onClose.bind( this );
    }
    
    getUri()
    {
        const user = this.appState.user.getValue();
        
        return `${window.gamePlatformSettings.socketChatUrl}?userId=${user.id}`;
    }
    
    onOpen( event: Event ): void
    {
        console.log( 'Open', { event } );
        // const now = new Date();
        // const ping = now.getTime() - this.connectTime.getTime();
        // this.appState.myConnection.setValue( { connected: true, pingMs: ping } );
    }
    
    onError( event: Event ): void
    {
        console.error( 'Error', { event } );
    }
    
    onClose( event: CloseEvent ): void
    {
        console.log( 'Close', { event } );
        // this.statusMessageService.setMyConnectionLost( event.reason );
    }
    
    // Messages received from server.
    onMessage( message: MessageEvent<string> ): void
    {
        const dto = JSON.parse( message.data );
        
        if ( dto.type === 'ChatMessageDto' ) {
            const msg = this.appState.chatMessages.getValue();
            this.appState.chatMessages.setValue( [...msg, dto] );
        } else if ( dto.type === 'ChatUsersDto' ) {
            const users = ( dto as ChatUsersDto ).users;
            this.appState.chatUsers.setValue( users );
        } else if ( dto.type === 'LeftChatDto' ) {
            const users = ( dto as LeftChatDto ).users;
            this.appState.chatUsers.setValue( users );
        }
    }
    
    // sending to server
    sendMessage( message: string )
    {
        const utcNow = new Date().toISOString();
        const dto: ChatMessageDto = {
            fromUser: this.appState.user.getValue().name,
            type: 'ChatMessageDto',
            message: message,
            utcDateTime: utcNow
        };
        
        if ( this.socket && this.socket.readyState === this.socket.OPEN ) {
            this.socket?.send( JSON.stringify( dto ) );
        }
    }
    
    ngOnDestroy(): void
    {
        this.cleanup();
    }
    
    private cleanup()
    {
        if ( this.socket && this.socket.readyState !== this.socket.CLOSED ) {
            this.socket.close();
        }
    }
    
    disconnect()
    {
        const users = [...this.appState.chatUsers.getValue()];
        const user = this.appState.user.getValue();
        if ( user ) {
          const index = users.indexOf( user.name );
          users.splice( index, 1 );
        }
        
        const dto: LeftChatDto = {
            users: users,
            type: 'LeftChatDto'
        };
        
        if ( this.socket?.readyState === this.socket?.OPEN ) {
            this.socket?.send( JSON.stringify( dto ) );
        }
    }
}
