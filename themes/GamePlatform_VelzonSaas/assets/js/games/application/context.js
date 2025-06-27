export const context    =  {
    isProduction: IS_PRODUCTION,
    //backendURL: IS_PRODUCTION ? 'http://api.game-platform.vankosoft.org/api' : 'http://api.game-platform.lh/api',
    backendURL: IS_PRODUCTION ? PROD_API_URL : DEV_API_URL,
    themeBuildPath: THEME_BUILD_PATH
}

/*
 * For easier debugging in development mode, you can import the following file
 * to ignore zone related error stack frames such as `zone.run`, `zoneDelegate.invokeTask`.
 *
 * This import should be commented out in production mode because it will have a negative impact
 * on performance if an error is thrown.
 */
import 'zone.js/plugins/zone-error';  // Included with Angular CLI.
