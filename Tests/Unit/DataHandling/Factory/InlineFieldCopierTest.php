<?php

namespace CPSIT\T3eventsTemplate\Tests\Unit\DataHandling\Factory;

use CPSIT\T3eventsTemplate\DataHandling\Factory\InlineFieldCopier;
use DWenzel\T3events\InvalidConfigurationException;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;

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
class InlineFieldCopierTest extends UnitTestCase
{

    /**
     * @var InlineFieldCopier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var DataHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHandler;

    /**
     * @var DatabaseConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataBase;

    /**
     * set up subject
     */
    public function setUp()
    {
        $this->subject = $this->getMockBuilder(InlineFieldCopier::class)
            ->setMethods(['dummy', 'callStatic'])->getMock();
        $this->dataBase = $this->getMockBuilder(DatabaseConnection::class)
            ->disableOriginalConstructor()->setMethods(['fullQuoteString'])
            ->getMock();
        $this->dataHandler = $this->getMockBuilder(DataHandler::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @test
     */
    public function initializeSetsTemplateTable()
    {
        $table = 'foo';
        $config = [
            'templateTable' => $table
        ];
        $this->assertAttributeEmpty('templateTable', $this->subject);

        $this->subject->initialize($config);

        $this->assertAttributeSame($table, 'templateTable', $this->subject);
    }

    /**
     * @test
     */
    public function getDataBaseGetsDataBaseConnectionFromGlobals()
    {
        $GLOBALS['TYPO3_DB'] = $this->dataBase;

        $this->assertSame(
            $this->dataBase,
            $this->subject->getDataBase()
        );
    }

    /**
     * provides values for copy test
     */
    public function copyValueDataProvider()
    {
        $sourceReferences = [
            ['uid' => 2]
        ];
        // $sourceRecord, $fieldConfig, $expectCall, $sourceReferences, $expectedValue
        return [
            [
                // empty config, empty result
                ['uid' => 1, 'foo' => 'baz'], [], false, null, ''
            ],
            [
                // empty config, empty result
                ['uid' => 1, 'foo' => 'baz'],
                [
                    'foreign_table' => 'boom'
                ],
                true, [], ''
            ],
        ];
    }

    /**
     * @test
     * @param array $sourceRecord
     * @param array $fieldConfig
     * @param boolean $expectCall BackendUtility called
     * @dataProvider copyValueDataProvider
     * @param array $sourceReferences
     * @param string $expectedValue
     */
    public function getValueReturnsCorrectValue($sourceRecord, $fieldConfig, $expectCall, $sourceReferences, $expectedValue)
    {
        if (isset($fieldConfig['foreign_table'])) {
            $GLOBALS['TCA'][$fieldConfig['foreign_table']] = ['ook'];
        }
        $fieldName = 'foo';
        $foreignField = 'uid_foreign';
        $templateTable = 'bar';
        $whereClause = '';
        $this->inject($this->subject, 'templateTable', $templateTable);
        $record = [];

        if ($expectCall) {
            $this->subject->expects($this->once())
                ->method('callStatic')
                ->with(
                    BackendUtility::class,
                    'getRecordsByField',
                    $fieldConfig['foreign_table'],
                    $foreignField,
                    $sourceRecord['uid'],
                    $whereClause
                )
                ->will(
                    $this->returnValue($sourceReferences)
                );
        }
        $this->assertSame(
            $expectedValue,
            $this->subject->getValue($record, $fieldConfig, $sourceRecord, $fieldName, $this->dataHandler)
        );
    }

    /**
     * @test
     * @expectedException \DWenzel\T3events\InvalidConfigurationException
     * @expectedExceptionCode 1500899571
     */
    public function getValueThrowsExceptionForInvalidForeignTable()
    {
        $foreignTable = 'foo';
        $fieldName = 'foo';
        $templateTable = 'bar';
        $this->inject($this->subject, 'templateTable', $templateTable);
        $record = [];
        $sourceRecord = [];

        $fieldConf = [
            'foreign_table' => $foreignTable
        ];

        unset($GLOBALS['TCA'][$foreignTable]);
        $this->subject->getValue($record, $fieldConf, $sourceRecord, $fieldName, $this->dataHandler);
    }

}

