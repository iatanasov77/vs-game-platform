const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath( 'public/shared_assets/build/game-platform/' )
    .setPublicPath( '/build/game-platform/' )
  
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
         from: './themes/GamePlatform/assets/images',
         to: 'images/[path][name].[ext]',
     })
    
    .addStyleEntry( 'css/app', './themes/GamePlatform/assets/css/main.scss' )
    .addEntry( 'js/global', './themes/GamePlatform/assets/js/global.js' )
    
    .addEntry( 'js/app', './themes/GamePlatform/assets/js/app.js' )
    .addEntry( 'js/home', './themes/GamePlatform/assets/js/pages/home.js' )
;

const config = Encore.getWebpackConfig();
config.name = 'GamePlatformTheme';

module.exports = config;
