<?php

namespace CPSIT\T3eventsTemplate\DataHandling\Factory;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Dirk Wenzel <wenzel@cps-it.de>
 *  All rights reserved
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the text file GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use DWenzel\T3events\CallStaticTrait;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class InlineFieldCopier
 * Copies inline fields.
 */
class InlineFieldCopier implements FieldCopierInterface, InitializeInterface
{
    use CallStaticTrait;

    /**
     * @var string
     */
    protected $templateTable;

    /**
     * @var DatabaseConnection
     */
    protected $dataBase;

    /**
     * Initialize copier by config
     *
     * @param array $config
     * @return void
     */
    public function initialize($config)
    {
        if (isset($config['templateTable'])) {
            $this->templateTable = $config['templateTable'];
        }
    }

    /**
     * Gets the new field value
     * @param $record
     * @param array $fieldConfig
     * @param array $sourceRecord
     * @param string|null $fieldName
     * @param DataHandler $dataHandler
     * @return string
     */
    public function getValue($record, $fieldConfig, $sourceRecord, $fieldName = null, $dataHandler)
    {
        $fieldValue = '';
        $foreignTable = $fieldConfig['foreign_table'];

        $sourceReferences = $this->getSourceReferences($fieldConfig, $sourceRecord, $foreignTable);

        if (empty($sourceReferences)) {
            return $fieldValue;
        }

        $newReferences = $this->getNewReferences($record, $sourceReferences);

        if (count($newReferences) > 0) {
            $dataHandler->datamap[$foreignTable] = $newReferences;
            $fieldValue = implode(',', array_keys($newReferences));
        }
        return $fieldValue;
    }

    /**
     * Gets the records referenced in source record
     * @param $fieldConfig
     * @param $sourceRecord
     * @param $foreignTable
     * @return mixed
     */
    protected function getSourceReferences($fieldConfig, $sourceRecord, $foreignTable)
    {
        $sourceReferences = [];
        $sourceRecordId = (int)$sourceRecord['uid'];

        $foreignField = 'uid_foreign';
        if (isset($fieldConfig['foreign_field'])) {
            $foreignField = $fieldConfig['foreign_field'];
        }

        if ($foreignTable && $GLOBALS['TCA'][$foreignTable]) {
            $whereClause = '';
            if (isset($fieldConfig['foreign_table_field'])) {
                $foreignTableField = $fieldConfig['foreign_table_field'];
            }
            if (!empty($foreignTableField)) {
                $whereClause .= ' AND ' . $foreignTableField . ' = '
                    . $this->getDataBase()->fullQuoteStr($this->templateTable, $foreignTable);
            }
            // Add additional where clause if foreign_match_fields are defined
            $foreignMatchFields = is_array($fieldConfig['foreign_match_fields']) ? $fieldConfig['foreign_match_fields'] : [];
            foreach ($foreignMatchFields as $matchField => $matchValue) {
                $whereClause .= ' AND ' . $matchField . '=' . $this->getDataBase()->fullQuoteStr($matchValue, $foreignTable);
            }

            $sourceReferences = $this->callStatic(
                BackendUtility::class,
                'getRecordsByField',
                $foreignTable, $foreignField, $sourceRecordId, $whereClause
            );
        }

        return $sourceReferences;
    }

    /**
     * @return DatabaseConnection
     */
    public function getDataBase()
    {
        if (!$this->dataBase instanceof DatabaseConnection) {
            $this->dataBase = $GLOBALS['TYPO3_DB'];
        }

        return $this->dataBase;
    }

    /**
     * Gets an array of references in
     * the syntax required for the datamap of the core DataHandler
     * @param $record
     * @param $sourceReferences
     * @return array
     */
    protected function getNewReferences($record, $sourceReferences)
    {
        $newReferences = [];
        foreach ($sourceReferences as $sourceReference) {
            $newReference = $sourceReference;
            unset($newReference['uid']);
            if (isset($sourceReference['table_local']) && isset($sourceReference['uid_local'])) {
                $localUid = $sourceReference['uid_local'];
                $localTable = $sourceReference['table_local'];
                $newReference = [
                    'uid_local' => $localTable . '_' . $localUid,
                    'pid' => $record['pid'],
                    'sys_language_uid' => $sourceReference['sys_language_uid'],
                ];
            }

            $newReferences[uniqid('NEW')] = $newReference;
        }
        return $newReferences;
    }
}

