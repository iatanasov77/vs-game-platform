const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath( 'public/shared_assets/build/game-platform-reactjs/' )
    .setPublicPath( '/build/game-platform-reactjs/' )
  
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: true
    })
    
    .enableReactPreset()
    
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
         from: './themes/GamePlatform_ReactJs/assets/images',
         to: 'images/[path][name].[ext]',
     })
    
    .addStyleEntry( 'css/app', './themes/GamePlatform_ReactJs/assets/css/main.scss' )
    .addEntry( 'js/global', './themes/GamePlatform_ReactJs/assets/js/global.js' )
    
    .addEntry( 'js/app', './themes/GamePlatform_ReactJs/assets/js/app.js' )
    .addEntry( 'js/home', './themes/GamePlatform_ReactJs/assets/js/pages/home.js' )
    
    // Games
    .addEntry( 'js/bridge-belote', './themes/GamePlatform_ReactJs/assets/js/games/bridge-belote/index.js' )
    .addEntry( 'js/contract-bridge', './themes/GamePlatform_ReactJs/assets/js/games/contract-bridge/index.js' )
    .addEntry( 'js/chess', './themes/GamePlatform_ReactJs/assets/js/games/chess/index.js' )
    .addEntry( 'js/backgammon', './themes/GamePlatform_ReactJs/assets/js/games/backgammon/index.js' )
;

const config = Encore.getWebpackConfig();
config.name = 'GamePlatform_ReactJs';

module.exports = config;
