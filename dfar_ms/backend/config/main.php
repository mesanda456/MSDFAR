<?php
use backend\config\Constant;
use backend\config\UserTypeUtil;
use backend\models\ProfileOfficer;
use common\components\WebUser;
use common\models\User;
use kartik\grid\Module as GridViewModule;
use yii\caching\FileCache;
use yii\i18n\PhpMessageSource;
use yii\log\FileTarget;
use yii\rbac\DbManager;
use yii\web\Cookie;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Colombo',

    'modules' => [
        'gridview' => [
            'class' => GridViewModule::class,
        ],
    ],

    'components' => [
        'i18n' => [
            'translations' => [
                'app' => [
                    'class' => PhpMessageSource::class,
                    'sourceLanguage' => 'en',
                    'fileMap' => [
                        'app' => 'app.php',

                        // Corrected from error/php
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],

        'request' => [
            'csrfParam' => '_csrf-backend',

            'csrfCookie' => [
                'name' => '_csrf-backend',
                'path' => '/',
                'httpOnly' => true,
                'secure' => $isSecure,
                'sameSite' => Cookie::SAME_SITE_LAX,
            ],
        ],

    //    'response' => [
    //         'on beforeSend' => static function ($event) {
    //             $headers = $event->sender->headers;

    //             /*
    //             * Enforced clickjacking protection.
    //             */
    //             $headers->set('X-Frame-Options', 'DENY');

    //             $headers->set(
    //                 'Content-Security-Policy',
    //                 "frame-ancestors 'none';"
    //             );

    //             /*
    //             * CSP testing policy.
    //             * This policy reports violations but does not block them.
    //             */
    //             $headers->set(
    //                 'Content-Security-Policy-Report-Only',
    //                 implode('; ', [
    //                     "default-src 'self'",
    //                     "base-uri 'self'",
    //                     "object-src 'none'",
    //                     "frame-ancestors 'none'",
    //                     "form-action 'self'",
    //                     "script-src 'self'",
    //                     "style-src 'self' 'unsafe-inline'",
    //                     "img-src 'self' data: blob:",
    //                     "font-src 'self' data:",
    //                     "connect-src 'self'",
    //                 ]) . ';'
    //             );

    //             $headers->set(
    //                 'X-Content-Type-Options',
    //                 'nosniff'
    //             );

    //             $headers->set(
    //                 'Referrer-Policy',
    //                 'strict-origin-when-cross-origin'
    //             );
    //         },
    //     ],

        'user' => [
            'class' => WebUser::class,
            'identityClass' => User::class,

            /*
            * Automatic login functionality remains enabled.
            * Set the login duration to 0 in LoginForm if a persistent
            * remember-me cookie is not required.
            */
            'enableAutoLogin' => true,

            'identityCookie' => [
                'name' => '_identity-backend',
                'path' => '/',
                'httpOnly' => true,
                'secure' => $isSecure,
                'sameSite' => Cookie::SAME_SITE_LAX,
            ],
        ],

        'session' => [
            'name' => 'advanced-backend',

            'cookieParams' => [
                // Expire when the browser session ends.
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'secure' => $isSecure,
                'sameSite' => Cookie::SAME_SITE_LAX,
            ],
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,

            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/app.log',

                    // Prevent sensitive request/session details in logs.
                    'logVars' => [],

                    'maxFileSize' => 10240,
                    'maxLogFiles' => 10,
                ],
            ],
        ],

        'errorHandler' => [
            'errorAction' => 'error/index',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,

            'rules' => [
                /*
                 * Specific rules must come before generic rules.
                 */
                'inquiry/download/<id:\d+>' => 'inquiry/download',
                'api/v1/report-website' => 'api/v1/report-website',
                'api/v1/catch-production' => 'api/v1/catch-production',
                 '<alias:\w+>' =>
                    'site/<alias>',
                    
                '<controller:\w+>/<action:\w+>/<id:\d+>' =>
                    '<controller>/<action>',

                '<controller:\w+>/<action:\w+>' =>
                    '<controller>/<action>',

                '<controller:\w+>/<id:\d+>' =>
                    '<controller>/view',

               
            ],
        ],

        'authManager' => [
            'class' => DbManager::class,
        ],

        'cache' => [
            'class' => FileCache::class,
        ],
    
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'mail.fisheriesdept.gov.lk',
                'username' => 'departure1@fisheriesdept.gov.lk',
                'password' => 'DFAR@123654',
                'port' => '587',
                'encryption' => 'tls',
                'streamOptions' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ],
            ],
        ],
        'formatter' => [
            'class' => 'app\components\CustomFormatter',
        ],
    ],

    'on beforeRequest' => function ($event) {

   
    /*
     * Existing password-reset logic.
     */
    if (!Yii::$app->user->isGuest) {
        $officer = ProfileOfficer::findOne(
            Yii::$app->user->identity->profile_id
        );

        if (
            !empty($officer) &&
            !UserTypeUtil::hasType(Constant::FISHERMAN) &&
            !UserTypeUtil::hasType(Constant::ADMIN) &&
            $officer->force_reset_pw == 1
        ) {
            $route = Yii::$app->request->pathInfo;

            if (!in_array($route, [
                'logout',
                'officer/password-update',
            ], true)) {
                Yii::$app->response
                    ->redirect(['/officer/password-update'])
                    ->send();

                Yii::$app->end();
            }
        }
    }
},
    'params' => $params,
];
