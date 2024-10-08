const Encore    = require('@symfony/webpack-encore');
const webpack   = require('webpack');
const path      = require('path');
const AngularCompilerPlugin = require('@ngtools/webpack').AngularWebpackPlugin;

Encore
    .setOutputPath( 'public/shared_assets/build/gameplatform-velzonsaas-theme/' )
    .setPublicPath( '/build/gameplatform-velzonsaas-theme/' )
  
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    
    .addAliases({
        '@': path.resolve( __dirname, '../../vendor/vankosoft/application/src/Vankosoft/ApplicationBundle/Resources/themes/default/assets' ),
        //'@@': path.resolve( __dirname, '../../vendor/vankosoft/payment-bundle/lib/Resources/assets' ),
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
        "tsConfigPath": './themes/GamePlatform_VelzonSaas/assets/js/games/tsconfig.app.json',
    }))
    
    /* Embed Angular Component Templates. */
    .addLoader(
        {
            test: /\.(html)$/,
            use: 'raw-loader',
        },
        {
            test: /\.(xlf)$/,
            loader: 'raw-loader'
        }
    )

    /**
     * Add Entries
     */
    .autoProvidejQuery()
    
    .configureFilenames({
        js: '[name].js?[contenthash]',
        css: '[name].css?[contenthash]',
        assets: '[name].[ext]?[hash:8]'
    })

    // Application Images
    .copyFiles([
        {from: './assets/library/GamePlatform/Einaregilsson_Cards.Js/img', to: 'einaregilsson-cards.js/img/[path][name].[ext]'},
        {from: './themes/GamePlatform_VelzonSaas/assets/images', to: 'images/[path][name].[ext]'},
     ])
     
     // Velzon Images
    .copyFiles([
        //{from: './themes/GamePlatform_VelzonSaas/assets/vendor/Velzon_v4.2.0/lang', to: 'lang/[path][name].[ext]'},
        {from: './themes/GamePlatform_VelzonSaas/assets/vendor/Velzon_v4.2.0/fonts', to: 'fonts/[path][name].[ext]'},
        {from: './themes/GamePlatform_VelzonSaas/assets/vendor/Velzon_v4.2.0/images/flags', to: 'images/flags/[path][name].[ext]'},
        {from: './themes/GamePlatform_VelzonSaas/assets/vendor/Velzon_v4.2.0/images/users', to: 'images/users/[path][name].[ext]'},
    ])

    // Global Assets
    .addStyleEntry( 'css/app', './themes/GamePlatform_VelzonSaas/assets/css/app.scss' )
    .addEntry( 'js/layout', './themes/GamePlatform_VelzonSaas/assets/js/layout.js' )
    .addEntry( 'js/app', './themes/GamePlatform_VelzonSaas/assets/app.js' )
    .addEntry( 'js/app-login', './themes/GamePlatform_VelzonSaas/assets/app-login.js' )
    
    // Pages Assets
    .addEntry( 'js/authentication', './themes/GamePlatform_VelzonSaas/assets/js/pages/authentication.js' )
    .addEntry( 'js/edit-profile', './themes/GamePlatform_VelzonSaas/assets/js/pages/edit-profile.js' )
    //.addEntry( 'js/profile', './themes/GamePlatform_VelzonSaas/assets/js/pages/profile.js' )
    //.addEntry( 'js/pricing-plans', './themes/GamePlatform_VelzonSaas/assets/js/pages/pricing-plans.js' )
    .addEntry( 'js/home', './themes/GamePlatform_VelzonSaas/assets/js/pages/home.js' )
    .addEntry( 'js/games', './themes/GamePlatform_VelzonSaas/assets/js/pages/games.js' )
    
    // Games
    .addEntry( 'js/svara', './themes/GamePlatform_VelzonSaas/assets/js/games/svara/index.js' )
    .addEntry( 'js/bridge-belote', './themes/GamePlatform_VelzonSaas/assets/js/games/bridge-belote/index.js' )
    .addEntry( 'js/contract-bridge', './themes/GamePlatform_VelzonSaas/assets/js/games/contract-bridge/index.js' )
    .addEntry( 'js/chess', './themes/GamePlatform_VelzonSaas/assets/js/games/chess/index.js' )
    .addEntry( 'js/backgammon', './themes/GamePlatform_VelzonSaas/assets/js/games/backgammon/index.js' )
    
    // Test
    .addEntry( 'js/test-websocket', './themes/GamePlatform_VelzonSaas/assets/js/pages/test-websocket.js' )
    .addEntry( 'js/test-wamp', './themes/GamePlatform_VelzonSaas/assets/js/pages/test-wamp.js' )
;

Encore.configureDefinePlugin( ( options ) => {
    options.IS_PRODUCTION       = JSON.stringify( Encore.isProduction() );
    options.THEME_BUILD_PATH    = JSON.stringify( '/build/gameplatform-velzonsaas-theme' );
});

const config = Encore.getWebpackConfig();
config.name = 'GamePlatform_VelzonSaas';

config.resolve.extensions = ['.ts', '.js'];

module.exports = config;
