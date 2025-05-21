export const context    =  {
    isProduction: IS_PRODUCTION,
    //backendURL: IS_PRODUCTION ? 'http://api.game-platform.vankosoft.org/api' : 'http://api.game-platform.lh/api',
    backendURL: IS_PRODUCTION ? PROD_API_URL : DEV_API_URL,
    themeBuildPath: THEME_BUILD_PATH
}
