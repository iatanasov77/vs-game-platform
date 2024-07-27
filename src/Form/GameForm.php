<?php namespace App\Form;

use Vankosoft\ApplicationBundle\Form\AbstractForm;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Component\Resource\Repository\RepositoryInterface;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

use App\Entity\Game;

class GameForm extends AbstractForm
{
	public function __construct(
        string $dataClass,
        RequestStack $requestStack,
        RepositoryInterface $localesRepository
    ) {
        parent::__construct( $dataClass );
        
        $this->requestStack         = $requestStack;
        $this->localesRepository    = $localesRepository;
    }
    
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
    	parent::buildForm( $builder, $options );
        
        $currentLocale  = $this->requestStack->getCurrentRequest()->getLocale();
        $builder
        	->add( 'locale', ChoiceType::class, [
                'label'                 => 'vs_cms.form.locale',
                'translation_domain'    => 'VSCmsBundle',
        	    'choices'               => \array_flip( $this->fillLocaleChoices() ),
                'data'                  => $currentLocale,
                'mapped'                => false,
            ])
            
            ->add( 'enabled', CheckboxType::class, [
                'label'                 => 'vs_application.form.enabled',
                'translation_domain'    => 'VSApplicationBundle',
            ])
            
            ->add( 'category_taxon', ChoiceType::class, [
                'label'                 => 'vs_application.form.category',
                'translation_domain'    => 'VSApplicationBundle',
                'required'              => false,
                'mapped'                => false,
                'placeholder'           => 'vs_application.form.category_placeholder',
            ])
            
            ->add( 'title', TextType::class, [
                'label'                 => 'vs_application.form.title',
                'translation_domain'    => 'VSApplicationBundle',
            ])
            
            ->add( 'picture', FileType::class, [
                'mapped'                => false,
                'required'              => false,
                
                'label'                 => 'vs_application.form.picture',
                'translation_domain'    => 'VSApplicationBundle',
                
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/gif',
                            'image/jpeg',
                            'image/png',
                            'image/svg+xml',
                        ],
                        'mimeTypesMessage' => 'vs_application.form.picture_invalid',
                    ])
                ],
            ])
            
            ->add( 'gameUrl', TextType::class, [
                'label'                 => 'game_platform.form.game.game_url',
                'translation_domain'    => 'GamePlatform',
                'required'              => false,
            ])
        ;
    }

    public function configureOptions( OptionsResolver $resolver ): void
    {
        parent::configureOptions( $resolver );
        
        $resolver->setDefaults([
            'data_class' => Game::class
        ]);
    }
    
    public function getName(): string
    {
        return 'vs_project.game';
    }
}
