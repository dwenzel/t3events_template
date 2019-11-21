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
use TYPO3\CMS\Core\DataHandling\DataHandler as CoreDataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Class EventRecordFactory
 */
class EventRecordFactory implements RecordFactoryInterface
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
        if (isset($GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['templateEnabledTypes'])
            && is_array($GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['templateEnabledTypes'])
        ) {
            $this->templateEnabledTypes = $GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['templateEnabledTypes'];
        }
        if (isset($GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['type'])) {
            $this->typeField = $GLOBALS['TCA'][static::TARGET_TABLE]['ctrl']['type'];
        }
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
     * @return array
     */
    public function getTemplateEnabledTypes()
    {
        return $this->templateEnabledTypes;
    }

    /**
     * @return string
     */
    public function getTypeField()
    {
        return $this->typeField;
    }

    /**
     * @param string $typeField
     */
    public function setTypeField($typeField)
    {
        $this->typeField = $typeField;
    }

    /**
     * @param array $record Array of fields
     * @param CoreDataHandler $parentObject
     * @return void
     */
    public function processNewRecord(&$record, $parentObject)
    {
        $type = $record[$this->typeField];
        $fieldListToCopy = $this->templateEnabledTypes[$type]['copyFields']['new'];
        $fieldsToCopy = GeneralUtility::trimExplode(',', $fieldListToCopy, true);
        if (!(bool)$fieldsToCopy) {
            return;
        }

        $templateParts = explode('_', $record['template']);
        $templateId = (int)array_pop($templateParts);
        $templateRecord = BackendUtility::getRecord(self::TEMPLATE_TABLE, $templateId);

        foreach ($fieldsToCopy as $fieldName) {
            if (isset($templateRecord[$fieldName])) {
                $fieldConfig = $GLOBALS['TCA'][self::TEMPLATE_TABLE]['columns'][$fieldName]['config'];
                if (!(bool)$fieldConfig) {
                    continue;
                }

                $this->copyFieldValue($record, $fieldName, $fieldConfig, $templateRecord, $parentObject);
            }
        }
        return;
    }


    /**
     * Copies the value of a given template field to the fieldArray.
     * Non trivial fields (i.e. relation fields) have to be prepared to be suitable for DataHandler.
     *
     * @param array $record
     * @param string $fieldName
     * @param array $fieldConfig
     * @param array $sourceRecord
     * @param CoreDataHandler $parentObject
     */
    protected function copyFieldValue(&$record, $fieldName, $fieldConfig, $sourceRecord, &$parentObject)
    {
        $copier = $this->getFieldCopier($fieldConfig);
        if ($copier instanceof InitializeInterface) {
            $initializerConfig = [
                'templateTable' => static::TEMPLATE_TABLE,
                'dataMapper' => $parentObject
            ];
            $copier->initialize($initializerConfig);
        }
       //todo process parents datamap
        $record[$fieldName] = $copier->getValue($record, $fieldConfig, $sourceRecord, $fieldName, $parentObject);
    }

    /**
     * @param $fieldConfig
     * @return FieldCopierInterface
     */
    protected function getFieldCopier($fieldConfig)
    {
        $constructorArguments = null;
        switch ($fieldConfig['type']) {
            case 'input':
            case 'check':
            case 'radio':
            case 'imageManipulation':
            case 'text':
            case 'group':
                $className = SimpleFieldCopier::class;
                break;
            case 'select':
                $className = SelectFieldCopier::class;
                break;
            case 'inline':
                $className = InlineFieldCopier::class;
                break;
            default:
                // type user falls through
                $className = EmptyFieldCopier::class;

        }
        /** @var FieldCopierInterface $copier */
        $copier = $this->callStatic(
            GeneralUtility::class,
            'makeInstance',
            $className
        );

        return $copier;
    }
}
