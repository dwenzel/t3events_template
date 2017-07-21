<?php

namespace CPSIT\T3eventsTemplate\Tests\Unit\DataHandling\Factory;

use CPSIT\T3eventsTemplate\DataHandling\Factory\SelectFieldCopier;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class SelectFieldCopierTest extends UnitTestCase
{

    /**
     * @var SelectFieldCopier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * set up subject
     */
    public function setUp()
    {
        $this->subject = $this->getMockBuilder(SelectFieldCopier::class)
            ->setMethods(['dummy', 'callStatic'])->getMock();
    }

    /**
     * provides values for copy test
     */
    public function copyValueDataProvider()
    {
        // $sourceRecord, $fieldConfig, $expected
        return [
            [
                ['uid' => 1, 'foo' => 'baz'], ['renderType' => 'boom'], ''
            ],
            [
                ['uid' => 1, 'foo' => 'baz'], ['renderType' => 'selectSingle'], 'baz'
            ],
            [
                ['uid' => 1, 'foo' => 'baz'], ['renderType' => 'selectTree'], '1,5'
            ],
            [
                ['uid' => 1, 'foo' => 'baz', 'foreign_table' => 'xyz'],
                ['renderType' => 'selectMultipleSideBySide', 'foreign_table' => 'xyz'],
                'xyz_1,xyz_5'
            ]
        ];
    }

    /**
     * @test
     * @param array $sourceRecord
     * @param array $fieldConfig
     * @param string $expected
     * @dataProvider copyValueDataProvider
     */
    public function getValueReturnsCorrectValue($sourceRecord, $fieldConfig, $expected)
    {
        $fieldName = 'foo';
        $templateTable = 'bar';
        $this->inject($this->subject, 'templateTable', $templateTable);
        $record = [];
        $processedValue = '1;5';
        $idArray = [1, 5];
        $this->subject->expects($this->exactly(2))->method('callStatic')
            ->withConsecutive(
                [
                    BackendUtility::class,
                    'getProcessedValue',
                    $templateTable,
                    $fieldName,
                    $sourceRecord[$fieldName],
                    0, false, true, $sourceRecord['uid']
                ],
                [
                    GeneralUtility::class,
                    'trimExplode', ';', $processedValue, true
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $processedValue, $idArray
            );
        $this->assertSame(
            $expected,
            $this->subject->getValue($record, $fieldConfig, $sourceRecord, $fieldName)
        );
    }
}
