<?php namespace App\Form;

use Vankosoft\ApplicationBundle\Form\AbstractForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\GamePlayer;
use App\Entity\UserManagement\User;
use App\Entity\GameRoom;

class GamePlayerForm extends AbstractForm
{
    private $gameTypes  = [
        GamePlayer::TYPE_COMPUTER   => 'Computer',
        GamePlayer::TYPE_USER       => 'User',
    ];
    
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        parent::buildForm( $builder, $options );
        
        $entity = $builder->getData();
        
        $builder
            ->add( 'playerRooms', HiddenType::class, [
                'mapped'    => false,
                'data'      => \json_encode( $entity->getRooms()->getKeys() )
            ])
        
            ->add( 'user', EntityType::class, [
                'label'                 => 'game_platform.form.game_player.user',
                'placeholder'           => 'game_platform.form.game_player.user_placeholder',
                'translation_domain'    => 'GamePlatform',
                'required'              => false,
                'mapped'                => true,
                'class'                 => User::class,
                'choice_label'          => 'username',
            ])
            
            ->add( 'rooms', EntityType::class, [
                'label'                 => 'game_platform.form.game_player.rooms',
                'placeholder'           => 'game_platform.form.game_player.rooms_placeholder',
                'translation_domain'    => 'GamePlatform',
                'required'              => false,
                'mapped'                => true,
                'multiple'              => true,
                'class'                 => GameRoom::class,
                'choice_label'          => 'name',
            ])
            
            ->add( 'type', ChoiceType::class, [
                'label'                 => 'game_platform.form.game_player.type',
                'translation_domain'    => 'GamePlatform',
                'choices'               => \array_flip( $this->gameTypes ),
                'mapped'                => true,
            ])
            
            ->add( 'name', TextType::class, [
                'label'                 => 'vs_application.form.name',
                'translation_domain'    => 'VSApplicationBundle',
            ])
        ;
    }
    
    public function configureOptions( OptionsResolver $resolver ): void
    {
        parent::configureOptions( $resolver );
        
        $resolver->setDefaults([
            'csrf_protection'   => false,
            'data_class'        => GamePlayer::class
        ]);
    }
    
    public function getName(): string
    {
        return 'vs_project.game_player';
    }
}