<?php
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3_MODE') || die('Access denied.');

// set instance values
$site = [
    'sitenameBase' => 'TYPO3 Distribution',
    'defaultMailFromAddress' => 'no-reply@example.at',
    'defaultMailFromName' => 'TYPO3 Distribution',

];

// get complete context
$context = GeneralUtility::getApplicationContext()->__toString();

// alternative: set $context (please keep in mind that you also have to set the correct context for cli tasks)
//$context = 'Development/Production';

// check for "Production/Live/Server123" etc
if ($context) {
    list($contextMainPart, $contextSubPart1, $contextSubPart2) = explode('/', $context);
}
// project specific configuration
$customChanges = [
    'EXTCONF' => [
        'lang' => [
            'availableLanguages' => [
                'de',
            ],
        ],
    ],
    'EXT' => [
        'extConf' => [
            't3monitoring_client' => serialize([
                'secret' => 'Provide some secret password',
                'allowedIps' => '2a03:2a00:1100:2::ac10:29bc,172.17.0.1,188.94.251.75',
                'enableDebugForErrors' => 0,
            ]),
            'backend' => serialize([
                'loginLogo' => 'EXT:theme/Resources/Public/Images/Backend/LoginLogo.svg',
                'loginHighlightColor' => '#ff8700',
                'loginBackgroundImage' => 'EXT:theme/Resources/Public/Images/Backend/LoginBackground.png',
                'backendLogo' => 'EXT:backend/Resources/Public/Images/Backend/typo3-topbar@2x.png', // @TODO: Backend Logo (Stage, Prod, Dev)
                'backendFavicon' => 'EXT:backend/Resources/Public/Backend/Icons/favicon.ico', // @TODO: Backend FavIcon (Stage, Prod, Dev)
            ]),
            'fluid_styled_content' => serialize([
                'loadContentElementWizardTsConfig' => 1,
            ]),
            'realurl' => serialize([
                'configFile' => 'typo3conf/ext/theme/Resources/Private/Extension/Realurl/ManualConfiguration.php',
                'enableAutoConf' => 1,
                'autoConfFormat' => 0,
                'enableDevLog' => 0,
            ]),
//            'lfeditor' => serialize([
//                'viewLanguages' => 'de',
//                'defaultLanguage' => '',
//                'extIgnore' => '/^(CVS|.svn|.git|csh_)/',
//                'changeXlfDate' => 1,
//            ]),
//            @TODO: Activate PBKDF2 if project will not be downgraded to TYPO3 7LTS.
//            'saltedpasswords' => serialize([
//                'BE.' => [
//                    'saltedPWHashingMethod' => 'TYPO3\CMS\Saltedpasswords\Salt\Pbkdf2Salt',
//                    'forceSalted' => 0,
//                    'onlyAuthService' => 0,
//                    'updatePasswd' => 1,
//                ],
//                'FE.' => [
//                    'enabled' => 1,
//                    'saltedPWHashingMethod' => 'TYPO3\CMS\Saltedpasswords\Salt\Pbkdf2Salt',
//                    'forceSalted' => 0,
//                    'onlyAuthService' => 0,
//                    'updatePasswd' => 1,
//                ],
//            ]),
            'static_info_tables' => serialize([
                'enableManager' => 0
            ]),
        ],
    ],
    'FE' => [
        'versionNumberInFilename' => 'querystring',
        'disableNoCacheParameter' => true,
    ],
    'MAIL' => [
        'defaultMailFromAddress' => $site['defaultMailFromAddress'],
        'defaultMailFromName' => $site['defaultMailFromName'],
    ],
    'SYS' => [
        'sitename' => htmlspecialchars($site['sitenameBase']) . ' [' . $context . ']',
        'curlUse' => true,
        'textfile_ext' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['tsfile_ext'] . ',setupts,constantsts,ts1,tsc',
        'UTF8filesystem' => true,
        'clearCacheSystem' => true,
        'cookieSecure' => 2,
    ],
];

$GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS'], (array)$customChanges);

/*
 * include the most general file e.g. "AdditionalConfiguration_Staging.php
 */
$file = realpath(__DIR__) . '/AdditionalConfiguration_' . $contextMainPart . '.php';
if (is_file($file)) {
    include_once($file);
    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS'], (array)$customChanges);
}

/*
 * check for a more specific configuration as well e.g. "AdditionalConfiguration_Development_Profiling.php"
 */
$file = realpath(__DIR__) . '/AdditionalConfiguration_' . $contextMainPart . '_' . $contextSubPart1 . '.php';
if (is_file($file)) {
    include_once($file);
    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS'], (array)$customChanges);
}

/*
 * check for a more specific configuration as well, e.g. "AdditionalConfiguration_Production_Live_Server4.php"
 */
$file = realpath(__DIR__) . '/AdditionalConfiguration_' . $contextMainPart . '_' . $contextSubPart1
    . '_' . $contextSubPart2 . '.php';
if (is_file($file)) {
    include_once($file);
    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS'], (array)$customChanges);
}

/*
 * load custom configuration, that is not placed in git, e.g. for local development only changes
 */
if ($contextMainPart === 'Development') {
    $file = realpath(__DIR__) . '/AdditionalConfiguration_custom.php';
    if (is_file($file)) {
        include_once($file);
        $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS'], (array)$customChanges);
    }
}

/*
 * add composer autoloader
 */
$composerAutoloader = realpath(__DIR__ . '/../../vendor/autoload.php');

if (!empty($composerAutoloader) && is_file($composerAutoloader)) {
    include_once($composerAutoloader);
}
