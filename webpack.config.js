var Encore = require( '@symfony/webpack-encore' );

/**
 *  AdminPanel Default Theme
 */
const themePath         = './vendor/vankosoft/application/src/Vankosoft/ApplicationBundle/Resources/themes/default';
const adminPanelConfig  = require( themePath + '/webpack.config' );

//=================================================================================================

/**
 *  AdminPanel Velzon Theme
 */
Encore.reset();
const adminPanelVelzonConfig	= require( './themes/AdminPanel_VelzonChild/webpack.config' );

//=================================================================================================

/**
 *  GamePlatform AngularJs Theme
 */
// Encore.reset();
// const GamePlatform_MPA_Config   = require('./themes/GamePlatform_MPA/webpack.config');

//=================================================================================================

/**
 *  GamePlatform AngularJs Theme
 */
Encore.reset();
const GamePlatform_SPA_Config   = require('./themes/GamePlatform_SPA/webpack.config');

//=================================================================================================


module.exports = [
    adminPanelConfig,
    adminPanelVelzonConfig,
    //GamePlatform_MPA_Config,
    GamePlatform_SPA_Config
];
