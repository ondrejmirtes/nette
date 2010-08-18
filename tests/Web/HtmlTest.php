<?php

namespace Nette\Web;

/**
 * Test: Nette\Web\Html basic usage.
 *
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class HtmlTest extends \TestCase
{

	public function testBasicTagBehaviour()
	{
		$el = Html::el('img')->src('image.gif')->alt('');
		$this->assertEquals('<img src="image.gif" alt="" />', (string) $el);
		$this->assertEquals('<img src="image.gif" alt="" />', $el->startTag());
		$this->assertEmpty($el->endTag());
		$this->assertEquals('image.gif', $el->getSrc());
		$this->assertNull($el->getTitle());
	}
	
	public function testSingleTagCanNotHaveText()
	{
		$el = Html::el('img')->src('image.gif')->alt('')->setText(NULL)->setText('any content');
		$this->assertEquals('<img src="image.gif" alt="" />', (string) $el);
		$this->assertEquals('<img src="image.gif" alt="" />', $el->startTag());
		$this->assertEmpty($el->endTag());
	}
	
	public function testSingleTagInHtmlDoctype()
	{
		Html::$xhtml = FALSE;
		$el = Html::el('img')->src('image.gif')->alt('');
		$this->assertEquals('<img src="image.gif" alt="">', (string) $el);
	}
	
	public function testOverwriteAttributes()
	{
		$el = Html::el('img')->setSrc('image.gif')->setAlt('alt')->setAlt('alt2');
		$this->assertEquals('<img src="image.gif" alt="alt2">', (string) $el);
		$this->assertEquals('alt2', $el->getAlt());
		
		$el->addAlt('alt3');
		$this->assertEquals('<img src="image.gif" alt="alt2 alt3">', (string) $el);
	}
	
	public function testConvertIntoDifferentTag()
	{
		$el = Html::el('img')->src('image.gif')->alt('alt');
		$el->addAlt('alt2');
		$el->style = 'float:left';
		$el->class = 'three';
		$el->lang = '';
		$el->title = '0';
		$el->checked = TRUE;
		$el->selected = FALSE;
		$el->name = 'testname';
		$el->setName('span');
		$this->assertEquals('<span src="image.gif" alt="alt alt2" style="float:left" class="three" lang="" title="0" checked name="testname"></span>', (string) $el);
	}
	
	public function testSetTextVersusSetHtml()
	{
		$el = Html::el('p')->setText('Hello &ndash; World');
		$this->assertEquals('<p>Hello &amp;ndash; World</p>', (string) $el);
		
		$el = Html::el('p')->setHtml('Hello &ndash; World');
		$this->assertEquals('<p>Hello &ndash; World</p>', (string) $el);
	}
	
	public function testGetTextVersusGetHtml()
	{
		$el = Html::el('p')->setHtml('Hello &ndash; World');
		$el->create('a')->setText('link');
		
		$this->assertEquals('<p>Hello &ndash; World<a>link</a></p>', (string) $el);
		$this->assertEquals('Hello – Worldlink', $el->getText());
	}
	
	public function testObfuscateEmail()
	{
		$el = Html::el('a')->href('mailto:dave@example.com');
		$this->assertEquals('<a href="mailto:dave&#64;example.com"></a>', (string) $el);
	}
	
	public function testHrefWithGetParameters()
	{
		$el = Html::el('a')->href('file.php', array('a' => 10));
		$this->assertEquals('<a href="file.php?a=10"></a>', (string) $el);
	}

}
