<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
Yii::app()->TimeZone = "Asia/Calcutta";
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'Help Desk',
    'defaultController' => 'auth',
    // preloading 'log' component
    'preload' => array('log'),
    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.components.*',
        'ext.yii-mail.YiiMailMessage',
    ),
    'modules' => array(
        // uncomment the following to enable the Gii tool

        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => 'gii',
            // If removed, Gii defaults to localhost only. Edit carefully to taste.
            'ipFilters' => array('127.0.0.1', '::1'),
        ),
    ),
    // application components
    'components' => array(
        'user' => array(
            // enable cookie-based authentication
            'allowAutoLogin' => true,
        ),
        // uncomment the following to enable URLs in path-format
        'urlManager' => array(
            'urlFormat' => 'path',
//            'showScriptName' => false,
//            'caseSensitive' => false,
            'rules' => array(
                // '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                'dashboard' => 'auth/dashboard',
                'usersetting' => 'auth/usersetting',
                'usersetting/update_password' => 'auth/update_password',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id>/<num>' => '<controller>/<action>',
            ),
        ),
        'db' => array(
             //'class' => 'system.db.CDbConnection',
             'connectionString' => 'sqlite:' . dirname(__FILE__) . '/../data/testdrive.db',
            
           
        ),
        /* ('db'=>array(
          'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
          ), */
        // uncomment the following to use a MySQL database
// 'db' => array(
//            'connectionString' => 'mysql:host=localhost;dbname=psd2html',
//            'emulatePrepare' => true,
//            'username' => 'root',
//            'password' => '',
//            'charset' => 'utf8',
//        ),
        'errorHandler' => array(
            // use 'site/error' action to display errors
            'errorAction' => 'auth/error',
        ),
        'cache'=>array( 
				'class'=>'system.caching.CDbCache'
			),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning,info'
                ),
            // uncomment the following to show log messages on web pages
            /*
              array(
              'class'=>'CWebLogRoute',
              ),
             */
            ),
        ),
        'mail' => array(
            'class' => 'ext.mail.YiiMail',
            'transportType' => 'smtp',
            'transportOptions' => array(
                'host' => 'smtp.cisinlabs.com',
                'encryption' => 'ssl',
                'username' => 'bhaiyyalal.b@cisinlabs.com',
                'password' => 'cHxj0Naxbh',
                'port' => 465,
            ),
            'viewPath' => 'application.views.mail',
            'logging' => true,
            'dryRun' => false
        ),
//        'mail' => array(
//            'class' => 'ext.yii-mail.YiiMail',
//            'transportType' => 'php',
//            'viewPath' => 'application.views.mail',
//            'logging' => true,
//            'dryRun' => false
//        ),
    ),
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(
        // this is used in contact page
        'adminEmail' => 'bhaiyyalal.b@cisinlabs.com',
        // 'smshost' => '121.241.242.120',
        // 'smsport' => '8000',        
        'smtphost' => 'ssl:smtp.cisinlabs.com',
        'smtpusername' => 'bhaiyyalal.b@cisinlabs.com',
        'smtppassword' => 'cHxj0Naxbh'
    )
);


