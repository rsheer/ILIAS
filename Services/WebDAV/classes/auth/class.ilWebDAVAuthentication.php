<?php

class ilWebDAVAuthentication
{
    public function authenticate($a_username, $a_password)
    {
        global $ilUser;
        file_put_contents('webdav_auth.txt', "Try to login with usrname: '$a_username' and a password with length of " . strlen($a_password) . "\n", FILE_APPEND);
        if($GLOBALS['DIC']['ilAuthSession']->isAuthenticated())
        {
            ilLoggerFactory::getLogger('init')->debug('User session is valid');
            //var_dump($ilUser);die;
            return true;
        }
        
        //var_dump(debug_backtrace());die;
        include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentialsHTTP.php';
        $credentials = new ilAuthFrontendCredentialsHTTP();
        $credentials->setUsername($a_username);
        $credentials->setPassword($a_password);
        
        include_once './Services/Authentication/classes/Provider/class.ilAuthProviderFactory.php';
        $provider_factory = new ilAuthProviderFactory();
        $providers = $provider_factory->getProviders($credentials);
        
        include_once './Services/Authentication/classes/class.ilAuthStatus.php';
        $status = ilAuthStatus::getInstance();
        
        include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendFactory.php';
        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_HTTP);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
            $status,
            $credentials,
            $providers
            );

        $frontend->authenticate();
        
        switch($status->getStatus())
        {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('auth')->debug('Authentication successful. Serving request');
                ilLoggerFactory::getLogger('auth')->info('Authenticated user id: ' . $GLOBALS['DIC']['ilAuthSession']->getUserId());
                ilLoggerFactory::getLogger('auth')->debug('Auth info authenticated: ' .$GLOBALS['DIC']['ilAuthSession']->isAuthenticated());
                ilLoggerFactory::getLogger('auth')->debug('Auth info expired: ' .$GLOBALS['DIC']['ilAuthSession']->isExpired());
                return true;
                
            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                ilLoggerFactory::getLogger('auth')->debug('Authentication failed; Account migration required.');
                return false;
                
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                ilLoggerFactory::getLogger('auth')->debug('Authentication failed; Wrong login, password.');
                return false;
        }
        
        return false;
    }
}