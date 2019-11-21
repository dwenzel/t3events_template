<?php

namespace CPSIT\T3eventsTemplate\Tests\Unit\DataHandling\Factory;
use CPSIT\T3eventsTemplate\DataHandling\Factory\EmptyFieldCopier;
use Nimut\TestingFramework\TestCase\UnitTestCase;

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

class EmptyFieldCopierTest extends UnitTestCase
{

    /**
     * @var EmptyFieldCopier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * set up subject
     */
    public function setUp()
    {
     $this->subject = $this->getMockBuilder(EmptyFieldCopier::class)
         ->setMethods(['dummy'])->getMock();
    }

    /**
     * @test
     */
    public function getValueAlwaysReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getValue([], [], [], '')
        );
    }
}
