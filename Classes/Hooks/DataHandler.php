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
use DWenzel\T3events\CallStaticTrait;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
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
     * @var DatabaseConnection
     */
    protected $dataBase;

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
     * Constructor
     */
    public function __construct()
    {
        if (!$this->dataBase instanceof DatabaseConnection) {
            $this->dataBase = $GLOBALS['TYPO3_DB'];
        }
        if (isset($GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['templateEnabledTypes'])
            && is_array($GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['templateEnabledTypes'])
        ) {
            $this->templateEnabledTypes = $GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['templateEnabledTypes'];
        }
        if (isset($GLOBALS['TCA'][DataHandler::TARGET_TABLE]['ctrl']['type'])) {
            $this->typeField = $GLOBALS['TCA'][DataHandler::TARGET_TABLE]['ctrl']['type'];
        }
    }

    /**
     * Hook method for DataHandler hook processDatamap_beforeStart
     *
     * @param CoreDataHandler $parentObject
     */
    public function processDatamap_beforeStart($parentObject)
    {
        if (isset($parentObject->datamap[self::TARGET_TABLE])) {
            foreach ($parentObject->datamap[self::TARGET_TABLE] as $key => &$record) {
                if ($this->shouldProcessDatamapBeforeStart(self::TARGET_TABLE, $key, $record)) {
                    $this->processNewRecord($key, $record, $parentObject);
                }
            }
        }
    }

    /**
     * @param int $id Id of record
     * @param array $fieldArray Array of fields
     * @param CoreDataHandler $parentObject
     */
    protected function processNewRecord($id, &$fieldArray, $parentObject)
    {
        $type = $fieldArray[$this->typeField];
        $fieldListToCopy = $this->templateEnabledTypes[$type]['copyFields']['new'];
        $fieldsToCopy = GeneralUtility::trimExplode(',', $fieldListToCopy, true);
        if (!(bool)$fieldsToCopy) {
            return;
        }

        $templateParts = explode('_', $fieldArray['template']);
        $templateId = (int)array_pop($templateParts);
        $templateRecord = BackendUtility::getRecord(self::TEMPLATE_TABLE, $templateId);

        foreach ($fieldsToCopy as $fieldName) {
            if (isset($templateRecord[$fieldName])) {
                $fieldConfig = $GLOBALS['TCA'][self::TEMPLATE_TABLE]['columns'][$fieldName]['config'];
                if (!(bool)$fieldConfig) {
                    continue;
                }

                $this->copyFieldValue($fieldArray, $id, $fieldName, $fieldConfig, $templateRecord, $parentObject);
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
            && $table == self::TARGET_TABLE
            && isset($fieldArray[$this->typeField])
            && array_key_exists($fieldArray[$this->typeField], $this->templateEnabledTypes)
            && isset($fieldArray['template'])
            && isset($this->templateEnabledTypes[$type]['copyFields']['new']);
    }

    /**
     * Copies the value of a given template field to the fieldArray.
     * Non trivial fields (i.e. relation fields) have to be prepared to be suitable for DataHandler.
     *
     * @param array $fieldArray
     * @param int $targetId
     * @param string $fieldName
     * @param array $fieldConfig
     * @param array $sourceRecord
     * @param CoreDataHandler $parentObject
     */
    protected function copyFieldValue(&$fieldArray, $targetId, $fieldName, $fieldConfig, $sourceRecord, &$parentObject)
    {
        switch ($fieldConfig['type']) {
            case 'group':
                // todo implement method for group
                $fieldValue = '';
                break;
            case 'select':
                $fieldValue = $this->getValueForSelectField($fieldName, $fieldConfig, $sourceRecord);
                break;
            case 'inline':
                $fieldValue = $this->getValueForInlineField($fieldArray, $fieldName, $fieldConfig, $sourceRecord, $parentObject);
                break;
            default:
                $fieldValue = $sourceRecord[$fieldName];
        }
        $fieldArray[$fieldName] = $fieldValue;
    }

    /**
     * Retrieves a select field value from a source record. Its format depends on
     * the renderType of the field
     *
     * @param string $fieldName
     * @param array $fieldConfig
     * @param array $sourceRecord
     * @return string
     */
    protected function getValueForSelectField($fieldName, $fieldConfig, $sourceRecord)
    {
        $sourceRecordId = (int)$sourceRecord['uid'];

        $processedValue = BackendUtility::getProcessedValue(
            self::TEMPLATE_TABLE,
            $fieldName,
            $sourceRecord[$fieldName],
            0,
            false,
            true,
            $sourceRecordId
        );
        $idArr = GeneralUtility::trimExplode(';', $processedValue);

        switch ($fieldConfig['renderType']) {
            case 'selectMultipleSideBySide':
                $tableName = $fieldConfig['foreign_table'];
                foreach ($idArr as $key => $value) {
                    $idArr[$key] = $tableName . '_' . $value;
                }
                $fieldValue = implode(',', $idArr);
                break;
            case 'selectTree':
                $fieldValue = implode(',', $idArr);
                break;
            case 'selectSingle':
                $fieldValue = $sourceRecord[$fieldName];
                break;
            default:
                $fieldValue = '';
        }

        return $fieldValue;
    }

    /**
     * Retrieves an inline field value from a source record.
     *
     * @param array $fieldArray
     * @param string $fieldName
     * @param array $fieldConfig
     * @param array $sourceRecord
     * @param CoreDataHandler $parentObject
     * @return string
     * @internal param int $targetId
     */
    protected function getValueForInlineField(&$fieldArray, $fieldName, $fieldConfig, $sourceRecord, $parentObject)
    {
        $fieldValue = '';
        $sourceRecordId = (int)$sourceRecord['uid'];
        $foreignTable = $fieldConfig['foreign_table'];
        $foreignTableField = $fieldConfig['foreign_table_field'];

        if ($foreignTable && $GLOBALS['TCA'][$foreignTable]) {
            $whereClause = '';
            if (!empty($foreignTableField)) {
                $whereClause .= ' AND ' . $foreignTableField . ' = ' . $this->dataBase->fullQuoteStr(self::TEMPLATE_TABLE, $foreignTable);
            }
            // Add additional where clause if foreign_match_fields are defined
            $foreignMatchFields = is_array($fieldConfig['foreign_match_fields']) ? $fieldConfig['foreign_match_fields'] : [];
            foreach ($foreignMatchFields as $matchField => $matchValue) {
                $whereClause .= ' AND ' . $matchField . '=' . $this->dataBase->fullQuoteStr($matchValue, $foreignTable);
            }
            $sourceReferences = BackendUtility::getRecordsByField(
                $foreignTable, $fieldConfig['foreign_field'], $sourceRecordId, $whereClause
            );


            if (!(bool)$sourceReferences) {
                return $fieldValue;
            }

            $newReferences = [];
            foreach ($sourceReferences as $sourceReference) {
                $newReference = [
                    'uid_local' => $sourceReference['table_local'] . '_' . $sourceReference['uid_local'],
                    'pid' => $fieldArray['pid'],
                    'sys_language_uid' => $sourceReference['sys_language_uid'],
                ];
                $newReferences[uniqid('NEW')] = $newReference;
            }

            if (count($newReferences) > 0) {
                $parentObject->datamap[$foreignTable] = $newReferences;
                $fieldValue = implode(',', array_keys($newReferences));
            }
        }

        return $fieldValue;
    }
}
