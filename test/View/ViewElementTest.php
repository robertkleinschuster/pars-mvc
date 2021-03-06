<?php

declare(strict_types=1);

/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace ParsTest\Mvc\View;

use Pars\Bean\Type\Base\AbstractBaseBean;
use Pars\Mvc\View\ViewElement;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DefaultTestCaseTest
 * @package Pars\Bean
 */
class ViewElementTest extends \Pars\Pattern\PHPUnit\DefaultTestCase
{


    /**
     * @var ViewElement|MockObject
     */
    protected $object;


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp(): void
    {
        $this->object = $this->getMockBuilder(ViewElement::class)->disableOriginalConstructor()->getMockForAbstractClass();
    }


    public function mockElement()
    {
        return $this->getMockBuilder(ViewElement::class)->disableOriginalConstructor()->getMockForAbstractClass();
    }

    public function mockBean()
    {
        return $this->getMockBuilder(AbstractBaseBean::class)->getMockForAbstractClass();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }


    /**
     * @group integration
     * @small
     */
    public function testTestClassExists()
    {
        $this->assertTrue(class_exists(ViewElement::class), "Class Exists");
        $this->assertTrue(is_a($this->object, ViewElement::class), "Mock Object is set");
    }

    /**
     * @group integration
     * @small
     */
    public function testRenderSimple()
    {
        $this->object->setTag('div');
        $this->object->setId('myDiv');
        $this->object->addOption('col');
        $this->object->setContent('test');
        $this->assertEquals("<div id='myDiv' class='col'>test</div>", $this->object->render());
    }


    /**
     * @group integration
     * @small
     */
    public function testRenderNested()
    {
        $this->object->setTag('div');
        $this->object->setId('myDiv');
        $this->object->addOption('col');
        $this->object->setContent('test');
        $child = $this->mockElement();
        $child->setTag('p');
        $child->setContent('foo');
        $child2 = $this->mockElement();
        $child2->setTag('div');
        $child2->addOption('bla');
        $child3 = $this->mockElement();
        $child3->setTag('p');
        $child3->setContent('bla');
        $child2->getElementList()->push($child3);
        $this->object->getElementList()->push($child);
        $this->object->getElementList()->push($child2);
        $this->assertEquals("<div id='myDiv' class='col'>test<p>foo</p><div class='bla'><p>bla</p></div></div>", $this->object->render());
    }

    /**
     * @group integration
     * @small
     */
    public function testRenderNestedData()
    {
        $bean = $this->mockBean();
        $bean->set('foo', 'bar');
        $this->object->setTag('div');
        $this->object->setId('myDiv');
        $this->object->addOption('col');
        $this->object->setContent('test');
        $child = $this->mockElement();
        $child->setTag('p');
        $child->setContent('{foo}');
        $child2 = $this->mockElement();
        $child2->setTag('div');
        $child2->addOption('bla');
        $child3 = $this->mockElement();
        $child3->setTag('p');
        $child3->setContent('bla');
        $child2->getElementList()->push($child3);
        $this->object->getElementList()->push($child);
        $this->object->getElementList()->push($child2);
        $this->assertEquals("<div id='myDiv' class='col'>test<p>bar</p><div class='bla'><p>bla</p></div></div>", $this->object->render($bean, true));
    }

    /**
     * @group integration
     * @small
     */
    public function testRenderNestedDataPath()
    {
        $bean = $this->mockBean();
        $bean->set('foo', 'bar');
        $this->object->setTag('div');
        $this->object->setId('myDiv');
        $this->object->addOption('col');
        $this->object->setContent('test');
        $this->object->setPath("/test/bla?id=" . urlencode("foo={foo}"));
        $child = $this->mockElement();
        $child->setTag('p');
        $child->setContent('foo');
        $child2 = $this->mockElement();
        $child2->setTag('div');
        $child2->addOption('bla');
        $child3 = $this->mockElement();
        $child3->setTag('p');
        $child3->setContent('bla');
        $child2->getElementList()->push($child3);
        $this->object->getElementList()->push($child);
        $this->object->getElementList()->push($child2);
        $this->assertEquals("<div id='myDiv' class='col text-decoration-none position-relative'><a class='text-decoration-none text-reset stretched-link' href='/test/bla?id=foo%3Dbar'>test<p>foo</p><div class='bla'><p>bla</p></div></a></div>", $this->object->render($bean, true));
    }
}
