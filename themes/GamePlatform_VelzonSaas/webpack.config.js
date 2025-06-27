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

    // Application Assets
    .copyFiles([
        {from: './assets/library/GamePlatform/Einaregilsson_Cards.Js/img', to: 'einaregilsson-cards.js/img/[path][name].[ext]'},
        {from: './themes/GamePlatform_VelzonSaas/assets/images', to: 'images/[path][name].[ext]'},
        {from: './themes/GamePlatform_VelzonSaas/assets/sound', to: 'sound/[path][name].[ext]'},
        {from: './themes/GamePlatform_VelzonSaas/assets/i18n', to: 'i18n/[path][name].[ext]'},
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
    .addEntry( 'js/backgammon-normal', './themes/GamePlatform_VelzonSaas/assets/js/games/backgammon-normal/index.js' )
    .addEntry( 'js/backgammon-gulbara', './themes/GamePlatform_VelzonSaas/assets/js/games/backgammon-gulbara/index.js' )
    .addEntry( 'js/backgammon-tapa', './themes/GamePlatform_VelzonSaas/assets/js/games/backgammon-tapa/index.js' )
;

if ( Encore.isDev() ) {
    require( 'dotenv' ).config( {path: '.env.dev'} );
} else {
    require( 'dotenv' ).config();
}

Encore.configureDefinePlugin( ( options ) => {
    options.IS_PRODUCTION       = JSON.stringify( Encore.isProduction() );
    options.THEME_BUILD_PATH    = JSON.stringify( '/build/gameplatform-velzonsaas-theme' );
    options.PROD_API_URL        = JSON.stringify( process.env.PROD_API_URL );
    options.DEV_API_URL         = JSON.stringify( process.env.DEV_API_URL );
});

const config = Encore.getWebpackConfig();
config.name = 'GamePlatform_VelzonSaas';

config.resolve.extensions = ['.ts', '.js'];

module.exports = config;
