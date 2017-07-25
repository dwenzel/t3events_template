<?php

namespace CPSIT\T3eventsTemplate\Utility;

use CPSIT\T3eventsTemplate\DataHandling\Factory\EventRecordFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Dirk Wenzel <dirk.wenzel@cps-it.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class TableConfiguration
 */
class TableConfiguration
{
    const ACTION_NEW = 'new';

    /**
     * Enable a type for the usage of templates
     *
     * @param string $type Record type - must match a valid value of the type field
     * @param string $fieldList Fields which should be copied from template
     * @param string $table Table which should be enabled
     * @param string $action
     */
    public static function registerTemplateEnabledType($type, $fieldList, $table = EventRecordFactory::TARGET_TABLE, $action = self::ACTION_NEW) {
        if (isset($GLOBALS['TCA'][$table]['types'][$type]))
        {
            $GLOBALS['TCA'][$table]['ctrl']['templateEnabledTypes'][$type]['copyFields'][$action] = $fieldList;
        }

    }

    /**
     * Hide fields in new records.
     * Note: This method works by setting a display condition. It can be used only for
     * fields where no display confition has been set, or the current condition is a string
     * @param string $fieldList Fields which should be hidden
     * @param string $table Optional table name
     * @internal param string $type Record type for which fields should be hidden
     */
    public static function hideFieldsInNewRecords($fieldList, $table = EventRecordFactory::TARGET_TABLE) {
        $fieldsToHideForNewRecords = GeneralUtility::trimExplode(',', $fieldList, true);

        foreach ($fieldsToHideForNewRecords as $fieldName) {
            if (isset($GLOBALS['TCA'][$table]['columns'][$fieldName])) {
                $currentDisplayCondition = $GLOBALS['TCA'][$table]['columns'][$fieldName]['displayCond'];
                $additionalDisplayCondition = 'REC:NEW:false';
                if (is_string($currentDisplayCondition)) {
                    $GLOBALS['TCA'][$table]['columns'][$fieldName]['displayCond'] = [
                        'AND' => [
                            $currentDisplayCondition,
                            $additionalDisplayCondition
                        ]
                    ];
                } elseif(!isset($GLOBALS ['TCA'][$table]['columns'][$fieldName]['displayCond'])) {
                    $GLOBALS ['TCA'][$table]['columns'][$fieldName]['displayCond'] = $additionalDisplayCondition;
                }
            }
        }
    }
}
