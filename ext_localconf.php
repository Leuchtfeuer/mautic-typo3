<?php

defined('TYPO3') || die;

call_user_func(function (): void {
    if (\TYPO3\CMS\Core\Core\Environment::isComposerMode() === false) {
        $filePath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mautic') . 'Libraries/vendor/autoload.php';
        if (@file_exists($filePath)) {
            require_once $filePath;
        } else {
            throw new \Exception(sprintf('File %s does not exist. Dependencies could not be loaded.', $filePath), 7049493518);
        }
    }

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('marketing_automation') === false) {
        throw new \Exception('Required extension is not loaded: EXT:marketing_automation.', 7616907311);
    }

    $marketingDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Leuchtfeuer\MarketingAutomation\Dispatcher\Dispatcher::class);
    $marketingDispatcher->addSubscriber(\Leuchtfeuer\Mautic\Slot\MauticSubscriber::class);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mautic/Configuration/PageTS/Mod/Wizards/NewContentElement.tsconfig">'
    );

    // TODO
    //if (TYPO3_MODE === 'FE') {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postTransform']['mautic_tag'] =
            \Leuchtfeuer\Mautic\Hooks\MauticTagHook::class . '->setTags';
    //}

    //##################
    //       FORM      #
    //##################
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Leuchtfeuer\Mautic\Form\FormDataProvider\MauticFormDataProvider::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues::class,
        ],
        'before' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
        ],
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1530047235] = [
        'nodeName' => 'updateSegmentsControl',
        'priority' => 30,
        'class' => \Leuchtfeuer\Mautic\FormEngine\FieldControl\UpdateSegmentsControl::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1551778913] = [
        'nodeName' => 'updateTagsControl',
        'priority' => 30,
        'class' => \Leuchtfeuer\Mautic\FormEngine\FieldControl\UpdateTagsControl::class,
    ];

    //#################
    //   FAL DRIVER   #
    //#################
    $driverRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Driver\DriverRegistry::class);
    $driverRegistry->registerDriverClass(
        \Leuchtfeuer\Mautic\Driver\AssetDriver::class,
        \Leuchtfeuer\Mautic\Driver\AssetDriver::DRIVER_SHORT_NAME,
        \Leuchtfeuer\Mautic\Driver\AssetDriver::DRIVER_NAME,
        'FILE:EXT:mautic/Configuration/FlexForm/AssetDriver.xml'
    );

    //#################
    //   EXTRACTOR    #
    //#################
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::class)->registerExtractionService(\Leuchtfeuer\Mautic\Index\Extractor::class);

    //##################
    //      PLUGIN     #
    //##################
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Mautic',
        'Form',
        [\Leuchtfeuer\Mautic\Controller\FrontendController::class => 'form'],
        [\Leuchtfeuer\Mautic\Controller\FrontendController::class => 'form'],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    //##################
    //      ICONS      #
    //##################
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $icons = [
        'tx_mautic-mautic-icon' => 'EXT:mautic/Resources/Public/Icons/Extension.svg',
    ];

    foreach ($icons as $identifier => $source) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => $source]
        );
    }

    //##################
    //     EXTCONF     #
    //##################
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'] = [
            'transformation' => [
                'form' => [],
                'formField' => [],
            ],
        ];
    }

    //######################
    // FORM TRANSFORMATION #
    //######################
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['form']['mautic_finisher_campaign_prototype'] = \Leuchtfeuer\Mautic\Transformation\Form\CampaignFormTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['form']['mautic_finisher_standalone_prototype'] = \Leuchtfeuer\Mautic\Transformation\Form\StandaloneFormTransformation::class;

    //#######################
    // FIELD TRANSFORMATION #
    //#######################
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['AdvancedPassword'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Checkbox'] = \Leuchtfeuer\Mautic\Transformation\FormField\CheckboxTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['ContentElement'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Date'] = \Leuchtfeuer\Mautic\Transformation\FormField\DatetimeTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['DatePicker'] = \Leuchtfeuer\Mautic\Transformation\FormField\DatetimeTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Email'] = \Leuchtfeuer\Mautic\Transformation\FormField\EmailTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['GridRow'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Fieldset'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['FileUpload'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Hidden'] = \Leuchtfeuer\Mautic\Transformation\FormField\HiddenTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['ImageUpload'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['MultiCheckbox'] = \Leuchtfeuer\Mautic\Transformation\FormField\MultiCheckboxTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['MultiSelect'] = \Leuchtfeuer\Mautic\Transformation\FormField\MultiSelectTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Number'] = \Leuchtfeuer\Mautic\Transformation\FormField\NumberTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Page'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Password'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['RadioButton'] = \Leuchtfeuer\Mautic\Transformation\FormField\RadioButtonTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['SingleSelect'] = \Leuchtfeuer\Mautic\Transformation\FormField\SingleSelectTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['StaticText'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['SummaryPage'] = \Leuchtfeuer\Mautic\Transformation\FormField\IgnoreTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Telephone'] = \Leuchtfeuer\Mautic\Transformation\FormField\TelephoneTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Text'] = \Leuchtfeuer\Mautic\Transformation\FormField\TextTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Textarea'] = \Leuchtfeuer\Mautic\Transformation\FormField\TextareaTransformation::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['Url'] = \Leuchtfeuer\Mautic\Transformation\FormField\UrlTransformation::class;

    // Register custom field transformation classes
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic']['transformation']['formField']['CountryList'] = \Leuchtfeuer\Mautic\Transformation\FormField\CountryListTransformation::class;

    //##################
    //     LOGGING     #
    //##################
    // Turn logging off by default
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Leuchtfeuer']['Mautic'] = [
        'writerConfiguration' => [
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                \TYPO3\CMS\Core\Log\Writer\NullWriter::class => [],
            ],
        ],
    ];

    if (\TYPO3\CMS\Core\Core\Environment::getContext()->isDevelopment()) {
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['Leuchtfeuer']['Mautic'] = [
            'writerConfiguration' => [
                \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                        'logFileInfix' => 'mautic',
                    ],
                ],
            ],
        ];
    }
});
