const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath( 'public/shared_assets/build/card-game/' )
    .setPublicPath( '/build/card-game/' )
  
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: true
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
         from: './themes/CardGame/assets/images',
         to: 'images/[path][name].[ext]',
     })
     
     .copyFiles([
        {from: './themes/CardGame/assets/images', to: 'images/[path][name].[ext]'},
        {from: './themes/CardGame/assets/js/library/GamePlatform/Einaregilsson_Cards.Js/img', to: 'einaregilsson-cards.js/img/[path][name].[ext]'}  
     ])
    
    .addStyleEntry( 'css/app', './themes/CardGame/assets/css/main.scss' )
    .addEntry( 'js/global', './themes/CardGame/assets/js/global.js' )
    
    .addEntry( 'js/app', './themes/CardGame/assets/js/app.js' )
    .addEntry( 'js/home', './themes/CardGame/assets/js/pages/home.js' )
    
    // Games
    .addEntry( 'js/bridge-belote', './themes/CardGame/assets/js/games/bridge-belote/index.js' )
    .addEntry( 'js/contract-bridge', './themes/CardGame/assets/js/games/contract-bridge/index.js' )
    .addEntry( 'js/chess', './themes/CardGame/assets/js/games/chess/index.js' )
    .addEntry( 'js/backgammon', './themes/CardGame/assets/js/games/backgammon/index.js' )
;

const config = Encore.getWebpackConfig();
config.name = 'CardGameTheme';

module.exports = config;
