var Encore = require( '@symfony/webpack-encore' );

/**
 *  AdminPanel Default Theme
 */
const themePath         = './vendor/vankosoft/application/src/Vankosoft/ApplicationBundle/Resources/themes/default';
const adminPanelConfig  = require( themePath + '/webpack.config' );

//=================================================================================================

/**
 *  GamePlatform ReactJs Theme
 */
Encore.reset();
const GamePlatform_ReactJs_Config   = require('./themes/GamePlatform_ReactJs/webpack.config');

//=================================================================================================

/**
 *  GamePlatform AngularJs Theme
 */
Encore.reset();
const GamePlatform_AngularJs_Config   = require('./themes/GamePlatform_AngularJs/webpack.config');

//=================================================================================================


module.exports = [
    adminPanelConfig,
    GamePlatform_ReactJs_Config,
    GamePlatform_AngularJs_Config
];
