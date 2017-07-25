<?php

namespace DWenzel\T3events\Tests\Unit\Utility;

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

use CPSIT\T3eventsTemplate\Utility\TableConfiguration;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class TableConfigurationTest extends UnitTestCase
{
    /**
     * @test
     */
    public function templateEnabledTypeCanBeRegistered()
    {
        $type = 'foo';
        $table = 'bar';
        $fieldList = 'boom,bang';
        $GLOBALS['TCA'][$table]['types'][$type] = [
            'showitem' => 'any'
        ];

        TableConfiguration::registerTemplateEnabledType(
            $type, $fieldList, $table
        );
        $this->assertSame(
            $fieldList,
            $GLOBALS['TCA'][$table]['ctrl']['templateEnabledTypes'][$type]['copyFields'][TableConfiguration::ACTION_NEW]
        );
    }

    /**
     * @test
     */
    public function nonExistingTypesAreNotRegistered()
    {
        $type = 'foo';
        $table = 'bar';
        $fieldList = 'boom,bang';
        $GLOBALS['TCA'][$table]['ctrl']['templateEnabledTypes'] = [];

        TableConfiguration::registerTemplateEnabledType(
            $type, $fieldList, $table
        );
        $this->assertArrayNotHasKey(
            $type,
            $GLOBALS['TCA'][$table]['ctrl']['templateEnabledTypes']
        );
    }

    /**
     * @test
     */
    public function hideFieldsIn8NewRecordsSetsDisplayCondition()
    {
        $table = 'bar';
        $fieldList = 'boom,bang';
        $GLOBALS['TCA'][$table]['columns'] = [
            'boom' => [
                'displayCond' => 'foo'
            ],
            'bang' => [],
        ];
        $expectedConfiguration = [
            'boom' => [
                'displayCond' => [
                    'AND' => [
                        'foo',
                        'REC:NEW:false'
                    ]
                ]
            ],
            'bang' => [
                'displayCond' => 'REC:NEW:false'
            ],
        ];
        TableConfiguration::hideFieldsInNewRecords(
            $fieldList, $table
        );

        $this->assertSame(
            $expectedConfiguration,
            $GLOBALS['TCA'][$table]['columns']
        );

    }
}
