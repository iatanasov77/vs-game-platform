const Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore
    .setOutputPath( 'public/admin-panel/build/custom-entries/' )
    .setPublicPath( '/build/custom-entries/' )
    
    .autoProvidejQuery()
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: true
    })
    .configureFilenames({
        js: '[name].js?[contenthash]',
        css: '[name].css?[contenthash]',
        assets: '[name].[ext]?[hash:8]'
    })
    .enableSingleRuntimeChunk()
    .enableVersioning( Encore.isProduction() )
    .enableSourceMaps( !Encore.isProduction() )
    
    .addAliases({
        '@': path.resolve( __dirname, '../../vendor/vankosoft/application/src/Vankosoft/ApplicationBundle/Resources/themes/default/assets' )
    })

    // Custom Entries
    /////////////////////////////////////////////////////////////////////////////////////////////////
    .addEntry( 'js/games-categories-edit', './assets/admin-panel/js/pages/games-categories-edit.js' )
    .addEntry( 'js/games-edit', './assets/admin-panel/js/pages/games-edit.js' )
    .addEntry( 'js/games', './assets/admin-panel/js/pages/games.js' )
;

const config = Encore.getWebpackConfig();
config.name = 'adminPanelCusstomEntries';

module.exports = config;
