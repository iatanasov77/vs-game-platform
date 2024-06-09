const Encore    = require('@symfony/webpack-encore');
const webpack   = require('webpack');
const path      = require('path');
const AngularCompilerPlugin = require('@ngtools/webpack').AngularWebpackPlugin;
//const CompressionPlugin     = require( 'compression-webpack-plugin' );

Encore
    .setOutputPath( 'public/shared_assets/build/game-platform-mpa/' )
    .setPublicPath( '/build/game-platform-mpa/' )
  
    .enableSingleRuntimeChunk()
    //.addPlugin(new CompressionPlugin())
    
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    
    .addAliases({
        '@': path.resolve( __dirname, '../../vendor/vankosoft/application/src/Vankosoft/ApplicationBundle/Resources/themes/default/assets' ),
        '_@': path.resolve( __dirname, '../../assets/library' ),
    })
    
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: true
    })
    
    /**
     * Configure Angular Compiler and Loader
     */
    .enableTypeScriptLoader()
    .addPlugin(new AngularCompilerPlugin({
        "tsConfigPath": './themes/GamePlatform_MPA/assets/js/games/tsconfig.app.json',
    }))
    
    /* Embed Angular Component Templates. */
    .addLoader({
        test: /\.(html)$/,
        use: 'raw-loader',
    })
    
    /**
     * Add Entries
     */
     .autoProvidejQuery()
     .configureFilenames({
        js: '[name].js?[contenthash]',
        css: '[name].css?[contenthash]',
        assets: '[name].[ext]?[hash:8]'
    })
    
    .copyFiles({
         from: './assets/library/GamePlatform/Einaregilsson_Cards.Js/img',
         to: 'einaregilsson-cards.js/img/[path][name].[ext]',
     })
     
    .copyFiles({
         from: './themes/GamePlatform_MPA/assets/images',
         to: 'images/[path][name].[ext]',
     })
    
    .addStyleEntry( 'css/app', './themes/GamePlatform_MPA/assets/css/main.scss' )
    .addEntry( 'js/global', './themes/GamePlatform_MPA/assets/js/global.js' )
    
    .addEntry( 'js/app', './themes/GamePlatform_MPA/assets/js/app.js' )
    .addEntry( 'js/home', './themes/GamePlatform_MPA/assets/js/pages/home.js' )
    
    // Games
    .addEntry( 'js/game-platform', './themes/GamePlatform_MPA/assets/js/games/game-platform/index.js' )
    .addEntry( 'js/bridge-belote', './themes/GamePlatform_MPA/assets/js/games/bridge-belote/index.js' )
    .addEntry( 'js/contract-bridge', './themes/GamePlatform_MPA/assets/js/games/contract-bridge/index.js' )
    .addEntry( 'js/chess', './themes/GamePlatform_MPA/assets/js/games/chess/index.js' )
    .addEntry( 'js/backgammon', './themes/GamePlatform_MPA/assets/js/games/backgammon/index.js' )
;

Encore.configureDefinePlugin( ( options ) => {
    options.IS_PRODUCTION = JSON.stringify( Encore.isProduction() );
});

const config = Encore.getWebpackConfig();
config.name = 'GamePlatform_MPA';

config.resolve.extensions = ['.ts', '.js'];

module.exports = config;
