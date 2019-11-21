<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_t3eventstemplate_domain_model_eventtemplate',
    'EXT:t3events_template/Resources/Private/Language/locallang_csh_tx_t3eventstemplate_domain_model_eventtemplate.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
    'tx_t3eventstemplate_domain_model_eventtemplate'
);
