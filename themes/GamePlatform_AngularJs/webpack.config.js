const Encore    = require('@symfony/webpack-encore');
const webpack   = require('webpack');
const path      = require('path');
const AngularCompilerPlugin = require('@ngtools/webpack').AngularWebpackPlugin;

Encore
    .setOutputPath( 'public/shared_assets/build/game-platform-angularjs/' )
    .setPublicPath( '/build/game-platform-angularjs/' )
  
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    
    .addAliases({
        '@': path.resolve( __dirname, '../../vendor/vankosoft/application/src/Vankosoft/ApplicationBundle/Resources/themes/default/assets' )
    })
    
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: true
    })
    
    /**
     * Configure Angular Compiler and Loader
     */
    .enableTypeScriptLoader()
    .addPlugin(new AngularCompilerPlugin({
        "tsConfigPath": './themes/WebGuitarPro_AngularJs/assets/js/Player/tsconfig.app.json',
        "entryModule": './themes/WebGuitarPro_AngularJs/assets/js/Player/main.ts',
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
         from: './themes/GamePlatform_AngularJs/assets/images',
         to: 'images/[path][name].[ext]',
     })
    
    .addStyleEntry( 'css/app', './themes/GamePlatform_AngularJs/assets/css/main.scss' )
    .addEntry( 'js/global', './themes/GamePlatform_AngularJs/assets/js/global.js' )
    
    .addEntry( 'js/app', './themes/GamePlatform_AngularJs/assets/js/app.js' )
    .addEntry( 'js/home', './themes/GamePlatform_AngularJs/assets/js/pages/home.js' )
    
    // Games
    .addEntry( 'js/bridge-belote', './themes/GamePlatform_AngularJs/assets/js/games/bridge-belote/index.js' )
    .addEntry( 'js/contract-bridge', './themes/GamePlatform_AngularJs/assets/js/games/contract-bridge/index.js' )
    .addEntry( 'js/chess', './themes/GamePlatform_AngularJs/assets/js/games/chess/index.js' )
    .addEntry( 'js/backgammon', './themes/GamePlatform_AngularJs/assets/js/games/backgammon/index.js' )
;

const config = Encore.getWebpackConfig();
config.name = 'GamePlatform_ReactJs';

config.resolve = {
    extensions: ['.ts', '.js']
};

config.plugins.push(
    new webpack.DefinePlugin({
        PRODUCTION: JSON.stringify( Encore.isProduction() ),
    })
);

module.exports = config;
