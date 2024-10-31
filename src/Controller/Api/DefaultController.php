<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vankosoft\UsersBundle\Security\SecurityBridge;
use Vankosoft\ApiBundle\Security\ApiManager;
use Vankosoft\ApplicationBundle\Component\Status;

class DefaultController extends AbstractController
{
    /** @var SecurityBridge */
    protected $vsSecurityBridge;
    
    /** @var ApiManager */
    protected $apiManager;
    
    public function __construct(
        SecurityBridge $vsSecurityBridge,
        ApiManager $apiManager
    ) {
        $this->vsSecurityBridge = $vsSecurityBridge;
        $this->apiManager       = $apiManager;
    }
    
    public function getTranslationsAction( $locale, Request $request ): Response
    {
        switch ( $locale ) {
            case 'bg_BG':
                $translations   = $this->getBulgarianTranslations();
                break;
            default:
                $translations   = $this->getEnglishTranslations();
        }
        
        return new JsonResponse( $translations );
    }
    
    public function getVerifySignatureAction( Request $request ): Response
    {
        $user                   = $this->vsSecurityBridge->getUser();
        $signatureComponents    = null;
        if ( $user ) {
            $signatureComponents    = $this->apiManager->getVerifySignature( $user, 'vs_api_login_by_signature' );
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'signedUrl' => $signatureComponents ? $signatureComponents->getSignedUrl() : null,
        ]);
    }
    
    private function getEnglishTranslations():array
    {
        return [
            'dialogs.close'                                 => 'Close',
            'dialogs.login'                                 => 'Login',
            'dialogs.not_loggedin_message'                  => 'You are NOT Logged In.',
            'dialogs.have_an_account_question'              => 'if you have an account',
            'dialogs.login_link'                            => 'login from here',
            'dialogs.not_an_account_question'               => 'if you have not an account',
            'dialogs.create_account_link'                   => 'create from here',
            
            'dialogs.has_no_player_message'                 => 'You have NOT a Player',
            'dialogs.create_a_player_question'              => 'You can create a Player',
            'dialogs.create_player_link'                    => 'from here',
            
            'dialogs.select_game_room'                      => 'Select Game Room',
            'forms.select_game_room.room_label'             => 'Room',
            'forms.select_game_room.room_placeholder'       => '-- Select a Game Room --',
            'forms.select_game_room.submit'                 => 'Select Room',
            
            'dialogs.create_game_room'                      => 'Create Game Room',
            'forms.create_game_room.opponent_label'         => 'Opponent',
            'forms.create_game_room.opponent_placeholder'   => '-- Select an Opponent --',
            'forms.create_game_room.submit'                 => 'Create Room',
            
            'game_board.statistics.we'                      => 'We',
            'game_board.statistics.you'                     => 'You',
            
            'game_board.select_room'                        => 'Select Room',
            'game_board.create_room'                        => 'Create Room',
            'game_board.play_with_computer'                 => 'Play with Computer',
            'game_board.play_with_friends'                  => 'Play with Friends',
            'game_board.start_game'                         => 'Start Game',
            'game_board.game'                               => 'Game',
            
            'game_board.acceptDoubling'                     => 'Accept',
            'game_board.resign'                             => 'Resign',
            'game_board.help'                               => 'Help',
            'game_board.done'                               => 'Done',
            'game_board.undo'                               => 'Undo',
            'game_board.roll'                               => 'Roll',
            'game_board.new_game'                           => 'New Game',
            'game_board.exit_game'                          => 'Exit Game',
            
            'game_board.players.title'                      => 'Players',
            'game_board.rooms.title'                        => 'Game Rooms',
            
            // Play AI Question Box
            'playaiquestion.icantfind'                      => 'I can\'t find anyone else playing at the moment.',
            'playaiquestion.doyouwanttoplay'                => 'Do you want to play against the AI?',
            'playaiquestion.playai'                         => 'Play AI',
            'playaiquestion.keepwaiting'                    => 'Keep waiting',
            
            // Status Messages
            'statusmessage.yourturn'                        => 'Your turn to move.  ({{color}})',
            'statusmessage.waitingfor'                      => 'Waiting for {{color}} to move.',
            'statusmessage.youareblocked'                   => 'You are blocked. Click "Done"',
            'statusmessage.noconnection'                    => 'No server connection',
            'statusmessage.opponentconnectionlost'          => 'Opponent connection lost',
            'statusmessage.waitingoppcnn'                   => 'Waiting for opponent to connect',
            'statusmessage.newscore'                        => 'New score {{ score }} ({{ increase }})',
            'statusmessage.youwon'                          => 'Congrats! You won. {{score}}',
            'statusmessage.youlost'                         => 'Sorry. You lost the game. {{score}}',
            'statusmessage.movenow'                         => 'Move now or lose!',
            'statusmessage.dblaccepted'                     => 'Opponent did accept. Your turn to move.',
            'statusmessage.dblrequested'                    => 'Double your bet or give up.',
            'statusmessage.waitfordblresponse'              => 'Waiting for opponent to respond.',
            
            'chat.entermessage'                             => 'Chat here',
            
            // Game Global Messages
            'backgammon'                                    => 'Backgammon',
            'black'                                         => 'black',
            'white'                                         => 'white',
            "pleasewait"                                    => "Please wait",
        ];
    }
    
    private function getBulgarianTranslations():array
    {
        return [
            'dialogs.close'                                 => 'Close',
            'dialogs.login'                                 => 'Login',
            'dialogs.not_loggedin_message'                  => 'You are NOT Logged In.',
            'dialogs.have_an_account_question'              => 'if you have an account',
            'dialogs.login_link'                            => 'login from here',
            'dialogs.not_an_account_question'               => 'if you have not an account',
            'dialogs.create_account_link'                   => 'create from here',
            
            'dialogs.has_no_player_message'                 => 'You have NOT a Player',
            'dialogs.create_a_player_question'              => 'You can create a Player',
            'dialogs.create_player_link'                    => 'from here',
            
            'dialogs.select_game_room'                      => 'Select Game Room',
            'forms.select_game_room.room_label'             => 'Room',
            'forms.select_game_room.room_placeholder'       => '-- Select a Game Room --',
            'forms.select_game_room.submit'                 => 'Select Room',
            
            'dialogs.create_game_room'                      => 'Create Game Room',
            'forms.create_game_room.opponent_label'         => 'Room',
            'forms.create_game_room.opponent_placeholder'   => '-- Select a Game Room --',
            'forms.create_game_room.submit'                 => 'Create Room',
            
            'game_board.statistics.we'                      => 'Ние',
            'game_board.statistics.you'                     => 'Вие',
            
            'game_board.select_room'                        => 'Select Room',
            'game_board.create_room'                        => 'Create Room',
            'game_board.play_with_computer'                 => 'Play with Computer',
            'game_board.play_with_friends'                  => 'Play with Friends',
            'game_board.start_game'                         => 'Start Game',
            'game_board.game'                               => 'Game',
            
            'game_board.acceptDoubling'                     => 'Accept',
            'game_board.resign'                             => 'Resign',
            'game_board.help'                               => 'Help',
            'game_board.done'                               => 'Done',
            'game_board.undo'                               => 'Undo',
            'game_board.roll'                               => 'Roll',
            'game_board.new_game'                           => 'New Game',
            'game_board.exit_game'                          => 'Exit Game',
            
            'game_board.players.title'                      => 'Players',
            'game_board.rooms.title'                        => 'Game Rooms',
            
            // Play AI Question Box
            'playaiquestion.icantfind'                      => 'I can\'t find anyone else playing at the moment.',
            'playaiquestion.doyouwanttoplay'                => 'Do you want to play against the AI?',
            'playaiquestion.playai'                         => 'Play AI',
            'playaiquestion.keepwaiting'                    => 'Keep waiting',
            
            // Status Messages
            'statusmessage.yourturn'                        => 'Your turn to move.  ({{color}})',
            'statusmessage.waitingfor'                      => 'Waiting for {{color}} to move.',
            'statusmessage.youareblocked'                   => 'You are blocked. Click "Done"',
            'statusmessage.noconnection'                    => 'No server connection',
            'statusmessage.opponentconnectionlost'          => 'Opponent connection lost',
            'statusmessage.waitingoppcnn'                   => 'Waiting for opponent to connect',
            'statusmessage.newscore'                        => 'New score {{ score }} ({{ increase }})',
            'statusmessage.youwon'                          => 'Congrats! You won. {{score}}',
            'statusmessage.youlost'                         => 'Sorry. You lost the game. {{score}}',
            'statusmessage.movenow'                         => 'Move now or lose!',
            'statusmessage.dblaccepted'                     => 'Opponent did accept. Your turn to move.',
            'statusmessage.dblrequested'                    => 'Double your bet or give up.',
            'statusmessage.waitfordblresponse'              => 'Waiting for opponent to respond.',
            
            'chat.entermessage'                             => 'Chat here',
            
            // Game Global Messages
            'backgammon'                                    => 'Backgammon',
            'black'                                         => 'black',
            'white'                                         => 'white',
            "pleasewait"                                    => "Please wait",
        ];
    }
}
