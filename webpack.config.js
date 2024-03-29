var Encore = require( '@symfony/webpack-encore' );

/**
 *  AdminPanel Default Theme
 */
const themePath         = './vendor/vankosoft/application/src/Vankosoft/ApplicationBundle/Resources/themes/default';
const adminPanelConfig  = require( themePath + '/webpack.config' );

//=================================================================================================

/**
 *  AdminPanel Cusstom Entries
 */
Encore.reset();
const adminPanelCusstomEntriesConfig = require('./assets/admin-panel/webpack.config');

//=================================================================================================

/**
 *  GamePlatform AngularJs Theme
 */
Encore.reset();
const GamePlatform_AngularJs_Config   = require('./themes/GamePlatform_AngularJs/webpack.config');

//=================================================================================================


module.exports = [
    adminPanelConfig,
    adminPanelCusstomEntriesConfig,
    GamePlatform_AngularJs_Config
];
