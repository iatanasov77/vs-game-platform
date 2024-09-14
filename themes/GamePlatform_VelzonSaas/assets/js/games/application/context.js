export const context    =  {
    isProduction: IS_PRODUCTION,
    backendURL: IS_PRODUCTION ? 'http://api.game-platform.vankosoft.org/api' : 'http://api.game-platform.lh/api',
    socketServiceUrl: IS_PRODUCTION ? 'ws://game-platform.vankosoft.org:8090' : 'ws://myprojects.lh:8090',
    themeBuildPath: THEME_BUILD_PATH
}
