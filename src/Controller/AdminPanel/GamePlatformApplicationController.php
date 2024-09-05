<?php namespace App\Controller\AdminPanel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vankosoft\ApplicationBundle\Controller\AbstractCrudController;

class GamePlatformApplicationController extends AbstractCrudController
{
    protected function customData( Request $request, $entity = NULL ): array
    {
        return [
            'applications'      => $this->get( 'vs_application.repository.application' )->findAll(),
            'applicationCode'   => $request->query->get( 'applicationCode', '' )
        ];
    }
    
    protected function prepareEntity( &$entity, &$form, Request $request )
    {
        $formData       = $request->request->all( 'game_platform_application_form' );
        $application    = $this->get( 'vs_application.repository.application' )
                                ->findOneBy( ['code' => $formData['applicationCode']] );
        
        $entity->setApplication( $application );
    }
}