@Library( 'VankosoftGroovyLib' ) _

node ( label: 'php-host' ) {
    String[] environments       = ["production", "staging"]
    def HOST_PREFIX             = 'game-platform-'
    def APP_HOST                = 'game-platform.vankosoft.org'
    def BUILD_ENVIRONMENT
    def BRANCH_NAME
    def DO_BACKUP
    
    def REMOTE_SSH_HOST         = '164.138.221.242'
    def REMOTE_SSH_PORT         = '22'
    def REMOTE_SSH_USER         = 'root'
    def REMOTE_DIR
    
    final PHP_BIN               = '/usr/bin/php8.2'
    
    final GIT_CREDENTIALS_ID    = 'github-iatanasov77';
    final GIT_URI               = 'github.com/iatanasov77/vs-game-platform.git'
    final GIT_REPO_URL          = "https://${GIT_URI}"
    def GIT_REPO_WITH_CRED;
    
    def MYSQL_CREDENTIALS_ID    = 'vankosoft-mysql';
    def MYSQL_DATABASE_PREFIX   = 'c0GamePlatform_';
    def APP_MYSQL_USER;
    def APP_MYSQL_PASSWORD;
    def APP_MYSQL_DATABASE;
    def APP_DATABASE_URL;
    
    def CONFIG_TEMPLATE;
    def FTP_HOST                = 'ftp://164.138.221.242';
    def FTP_CREDENTIALS_ID      = 'gameplatform-ftp';
    def APP_DIR                 = '/opt/VankosoftProjects/GamePlatform';
    def APP_FTP_USER;
    def APP_FTP_PASSWORD;
    def APP_FTP_URL;
    
    stage( 'Configure Environement' ) {
    
        // Bind Git Credentials
        withCredentials([usernamePassword(credentialsId: "${GIT_CREDENTIALS_ID}", usernameVariable: 'USERNAME', passwordVariable: 'PASSWORD')]) {
            GIT_REPO_WITH_CRED = "https://$USERNAME:$PASSWORD@${GIT_URI}"
        }

        BUILD_ENVIRONMENT   = input message: 'Select Environment', ok: 'Proceed!',
                                    parameters: [choice(name: 'Select Environment', choices: "${environments.join('\n')}", description: 'What environment to build?')]
        
        switch( BUILD_ENVIRONMENT ) {
            case 'production':
                DO_BACKUP   = false
                
                def tags    = vankosoftJob.getGitTags( GIT_REPO_WITH_CRED )
                
                BRANCH_NAME = input message: 'Select Tag', ok: 'Proceed!',
                                    parameters: [choice(name: 'Select a Tag', choices: "${tags.join('\n')}", description: 'What tag to deploy?')]
                                
                break;
            default:
                DO_BACKUP       = false
                APP_HOST        = "${HOST_PREFIX}${BUILD_ENVIRONMENT}.vankosoft.org"  
                
                def branches    = vankosoftJob.getGitBranches( GIT_REPO_WITH_CRED )
                
                BRANCH_NAME     = input message: 'Select Branch', ok: 'Proceed!',
                                        parameters: [choice(name: 'Select a Branch', choices: "${branches.join('\n')}", description: 'What branch to deploy?')]
        }
        
        // Bind MySql Credentials
        withCredentials([usernamePassword(credentialsId: "${MYSQL_CREDENTIALS_ID}", usernameVariable: 'USERNAME', passwordVariable: 'PASSWORD')]) {            
            APP_MYSQL_USER      = "$USERNAME"
            APP_MYSQL_PASSWORD  = "$PASSWORD"
            APP_MYSQL_DATABASE  = "${MYSQL_DATABASE_PREFIX}${BUILD_ENVIRONMENT}"
    
            APP_DATABASE_URL="mysql://$APP_MYSQL_USER:$APP_MYSQL_PASSWORD@127.0.0.1:3306/$APP_MYSQL_DATABASE"
        }
        
        // Bind FTP Credentials
        withCredentials([usernamePassword(credentialsId: "${FTP_CREDENTIALS_ID}", usernameVariable: 'USERNAME', passwordVariable: 'PASSWORD')]) {
            //Using in Template Rendering
            APP_FTP_USER        = "$USERNAME"
            APP_FTP_PASSWORD    = "$PASSWORD"
            APP_FTP_URL         = "${FTP_HOST}/web/project/${BUILD_ENVIRONMENT}/"
            REMOTE_DIR          = "${APP_DIR}/${BUILD_ENVIRONMENT}"
        }
    }
    
    stage( 'Source Checkout' ) {
        if ( BUILD_ENVIRONMENT == 'production' ) {
            checkout([$class: 'GitSCM', 
                branches: [[name: "refs/tags/${BRANCH_NAME}"]], 
                userRemoteConfigs: [[
                    credentialsId: "${GIT_CREDENTIALS_ID}", 
                    refspec: '+refs/tags/*:refs/remotes/origin/tags/*', 
                    url: "${GIT_REPO_URL}"]]
            ])
        } else {
            git(
                url: "${GIT_REPO_URL}",
                credentialsId: "${GIT_CREDENTIALS_ID}",
                branch: "${BRANCH_NAME}"
            )
        }
    }
    
    stage( 'Build Application' ) {
        CONFIG_TEMPLATE = readFile( 'ftp_deploy.ini.template' )
        writeFile file: 'ftp_deploy.ini',
                text: vankosoftJob.renderTemplate( CONFIG_TEMPLATE, [
                    'environement': BUILD_ENVIRONMENT,
                    'url': APP_FTP_URL,
                    'user': APP_FTP_USER,
                    'password': APP_FTP_PASSWORD
                ])
        
        CONFIG_TEMPLATE = readFile( ".env.${BUILD_ENVIRONMENT}" )
        writeFile file: '.env',
                text: vankosoftJob.renderTemplate( CONFIG_TEMPLATE, [
                    'database_url': APP_DATABASE_URL,
                    'app_host': APP_HOST,
                ])
        
        sh """
            export COMPOSER_HOME='/home/vagrant/.composer';
            export COMPOSER_ALLOW_SUPERUSER=1;
            
            # https://www.makeuseof.com/javascript-heap-out-of-memory-error-fix/
            export NODE_OPTIONS='--max-old-space-size=2048';
            
            /usr/local/bin/phing install-${BUILD_ENVIRONMENT} -verbose -debug
        """
    }
    
    stage( 'Before Deploy (Create Backup on Hosting, Set Maintenance Mode etc.)' ) {
        if ( BUILD_ENVIRONMENT == 'production' ) {
            if ( DO_BACKUP ) {
                def now = new Date()
                
                script {
                    sshagent(credentials : ['vps-mini-ssh-root']) {
                        sh """
                            ssh -t -t -l ${REMOTE_SSH_USER} ${REMOTE_SSH_HOST} -o StrictHostKeyChecking=no -p ${REMOTE_SSH_PORT} << ENDSSH
                                cd ${REMOTE_DIR}
                                yes | cp -dRf ${REMOTE_DIR} ${REMOTE_DIR}_BACKUP
                                mysqldump -pg2Sx4,+WXwdQ ${DATABASE_PRODUCTION} > ${REMOTE_DIR}/../${DATABASE_PRODUCTION}_${now}.sql
                                #${PHP_BIN} -d memory_limit=-1 bin/console vankosoft:maintenance --set-maintenance
                                
                                returnCode=\$?   # Capture return code
                                exit \$returnCode
ENDSSH
                        """
                    }
                }
            }
        } else {
            echo "For environment 'staging' not needed nothing!"
        }
    }
    
    stage( 'Ftp Deploy' ) {
        sh """
            /usr/bin/php /usr/local/bin/ftpdeploy 'ftp_deploy.ini'
            
            returnCode=\$?   # Capture return code
            exit \$returnCode
        """
    }
    
    stage( 'After Deploy (Run migrations, etc.)' ) {
        if ( BUILD_ENVIRONMENT == 'production' ) {
            script {
                sshagent(credentials : ['vps-mini-ssh-root']) {
                    sh """
                        ssh -t -t -l ${REMOTE_SSH_USER} ${REMOTE_SSH_HOST} -o StrictHostKeyChecking=no -p ${REMOTE_SSH_PORT} << ENDSSH
                            cd ${REMOTE_DIR}
                            ${PHP_BIN} -d memory_limit=-1 bin/console --no-interaction doctrine:migrations:migrate
                            migrationCode=\$?   # Capture migration return code
                            
                            ${PHP_BIN} -d memory_limit=-1 bin/console cache:clear
                            ${PHP_BIN} -d memory_limit=-1 bin/game-platform cache:clear
                            ${PHP_BIN} -d memory_limit=-1 bin/game-platform-api cache:clear
                            
                            #${PHP_BIN} -d memory_limit=-1 bin/console vankosoft:maintenance --unset-maintenance
                            
                            #SETUP APPLICATION PERMISSIONS
                            chmod -R 0777 ${REMOTE_DIR}
                            
                            ${PHP_BIN} -d memory_limit=-1 bin/console vankosoft:install:info update
                            ${PHP_BIN} -d memory_limit=-1 bin/console vankosoft:load-widgets
                            
                            service websocket_game_platform_chat restart
                            service websocket_game_platform_game restart
                            
                            exit \$migrationCode
ENDSSH
                    """
                }
            }
        } else {
            script {
                sshagent(credentials : ['vps-mini-ssh-root']) {
                    sh """
                        ssh -t -t -l ${REMOTE_SSH_USER} ${REMOTE_SSH_HOST} -o StrictHostKeyChecking=no -p ${REMOTE_SSH_PORT} << ENDSSH
                            cd ${REMOTE_DIR}
                            ${PHP_BIN} -d memory_limit=-1 bin/console --no-interaction doctrine:migrations:migrate
                            migrationCode=\$?   # Capture migration return code
                            
                            ${PHP_BIN} -d memory_limit=-1 bin/console cache:clear
                            ${PHP_BIN} -d memory_limit=-1 bin/game-platform cache:clear
                            ${PHP_BIN} -d memory_limit=-1 bin/game-platform-api cache:clear
                            
                            #SETUP APPLICATION PERMISSIONS
                            chmod -R 0777 ${REMOTE_DIR}
                            
                            ${PHP_BIN} -d memory_limit=-1 bin/console vankosoft:install:info update
                            ${PHP_BIN} -d memory_limit=-1 bin/console vankosoft:load-widgets
                            
                            service websocket_game_platform_chat restart
                            service websocket_game_platform_game restart
                            
                            exit \$migrationCode
ENDSSH
                    """
                }
            }
        }
    }

}