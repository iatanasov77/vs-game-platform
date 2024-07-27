<?php namespace App\Controller\AdminPanel;

use Symfony\Component\HttpFoundation\Request;
use Vankosoft\ApplicationBundle\Controller\AbstractCrudController;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use App\Entity\GamePicture;

class GameController extends AbstractCrudController
{
    protected function customData( Request $request, $entity = NULL ): array
    {
        $taxonomy   = $this->get( 'vs_application.repository.taxonomy' )->findByCode(
            $this->getParameter( 'vsapp.game-categories.taxonomy_code' )
        );
        
    	return [
    	    'taxonomyId'   => $taxonomy->getId(),
    	    'translations' => $this->classInfo['action'] == 'indexAction' ? $this->getTranslations() : [],
    	    'categories'   => $this->get( 'vsapp.repository.game_categories' )->findBy(['parent' => null])
    	];
    }
    
    protected function prepareEntity( &$entity, &$form, Request $request )
    {
        $pcr        = $this->get( 'vsapp.repository.game_categories' );
        $formPost   = $request->request->all( 'game_form' );
        
        if ( isset( $formPost['locale'] ) ) {
            $entity->setTranslatableLocale( $formPost['locale'] );
        }
        
        if ( isset( $formPost['category_taxon'] ) ) {
            $category   = $pcr->findOneBy( ['taxon' => $formPost['category_taxon']] );
            if ( $category ) {
                $entity->setCategory( $category );
            }
        }
        
        $gamePictureFile    = $form->get( 'picture' )->getData();
        if ( $gamePictureFile ) {
            $this->addGamePicture( $entity, $gamePictureFile );
        }
    }
    
    private function getTranslations()
    {
        $translations   = [];
        $transRepo      = $this->get( 'vs_application.repository.translation' );
        
        foreach ( $this->getRepository()->findAll() as $game ) {
            $translations[$game->getId()] = array_keys( $transRepo->findTranslations( $game ) );
        }
        
        return $translations;
    }
    
    private function addGamePicture( &$entity, File $file ): void
    {
        $uploadedFile   = new UploadedFile( $file->getRealPath(), $file->getBasename() );
        $gamePicture    = $entity->getPicture() ?: new GamePicture();
        
        $gamePicture->setOriginalName( $file->getClientOriginalName() );
        $gamePicture->setFile( $uploadedFile );
        
        $this->get( 'vs_application.app_pictures_uploader' )->upload( $gamePicture );
        $gamePicture->setFile( null ); // reset File Because: Serialization of 'Symfony\Component\HttpFoundation\File\UploadedFile' is not allowed
        
        if ( ! $entity->getPicture() ) {
            $entity->setPicture( $gamePicture );
        }
    }
}
