<?php
defined('TYPO3_MODE') or die('Access denied.');
use CPSIT\T3eventsTemplate\Hooks\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$temporaryColumns = [
    'template' => [
        'exclude' => 1,
        'label' => $ll . 'tx_t3events_domain_model_event.template',
        'displayCond' => 'REC:NEW:true',
        'config' => [
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'tx_t3eventstemplate_domain_model_eventtemplate',
            'l10nmode' => 'mergeIfNotBlank',
            'size' => 1,
            'minitems' => 1,
            'maxitems' => 1,
            'eval' => 'required',
            'show_thumbs' => 0,
            'wizards' => [
                'suggest' => [
                    'type' => 'suggest',
                ],
            ],
        ],
    ],
];
// add type field if missing
if (!isset($GLOBALS['TCA']['tx_t3events_domain_model_event']['columns']['tx_extbase_type'])) {
    $temporaryColumns['tx_extbase_type'] = [
        'config' => [
            'label' => $ll . 'tx_t3events_domain_model_event.tx_extbase_type',
            'type' => 'select',
            'items' => [
                [$ll . 'label.tx_extbase_type.default', '1']
            ],
        ]
    ];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'tx_t3events_domain_model_event', 'tx_extbase_type', '', '');
}


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_t3events_domain_model_event',
    $temporaryColumns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_t3events_domain_model_event', 'template', '1', 'before:headline'
);
