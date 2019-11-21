<?php

namespace CPSIT\T3eventsTemplate\Tests\Unit\Hooks;

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

use CPSIT\T3eventsTemplate\DataHandling\Factory\EventRecordFactory;
use CPSIT\T3eventsTemplate\Hooks\DataHandler;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler as CoreDataHandler;

/**
 * Class DataHandlerTest
 */
class DataHandlerTest extends UnitTestCase
{
    /**
     * @var DataHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var EventRecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventRecordFactory;

    /**
     * set up subject
     */
    public function setUp()
    {
        $this->subject = $this->getMockBuilder(DataHandler::class)
            ->setMethods(['dummy'])->getMock();
        $this->eventRecordFactory = $this->getMockBuilder(EventRecordFactory::class)
            ->setMethods(['processNewRecord'])->getMock();
        $this->inject(
            $this->subject, 'eventRecordFactory', $this->eventRecordFactory
        );
    }

    /**
     * @test
     */
    public function templateEnabledFieldsIsInitiallyEmpty()
    {
        $this->assertAttributeSame(
            [],
            'templateEnabledTypes',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function constructorSetsTemplateEnabledRecordTypesFromGlobals()
    {
        $templateEnabledTypes = ['foo'];
        $GLOBALS['TCA'][DataHandler::TARGET_TABLE]['ctrl']['templateEnabledTypes'] = $templateEnabledTypes;
        $this->subject->__construct();
        $this->assertAttributeSame(
            $templateEnabledTypes,
            'templateEnabledTypes',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function typeFieldInitiallyContainsCorrectValue()
    {
        $this->assertAttributeSame(
            DataHandler::DEFAULT_TYPE_FIELD,
            'typeField',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function constructorSetsTypeFieldFromGlobals()
    {
        $type = 'foo';
        $GLOBALS['TCA'][DataHandler::TARGET_TABLE]['ctrl']['type'] = $type;
        $this->subject->__construct();
        $this->assertAttributeSame(
            $type,
            'typeField',
            $this->subject
        );
    }

    /**
     * Provides dataMaps for TYPO3\CMS\Core\DataHandling\DataHandler instances
     */
    public function dataMapDataProvider()
    {// dataMap, templateEnabledTypes, expected
        return [
            [
                // dataMap empty
                [],
                [],
                $this->never()
            ],
            [
                // table does not match
                ['fooTable' => []],
                [],
                $this->never()
            ],
            [
                // record not new
                [
                    DataHandler::TARGET_TABLE => [
                        '1' => []
                    ]
                ],
                [],
                $this->never()
            ],
            [
                // field template not set
                [
                    DataHandler::TARGET_TABLE => [
                        'NEW1' => [
                            DataHandler::DEFAULT_TYPE_FIELD => 'foo'
                        ]
                    ]
                ],
                [],
                $this->never()
            ],
            [
                // valid
                [
                    DataHandler::TARGET_TABLE => [
                        'NEW1' => [
                            DataHandler::DEFAULT_TYPE_FIELD => 'foo',
                            'template' => 'baz'
                        ]
                    ]
                ],
                [
                    'foo' => [
                        'copyFields' => [
                            'new' => 'zap'
                        ]
                    ]
                ],
                $this->once()
            ],
            [
                // type not enabled
                [
                    DataHandler::TARGET_TABLE => [
                        'NEW1' => [
                            DataHandler::DEFAULT_TYPE_FIELD => 'foo',
                            'template' => 'baz'
                        ]
                    ]
                ],
                [
                    'boom' => 'bar'
                ],
                $this->never()
            ]
        ];
    }

    /**
     * @test
     * @param array $dataMap
     * @param array $templateEnabledTypes
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expected * @dataProvider dataMapDataProvider
     */
    public function hookMethodProcessesMatchingRecordsFromDataMap($dataMap, $templateEnabledTypes, $expected)
    {
        $this->inject(
            $this->subject,
            'templateEnabledTypes',
            $templateEnabledTypes
        );
        /** @var CoreDataHandler|\PHPUnit_Framework_MockObject_MockObject $mockDataHandler */
        $mockDataHandler = $this->getMockBuilder(CoreDataHandler::class)
            ->disableOriginalConstructor()->getMock();
        $mockDataHandler->datamap = $dataMap;

        $this->eventRecordFactory->expects($expected)->method('processNewRecord');
        $this->subject->processDatamap_beforeStart($mockDataHandler);
    }
}
