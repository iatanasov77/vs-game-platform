var Encore = require( '@symfony/webpack-encore' );

/**
 *  AdminPanel Velzon Theme
 */
Encore.reset();
const adminPanelVelzonConfig	= require( './themes/AdminPanel_VelzonChild/webpack.config' );

//=================================================================================================

/**
 *  GamePlatform AngularJs Theme
 */
Encore.reset();
const GamePlatform_VelzonSaas_Config   = require('./themes/GamePlatform_VelzonSaas/webpack.config');

//=================================================================================================

module.exports = [
    adminPanelVelzonConfig,
    GamePlatform_VelzonSaas_Config
];
