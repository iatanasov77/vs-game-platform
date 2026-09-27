<?php namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\Game;

class GameRoomForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        $builder
            ->add( 'game', EntityType::class, [
                'label'                 => 'game_platform.form.game_room.game',
                'placeholder'           => 'game_platform.form.game_room.game_placeholder',
                'translation_domain'    => 'GamePlatform',
                'required'              => true,
                'class'                 => Game::class,
                'choice_label'          => 'title',
            ])
        ;
    }
    
    public function configureOptions( OptionsResolver $resolver ): void
    {
        $resolver
            ->setDefaults([
                'csrf_protection'   => false,
            ])
        ;
    }
}
