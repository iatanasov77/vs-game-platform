<?php namespace App\Form;

use Vankosoft\ApplicationBundle\Form\AbstractForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\GamePlatformApplication;
use App\Entity\GamePlatformSettings;

class GamePlatformApplicationForm extends AbstractForm
{
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        parent::buildForm( $builder, $options );
        
        $builder
            ->add( 'applicationCode', HiddenType::class, ['mapped'    => false] )
            
            ->add( 'settings', EntityType::class, [
                'label'                 => 'game_platform.form.game_platform_settings_label',
                'translation_domain'    => 'GamePlatform',
                'placeholder'           => 'game_platform.form.game_platform_settings_placeholder',
                'class'                 => GamePlatformSettings::class,
                'choice_label'          => 'settingsKey',
            ])
        ;
    }
    
    public function configureOptions( OptionsResolver $resolver ): void
    {
        parent::configureOptions( $resolver );
        
        $resolver
            ->setDefaults([
                'data_class'        => GamePlatformApplication::class,
                'csrf_protection'   => false,
            ])
        ;
    }
    
    public function getName()
    {
        return 'vsapp.game_platform_application';
    }
}