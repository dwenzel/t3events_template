<?php

namespace CPSIT\T3eventsTemplate\Hooks;

/***************************************************************
 *  Copyright notice
 *  (c) 2017 Dirk Wenzel <dirk.wenzel@cps-it.de>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use CPSIT\T3eventsTemplate\DataHandling\Factory\EventRecordFactory;
use DWenzel\T3events\CallStaticTrait;
use TYPO3\CMS\Core\DataHandling\DataHandler as CoreDataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DataHandler
 */
class DataHandler
{
    use CallStaticTrait;

    const TEMPLATE_TABLE = 'tx_t3eventstemplate_domain_model_eventtemplate';
    const TARGET_TABLE = 'tx_t3events_domain_model_event';
    const DEFAULT_TYPE_FIELD = 'tx_extbase_type';

     /**
     * Template enabled record types
     * @var array
     */
    protected $templateEnabledTypes = [];

    /**
     * Field name which determines record type
     * @var string
     */
    protected $typeField = self::DEFAULT_TYPE_FIELD;

    /**
     * @var EventRecordFactory
     */
    protected $eventRecordFactory;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (isset($GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['templateEnabledTypes'])
            && is_array($GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['templateEnabledTypes'])
        ) {
            $this->templateEnabledTypes = $GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['templateEnabledTypes'];
        }
        if (isset($GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['type'])) {
            $this->typeField = $GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['type'];
        }
        $this->eventRecordFactory = $this->callStatic(
            GeneralUtility::class,
            'makeInstance',
            EventRecordFactory::class
        );
    }

    /**
     * Hook method for DataHandler hook processDatamap_beforeStart
     *
     * @param CoreDataHandler $parentObject
     */
    public function processDatamap_beforeStart($parentObject)
    {
        if (isset($parentObject->datamap[static::TARGET_TABLE])) {
            foreach ($parentObject->datamap[static::TARGET_TABLE] as $key => &$record) {
                if ($this->shouldProcessDatamapBeforeStart(static::TARGET_TABLE, $key, $record)) {
                    $this->eventRecordFactory->processNewRecord($record, $parentObject);
                }
            }
        }
    }

    /**
     * @param string $table
     * @param string $id
     * @param array $fieldArray
     * @return bool
     */
    protected function shouldProcessDatamapBeforeStart($table, $id, array $fieldArray)
    {
        $isNew = (strpos($id, 'NEW') !== false);
        $type = $fieldArray[$this->typeField];

        return
            $isNew
            && $table == static::TARGET_TABLE
            && (isset($fieldArray[$this->typeField])
                && array_key_exists($fieldArray[$this->typeField], $this->templateEnabledTypes))
            && isset($fieldArray['template'])
            && isset($this->templateEnabledTypes[$type]['copyFields']['new']);
    }
}
