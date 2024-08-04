require( '@/js/includes/resource-delete.js' );
require( 'jquery-easyui/css/easyui.css' );
require( 'jquery-easyui/js/jquery.easyui.min.js' );

import { VsRemoveDuplicates } from '@/js/includes/vs_remove_duplicates.js';
import { EasyuiCombobox } from 'jquery-easyui-extensions/EasyuiCombobox.js';

$( function()
{
    let selectedRooms  = JSON.parse( $( '#game_player_form_playerRooms').val() );
    EasyuiCombobox( $( '#game_player_form_rooms' ), {
        required: false,
        multiple: true,
        checkboxId: "rooms",
        values: selectedRooms
    });
    VsRemoveDuplicates();
});