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
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SelectFieldCopier
 */
class SelectFieldCopier
    implements FieldCopierInterface, InitializeInterface
{
    use CallStaticTrait;

    /**s
     * @var string
     */
    protected $templateTable;

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
    public function getValue($record, $fieldConfig, $sourceRecord, $fieldName = null, $dataHandler = null)
    {
        $sourceRecordId = (int)$sourceRecord['uid'];

        $processedValue = $this->callStatic(
            BackendUtility::class,
            'getProcessedValue',
            $this->templateTable,
            $fieldName,
            $sourceRecord[$fieldName],
            0,
            false,
            true,
            $sourceRecordId
        );
        $idArr = $this->callStatic(
            GeneralUtility::class,
            'trimExplode',
            ';',
            $processedValue,
            true
        );

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
}
