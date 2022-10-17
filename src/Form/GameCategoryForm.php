<?php namespace App\Form;

use Vankosoft\ApplicationBundle\Form\AbstractForm;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\GameCategory;

class GameCategoryForm extends AbstractForm
{
	protected $requestStack;
    
    public function __construct(
        string $dataClass,
        RequestStack $requestStack
    ) {
        parent::__construct( $dataClass );
        
        $this->requestStack	= $requestStack;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    	parent::buildForm( $builder, $options );
        
        $entity         = $builder->getData();
        $currentLocale  = $this->requestStack->getCurrentRequest()->getLocale();
        
        $builder
        	->add( 'locale', ChoiceType::class, [
                'label'                 => 'vs_application.form.locale',
                'translation_domain'    => 'VSApplicationBundle',
                'choices'               => \array_flip( \Vankosoft\ApplicationBundle\Component\I18N::LanguagesAvailable() ),
                'data'                  => $currentLocale,
                'mapped'                => false,
            ])
            
            ->add( 'name', TextType::class, [
                'label'                 => 'vs_application.form.name',
                'translation_domain'    => 'VSApplicationBundle',
                'mapped'                => false,
            ])
            
            ->add( 'parent', EntityType::class, [
                'class'                 => GameCategory::class,
                'choice_label'          => 'name',
                'required'              => false,
                'label'                 => 'vs_application.form.parent_category',
                'placeholder'           => 'vs_application.form.parent_category_placeholder',
                'translation_domain'    => 'VSApplicationBundle',
                'mapped'                => false,
            ])
        ;
    }

    public function configureOptions( OptionsResolver $resolver ): void
    {
        parent::configureOptions( $resolver );
    }
    
    public function getName(): string
    {
        return 'vs_project.gamecategory';
    }
}
