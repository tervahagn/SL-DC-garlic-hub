<?php

namespace Tests\Unit\Framework\Utils\Datatable\Results;

use App\Framework\Utils\Datatable\Results\BodyPreparer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class BodyPreparerTest extends TestCase
{
	private BodyPreparer $bodyPreparer;

	protected function setUp(): void
	{
		$this->bodyPreparer = new BodyPreparer();
	}

	#[Group('units')]
	public function testFormatTextWithValidString(): void
	{
		$text = 'Sample Text';

		$result = $this->bodyPreparer->formatText($text);

		$this->assertEquals([
			'CONTROL_ELEMENT_VALUE_TEXT' => 'Sample Text',
		], $result);
	}

	#[Group('units')]
	public function testFormatSpanWithAllParameters(): void
	{
		$valueName = 'Sample Name';
		$title = 'Sample Title';
		$valueId = '123';
		$cssClass = 'test-class';

		$result = $this->bodyPreparer->formatSpan($valueName, $title, $valueId, $cssClass);

		$this->assertEquals([
			'CONTROL_ELEMENT_VALUE_NAME' => 'Sample Name',
			'CONTROL_ELEMENT_VALUE_TITLE' => 'Sample Title',
			'CONTROL_ELEMENT_VALUE_ID' => '123',
			'CONTROL_ELEMENT_VALUE_CLASS' => 'test-class',
		], $result);
	}

	#[Group('units')]
	public function testFormatSpanWithoutCssClass(): void
	{
		$valueName = 'Sample Name';
		$title = 'Sample Title';
		$valueId = '123';

		$result = $this->bodyPreparer->formatSpan($valueName, $title, $valueId);

		$this->assertEquals([
			'CONTROL_ELEMENT_VALUE_NAME' => 'Sample Name',
			'CONTROL_ELEMENT_VALUE_TITLE' => 'Sample Title',
			'CONTROL_ELEMENT_VALUE_ID' => '123',
			'CONTROL_ELEMENT_VALUE_CLASS' => '',
		], $result);
	}

	#[Group('units')]
	public function testFormatSpanWithEmptyValues(): void
	{
		$result = $this->bodyPreparer->formatSpan('', '', '');

		$this->assertEquals([
			'CONTROL_ELEMENT_VALUE_NAME' => '',
			'CONTROL_ELEMENT_VALUE_TITLE' => '',
			'CONTROL_ELEMENT_VALUE_ID' => '',
			'CONTROL_ELEMENT_VALUE_CLASS' => '',
		], $result);
	}


	#[Group('units')]
	public function testFormatActionWithValidParameters(): void
	{
		$lang = 'en';
		$link = 'https://example.com/action';
		$id   = 'action_id';
		$name = 'Example Action';
		$cssClass = 'icon-class';

		$result = $this->bodyPreparer->formatAction($lang, $link, $name, $id, $cssClass);

		$this->assertEquals([
			'LANG_ACTION' => 'en',
			'LINK_ACTION' => 'https://example.com/action',
			'ACTION_ID'   => 'action_id',
			'ACTION_NAME' => 'Example Action',
			'ACTION_ICON_CLASS' => 'icon-class',
		], $result);
	}

	#[Group('units')]
	public function testFormatActionWithEmptyParameters(): void
	{
		$lang = '';
		$link = '';
		$id   = '';
		$name = '';
		$cssClass = '';

		$result = $this->bodyPreparer->formatAction($lang, $link, $name, $id, $cssClass);

		$this->assertEquals([
			'LANG_ACTION' => '',
			'LINK_ACTION' => '',
			'ACTION_ID'   => '',
			'ACTION_NAME' => '',
			'ACTION_ICON_CLASS' => '',
		], $result);
	}

	#[Group('units')]
	public function testFormatActionWithSpecialCharacters(): void
	{
		$lang = '!lang123';
		$link = 'https://example.com?action=1&data=special';
		$id   = 'action_id';
		$name = 'Name@Action!';
		$cssClass = '.icon#special';

		$result = $this->bodyPreparer->formatAction($lang, $link, $name, $id, $cssClass);

		$this->assertEquals([
			'LANG_ACTION' => '!lang123',
			'LINK_ACTION' => 'https://example.com?action=1&data=special',
			'ACTION_ID'   => 'action_id',
			'ACTION_NAME' => 'Name@Action!',
			'ACTION_ICON_CLASS' => '.icon#special',
		], $result);
	}

	public function testFormatTextWithEmptyString(): void
	{
		$text = '';

		$result = $this->bodyPreparer->formatText($text);

		$this->assertEquals([
			'CONTROL_ELEMENT_VALUE_TEXT' => '',
		], $result);
	}

	#[Group('units')]
	public function testFormatTextWithSpecialCharacters(): void
	{
		$text = 'Text with special characters: !@#$%^&*()_+';

		$result = $this->bodyPreparer->formatText($text);

		$this->assertEquals([
			'CONTROL_ELEMENT_VALUE_TEXT' => 'Text with special characters: !@#$%^&*()_+',
		], $result);
	}

	#[Group('units')]
	public function testFormatLinkWithAllParameters(): void
	{
		$valueName = 'testValue';
		$title = 'Test Title';
		$href = 'https://example.com';
		$valueId = '123';
		$cssClass = 'test-class';

		$result = $this->bodyPreparer->formatLink($valueName, $title, $href, $valueId, $cssClass);

		$this->assertEquals([
			'CONTROL_ELEMENT_VALUE_NAME' => 'testValue',
			'CONTROL_ELEMENT_VALUE_TITLE' => 'Test Title',
			'CONTROL_ELEMENT_VALUE_LINK' => 'https://example.com',
			'CONTROL_ELEMENT_VALUE_ID' => '123',
			'CONTROL_ELEMENT_VALUE_CLASS' => 'test-class',
			'CONTROL_ELEMENT_ADDITIONAL_TEXT' => ''
		], $result);
	}

	#[Group('units')]
	public function testFormatLinkWithoutCssClass(): void
	{
		$valueName = 'testValue';
		$title = 'Test Title';
		$href = 'https://example.com';
		$valueId = '123';

		$result = $this->bodyPreparer->formatLink($valueName, $title, $href, $valueId);

		$this->assertEquals([
			'CONTROL_ELEMENT_VALUE_NAME' => 'testValue',
			'CONTROL_ELEMENT_VALUE_TITLE' => 'Test Title',
			'CONTROL_ELEMENT_VALUE_LINK' => 'https://example.com',
			'CONTROL_ELEMENT_VALUE_ID' => '123',
			'CONTROL_ELEMENT_VALUE_CLASS' => '',
		    'CONTROL_ELEMENT_ADDITIONAL_TEXT' => ''
		], $result);
	}

	#[Group('units')]
	public function testFormatLinkWithEmptyValues(): void
	{
		$result = $this->bodyPreparer->formatLink('', '', '', '');

		$this->assertEquals([
			'CONTROL_ELEMENT_VALUE_NAME' => '',
			'CONTROL_ELEMENT_VALUE_TITLE' => '',
			'CONTROL_ELEMENT_VALUE_LINK' => '',
			'CONTROL_ELEMENT_VALUE_ID' => '',
			'CONTROL_ELEMENT_VALUE_CLASS' => '',
		    'CONTROL_ELEMENT_ADDITIONAL_TEXT' => ''
		], $result);
	}

	#[Group('units')]
	public function testFormatLinkWithSpecialCharacters(): void
	{
		$valueName = 'val@123';
		$title = 'Title & More';
		$href = 'https://example.com?page=1&name=test';
		$valueId = 'id-456';
		$cssClass = '.some-class#unique';

		$result = $this->bodyPreparer->formatLink($valueName, $title, $href, $valueId, $cssClass, 'some suffix');

		$this->assertEquals([
			'CONTROL_ELEMENT_VALUE_NAME' => 'val@123',
			'CONTROL_ELEMENT_VALUE_TITLE' => 'Title & More',
			'CONTROL_ELEMENT_VALUE_LINK' => 'https://example.com?page=1&name=test',
			'CONTROL_ELEMENT_VALUE_ID' => 'id-456',
			'CONTROL_ELEMENT_VALUE_CLASS' => '.some-class#unique',
		    'CONTROL_ELEMENT_ADDITIONAL_TEXT' => 'some suffix'
		], $result);
	}

	#[Group('units')]
	public function testFormatUIDWithValidValues(): void
	{
		$UID = 12345;
		$username = 'test_user';

		$result = $this->bodyPreparer->formatUID($UID, $username);

		$this->assertEquals([
			'OWNER_UID' => 12345,
			'OWNER_NAME' => 'test_user',
		], $result);
	}

	#[Group('units')]
	public function testFormatUIDWithZeroUID(): void
	{
		$UID = 0;
		$username = 'guest';

		$result = $this->bodyPreparer->formatUID($UID, $username);

		$this->assertEquals([
			'OWNER_UID' => 0,
			'OWNER_NAME' => 'guest',
		], $result);
	}

	#[Group('units')]
	public function testFormatUIDWithEmptyUsername(): void
	{
		$UID = 999;
		$username = '';

		$result = $this->bodyPreparer->formatUID($UID, $username);

		$this->assertEquals([
			'OWNER_UID' => 999,
			'OWNER_NAME' => '',
		], $result);
	}

	#[Group('units')]
	public function testFormatUIDWithNegativeUID(): void
	{
		$UID = -123;
		$username = 'negative_test';

		$result = $this->bodyPreparer->formatUID($UID, $username);

		$this->assertEquals([
			'OWNER_UID' => -123,
			'OWNER_NAME' => 'negative_test',
		], $result);
	}

	#[Group('units')]
	public function testFormatActionDeleteWithValidParameters(): void
	{
		$lang = 'delete';
		$langConfirm = 'Are you sure?';
		$id = '456';
		$cssClass = 'delete-icon';

		$result = $this->bodyPreparer->formatActionDelete($lang, $langConfirm, $id, $cssClass);

		$this->assertEquals([
			'LANG_DELETE_ACTION' => 'delete',
			'DELETE_ID' => '456',
			'LANG_CONFIRM_DELETE' => 'Are you sure?',
			'ELEMENT_DELETE_CLASS' => 'delete-icon',
		], $result);
	}

	#[Group('units')]
	public function testFormatActionDeleteWithEmptyParameters(): void
	{
		$lang = '';
		$langConfirm = '';
		$id = '';
		$cssClass = '';

		$result = $this->bodyPreparer->formatActionDelete($lang, $langConfirm, $id, $cssClass);

		$this->assertEquals([
			'LANG_DELETE_ACTION' => '',
			'DELETE_ID' => '',
			'LANG_CONFIRM_DELETE' => '',
			'ELEMENT_DELETE_CLASS' => '',
		], $result);
	}

	#[Group('units')]
	public function testFormatActionDeleteWithSpecialCharacters(): void
	{
		$lang = 'del@ete';
		$langConfirm = 'Conf!rm@tion??';
		$id = 'del-123#456';
		$cssClass = '.del-class#special';

		$result = $this->bodyPreparer->formatActionDelete($lang, $langConfirm, $id, $cssClass);

		$this->assertEquals([
			'LANG_DELETE_ACTION' => 'del@ete',
			'DELETE_ID' => 'del-123#456',
			'LANG_CONFIRM_DELETE' => 'Conf!rm@tion??',
			'ELEMENT_DELETE_CLASS' => '.del-class#special',
		], $result);
	}

	#[Group('units')]
	public function testFormatIconWithValidValues(): void
	{
		$iconClass = 'icon-class-test';
		$title = 'Icon Test';

		$result = $this->bodyPreparer->formatIcon($iconClass, $title);

		$this->assertEquals([
			'ICON_CLASS' => 'icon-class-test',
			'ICON_TITLE' => 'Icon Test',
		], $result);
	}

	#[Group('units')]
	public function testFormatIconWithEmptyValues(): void
	{
		$iconClass = '';
		$title = '';

		$result = $this->bodyPreparer->formatIcon($iconClass, $title);

		$this->assertEquals([
			'ICON_CLASS' => '',
			'ICON_TITLE' => '',
		], $result);
	}

	#[Group('units')]
	public function testFormatIconWithSpecialCharacters(): void
	{
		$iconClass = '.icon-class@special';
		$title = 'Special@Icon!';

		$result = $this->bodyPreparer->formatIcon($iconClass, $title);

		$this->assertEquals([
			'ICON_CLASS' => '.icon-class@special',
			'ICON_TITLE' => 'Special@Icon!',
		], $result);
	}
}