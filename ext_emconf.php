<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "t3events_template".
 *
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Event Templates',
	'description' => 'Event Templates extends t3events by templates.',
	'category' => 'plugin',
	'author' => 'Dirk Wenzel',
	'author_email' => 't3events@gmx.de',
	'state' => 'beta',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '0.1.0',
	'constraints' =>
	array (
		'depends' =>
		array (
			'typo3' => '8.7.99-9.5.99',
			't3events' => '0.31.0-0.0.0',
		),
		'conflicts' =>
		array (
		),
		'suggests' =>
		array (
		),
	),
	'_md5_values_when_last_written' => 'foo',
);

