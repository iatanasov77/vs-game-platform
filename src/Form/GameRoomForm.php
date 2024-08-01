<?php namespace App\Form;

use Vankosoft\ApplicationBundle\Form\AbstractForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\GameRoom;
use App\Entity\Game;
use App\Entity\UserManagement\User;

class GameRoomForm extends AbstractForm
{
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        parent::buildForm( $builder, $options );
        
        $entity = $builder->getData();
        
        $builder
            ->add( 'roomPlayers', HiddenType::class, [
                'mapped'    => false,
                'data'      => \json_encode( $entity->getPlayers()->getKeys() )
            ])
        
            ->add( 'game', EntityType::class, [
                'label'                 => 'game_platform.form.game_room.game',
                'placeholder'           => 'game_platform.form.game_room.game_placeholder',
                'translation_domain'    => 'GamePlatform',
                'required'              => true,
                'mapped'                => true,
                'class'                 => Game::class,
                'choice_label'          => 'title',
            ])
            
            ->add( 'players', EntityType::class, [
                'label'                 => 'game_platform.form.game_room.players',
                'placeholder'           => 'game_platform.form.game_room.players_placeholder',
                'translation_domain'    => 'GamePlatform',
                'required'              => true,
                'mapped'                => true,
                'multiple'              => true,
                'class'                 => User::class,
                'choice_label'          => 'email',
            ])
        ;
    }
    
    public function configureOptions( OptionsResolver $resolver ): void
    {
        parent::configureOptions( $resolver );
        
        $resolver->setDefaults([
            'data_class' => GameRoom::class
        ]);
    }
    
    public function getName(): string
    {
        return 'vs_project.game_room';
    }
}