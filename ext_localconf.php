<?php
defined('TYPO3_MODE') or die();

// process data map hook
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['aue_events'] = 'CPSIT\\T3eventsTemplate\\Hooks\\DataHandler';
