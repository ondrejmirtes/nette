<?php

namespace Nette\Web;

/**
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
		$default = Html::$xhtml;
		Html::$xhtml = FALSE;
		$el = Html::el('img')->src('image.gif')->alt('');
		$this->assertEquals('<img src="image.gif" alt="">', (string) $el);
		Html::$xhtml = $default;
	}
	
	public function testOverwriteAttributes()
	{
		$el = Html::el('img')->setSrc('image.gif')->setAlt('alt')->setAlt('alt2');
		$this->assertEquals('<img src="image.gif" alt="alt2" />', (string) $el);
		$this->assertEquals('alt2', $el->getAlt());
		
		$el->addAlt('alt3');
		$this->assertEquals('<img src="image.gif" alt="alt2 alt3" />', (string) $el);
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
		$this->assertEquals('<span src="image.gif" alt="alt alt2" style="float:left" class="three" lang="" title="0" checked="checked" name="testname"></span>', (string) $el);
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
	
	public function testWholeStartTagString()
	{
		$el = Html::el('a lang=cs href="#" title="" selected')->setText('click');
		$this->assertEquals('<a lang="cs" href="#" title="" selected="selected">click</a>', (string) $el);
		
		$el = Html::el('a lang=hello world href="hello world" title="hello \'world"')->setText('click');
		$this->assertEquals('<a lang="hello" world="world" href="hello world" title="hello \'world">click</a>', (string) $el);
		
		$el = Html::el('a lang=\'hello" world\' href="hello "world" title=0')->setText('click');
		$this->assertEquals('<a lang="hello&quot; world" href="hello " world="world" title="0">click</a>', (string) $el);
	}
	
	public function testDataAttribute()
	{
		$el = Html::el('div');
		$el->data['a'] = 'one';
		$el->data['b'] = NULL;
		$el->data['c'] = FALSE;
		$el->data['d'] = '';
		$el->data['e'] = 'two';
		$this->assertEquals('<div data-a="one" data-d="" data-e="two"></div>', (string) $el);
		
		// direct
		$el = Html::el('div');
		$el->{'data-x'} = 'x';
		$el->data['x'] = 'y';
		$this->assertEquals('<div data-x="x" data-x="y"></div>', (string) $el);
		
		// function
		$el = Html::el('div');
		$el->data('a', 'one');
		$el->data('b', 'two');
		$this->assertEquals('<div data-a="one" data-b="two"></div>', (string) $el);
		
		$el = Html::el('div');
		$el->data('top', NULL);
		$el->data('active', FALSE);
		$el->data('x', '');
		
		$this->assertEquals('<div data-x=""></div>', (string) $el);
		
		$el = Html::el('div');
		$el->data = 'simple';
		$this->assertEquals('<div data="simple"></div>', (string) $el); 
	}
	
	public function testChildren()
	{
		// add
		$el = Html::el('ul');
		$el->create('li')->setText('one');
		$el->add( Html::el('li')->setText('two') )->class('hello');
		$this->assertEquals('<ul class="hello"><li>one</li><li>two</li></ul>', (string) $el);
		
		$el = Html::el(NULL);
		$el->add( Html::el('p')->setText('one') );
		$el->add( Html::el('p')->setText('two') );
		$this->assertEquals('<p>one</p><p>two</p>', (string) $el);
		
		//get child
		$this->assertTrue(isset($el[1]));
		$this->assertEquals('<p>two</p>', (string) $el[1]);
		$this->assertFalse(isset($el[2]));
	}
	
	public function testChildrenRenderIndentation()
	{
		$el = Html::el('ul');
		$el->create('li')->setText('one');
		$el->add( Html::el('li')->setText('two') )->class('hello');
		$this->assertEquals("\n\t<ul class=\"hello\">\n\t\t<li>one</li>\n\t\n\t\t<li>two</li>\n\t</ul>\n", $el->render(1));
	}
	
	public function testChildrenIterator()
	{
		$el = Html::el('select');
		$el->create('optgroup')->label('Main')->create('option')->setText('sub one')->create('option')->setText('sub two');
		$el->create('option')->setText('Item');
		$this->assertEquals('<select><optgroup label="Main"><option>sub one<option>sub two</option></option></optgroup><option>Item</option></select>', (string) $el);
		
		foreach ($el as $name => $child) {
				$this->assertType('Nette\Web\Html', $child);
		}
		
		$this->assertEquals('optgroup', $el[0]->getName());
		$this->assertEquals('option', $el[1]->getName());
		
		// deep iterator
		$i = 0;
		foreach($el->getIterator(TRUE) as $name => $child) {
			switch ($i) {
				case 0:
					$this->assertEquals('optgroup', $child->getName());
					break;
				case 1:
					$this->assertEquals('option', $child->getName());
					break;
				case 2:
					$this->assertEquals('sub one', $child);
					break;
				case 3:
					$this->assertEquals('option', $child->getName());
					break;
				case 4:
					$this->assertEquals('sub two', $child);
					break;
				case 5:
					$this->assertEquals('option', $child->getName());
					break;
				case 6:
					$this->assertEquals('Item', $child);
					break;
				default:
					$this->fail('Deep children iterator should not have more than 7 items.');
			}
			$i++;
		}
	}
	
	public function testStyle()
	{
		$el = Html::el('div');
		$el->style[] = 'text-align:right';
		$el->style[] = NULL;
		$el->style[] = 'background-color: blue';
		$el->class[] = 'one';
		$el->class[] = NULL;
		$el->class[] = 'two';
		$this->assertEquals('<div style="text-align:right;background-color: blue" class="one two"></div>', (string) $el);
		
		$el->style = NULL;
		$el->style['text-align'] = 'left';
		$el->style['background-color'] = 'green';
		$this->assertEquals('<div style="text-align:left;background-color:green" class="one two"></div>', (string) $el);
	}
	
	public function testStyleAppend()
	{
		$el = Html::el('div');
		$el->style('color', 'white');
		$el->style('background-color', 'blue');
		
		$el->class = 'one';
		$el->class('', TRUE);
		$el->class('two', TRUE);
		
		$el->id('my', TRUE);
		$this->assertEquals('<div style="color:white;background-color:blue" class="one two" id="my"></div>', (string) $el);

		$el = Html::el('div');
		$el->style[] = 'text-align:right';
		$el->style('', TRUE);
		$el->style('background-color: blue', TRUE);
		$this->assertEquals('<div style="text-align:right;background-color: blue"></div>', (string) $el);

		$el = Html::el('div');
		$el->class('top', TRUE);
		$el->class('active', TRUE);
		$this->assertEquals('<div class="top active"></div>', (string) $el);
		
		$el->class('top', NULL);
		$el->class('active', FALSE);
		$this->assertEquals('<div></div>', (string) $el);
	}

}
