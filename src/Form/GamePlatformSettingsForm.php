<?php namespace App\Form;

use Vankosoft\ApplicationBundle\Form\AbstractForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use App\Entity\GamePlatformSettings;

class GamePlatformSettingsForm extends AbstractForm
{
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        parent::buildForm( $builder, $options );
        
        $entity = $builder->getData();
        
        $builder
            ->add( 'settingsKey', TextType::class, [
                'label'                 => 'game_platform.form.game_platform_settings.settings_key',
                'translation_domain'    => 'GamePlatform',
            ])
            
            ->add( 'timeoutBetweenPlayers', IntegerType::class, [
                'label'                 => 'game_platform.form.game_platform_settings.timeout_between_players',
                'translation_domain'    => 'GamePlatform',
            ])
        ;
    }
    
    public function configureOptions( OptionsResolver $resolver ): void
    {
        parent::configureOptions( $resolver );
        
        $resolver
            ->setDefaults([
                'data_class'            => GamePlatformSettings::class,
                'csrf_protection'       => false,
            ])
        ;
    }
    
    public function getName()
    {
        return 'vsapp.game_platform_settings';
    }
}