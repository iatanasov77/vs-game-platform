var Encore = require( '@symfony/webpack-encore' );

/**
 *  AdminPanel Default Theme
 */
const themePath         = './vendor/vankosoft/application/src/Vankosoft/ApplicationBundle/Resources/themes/default';
const adminPanelConfig  = require( themePath + '/webpack.config' );

//=================================================================================================

/**
 *  GamePlatform Theme
 */
Encore.reset();
const GamePlatformThemeConfig   = require('./themes/GamePlatform/webpack.config');

//=================================================================================================


module.exports = [
    adminPanelConfig,
    //WebGuitarPro_ReactJs_Config,
    GamePlatformThemeConfig
];
