<?php namespace App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollectionBuilder;

use Vankosoft\ApplicationBundle\Component\Application\Kernel as BaseKernel;

class GamePlatformKernel extends BaseKernel
{
    const VERSION   = '1.9.6';
    const APP_ID    = 'game-platform';
    
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\HttpKernel\Kernel::getProjectDir()
     * 
     * READ HERE: https://symfony.com/doc/current/reference/configuration/kernel.html#project-directory
     */
    public function getProjectDir(): string
    {
        return \dirname( __DIR__ );
    }
    
    protected function __getConfigDir(): string
    {
        return $this->getProjectDir() . '/config/applications/game-platform';
    }
}
