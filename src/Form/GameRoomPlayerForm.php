<?php namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Component\GamePlatform;
use App\Entity\GamePlayer;

class GameRoomPlayerForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        $builder
            ->add( 'player', EntityType::class, [
                'label'                 => 'game_platform.form.game_room_player.player',
                'placeholder'           => 'game_platform.form.game_room_player.player_placeholder',
                'translation_domain'    => 'GamePlatform',
                'required'              => true,
                'class'                 => GamePlayer::class,
                'choice_label'          => 'name',
            ])
        ;
        
        if ( $options['gameType'] == GamePlatform::GAME_TYPE_BOARD_GAME ) {
            $builder
                ->add( 'color', ChoiceType::class, [
                    'label'                 => 'game_platform.form.game_room_player.player_color',
                    'placeholder'           => 'game_platform.form.game_room_player.player_color_placeholder',
                    'translation_domain'    => 'GamePlatform',
                    'choices'               => \array_flip( GamePlatform::GAME_PLAYER_COLOR ),
                ])
            ;
        } else {
            $builder
                ->add( 'position', ChoiceType::class, [
                    'label'                 => 'game_platform.form.game_room_player.player_position',
                    'placeholder'           => 'game_platform.form.game_room_player.player_position_placeholder',
                    'translation_domain'    => 'GamePlatform',
                    'choices'               => \array_flip( GamePlatform::GAME_PLAYER_POSITION ),
                ])
            ;
        }
    }
    
    public function configureOptions( OptionsResolver $resolver ): void
    {
        $resolver
            ->setDefaults([
                'csrf_protection'   => false,
                'gameType'          => GamePlatform::GAME_TYPE_BOARD_GAME,
            ])
        ;
    }
}
