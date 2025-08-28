require( '@@/js/includes/resource-delete.js' );
require( 'jquery-easyui/css/easyui.css' );
require( 'jquery-easyui/js/jquery.easyui.min.js' );

import { VsRemoveDuplicates } from '@@/js/includes/vs_remove_duplicates.js';
import { EasyuiCombobox } from '@vankosoft/jquery-easyui-extensions/EasyuiCombobox.js';

$( function()
{
    let selectedPlayers  = JSON.parse( $( '#game_room_form_roomPlayers').val() );
    EasyuiCombobox( $( '#game_room_form_players' ), {
        required: true,
        multiple: true,
        checkboxId: "players",
        values: selectedPlayers
    });
    VsRemoveDuplicates();
});