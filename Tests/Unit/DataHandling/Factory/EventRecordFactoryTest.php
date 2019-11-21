<?php
namespace CPSIT\T3eventsTemplate\Tests\Unit\DataHandling\Factory;

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
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Class EventRecordFactoryTest
 */
class EventRecordFactoryTest extends UnitTestCase
{
    /**
     * @var EventRecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var DatabaseConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataBase;

    /**
     * set up subject
     */
    public function setUp()
    {
        $this->subject = $this->getMockBuilder(EventRecordFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['dummy'])->getMock();
    }

    /**
     * @test
     */
    public function getDataBaseSetsDataBaseFromGlobals()
    {
        $mockDatabase = $this->getMockBuilder(DatabaseConnection::class)
            ->disableOriginalConstructor()->getMock();
        $GLOBALS['TYPO3_DB'] = $mockDatabase;
        $this->assertSame(
            $mockDatabase,
            $this->subject->getDataBase()
        );

        $this->assertAttributeSame(
            $mockDatabase,
            'dataBase',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function constructorSetsTemplateEnabledTypes() {
        $templateEnabledTypes = ['foo'];
        $GLOBALS['TCA'][EventRecordFactory::TARGET_TABLE]['ctrl']['templateEnabledTypes'] = $templateEnabledTypes;

        $this->subject->__construct();
        $this->assertSame(
            $templateEnabledTypes,
            $this->subject->getTemplateEnabledTypes()
        );
    }

    /**
     * @test
     */
    public function constructorSetsTypeField() {
        $type = 'foo';
        $GLOBALS['TCA'][EventRecordFactory::TARGET_TABLE]['ctrl']['type'] = $type;

        $this->subject->__construct();
        $this->assertSame(
            $type,
            $this->subject->getTypeField()
        );
    }

    /**
     * @test
     */
    public function typeFieldHasInitialValue() {
        $this->assertSame(
            EventRecordFactory::DEFAULT_TYPE_FIELD,
            $this->subject->getTypeField()
        );
    }

    /**
     * @test
     */
    public function typeFieldCanBeSet() {
        $type = 'foo';
        $this->subject->setTypeField($type);
        $this->assertSame(
            $type,
            $this->subject->getTypeField()
        );
    }

    /**
     * test
     */
    public function fromTemplateInitiallyReturnsEmptyArray(){

    }
}
