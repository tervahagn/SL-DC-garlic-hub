<?php

namespace Tests\Unit\Modules\Playlists\Controller;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\Datatable\DatatableTemplatePreparer;
use App\Modules\Playlists\Controller\ShowDatatableController;
use App\Modules\Playlists\Helper\Datatable\ControllerFacade as Facade;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ShowDatatableControllerTest extends TestCase
{
	private readonly ShowDatatableController $controller;
	private readonly Facade $facadeMock;
	private readonly DatatableTemplatePreparer $templatePreparerMock;
	private readonly ServerRequestInterface $requestMock;
	private readonly ResponseInterface $responseMock;
	private readonly Translator $translatorMock;
	private readonly Session $sessionMock;
	private readonly StreamInterface $streamInterfaceMock;

	protected function setUp(): void
	{
		$this->facadeMock           = $this->createMock(Facade::class);
		$this->templatePreparerMock = $this->createMock(DatatableTemplatePreparer::class);
		$this->requestMock          = $this->createMock(ServerRequestInterface::class);
		$this->responseMock         = $this->createMock(ResponseInterface::class);
		$this->translatorMock       = $this->createMock(Translator::class);
		$this->sessionMock          = $this->createMock(Session::class);
		$this->streamInterfaceMock  = $this->createMock(StreamInterface::class);
		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);

		$this->controller = new ShowDatatableController($this->facadeMock, $this->templatePreparerMock);
	}

	#[Group('units')]
	public function testShowMethodReturnsResponseWithSerializedTemplateData(): void
	{
		$this->requestMock->expects($this->exactly(2))->method('getAttribute')
			->willReturnMap([
				['translator', null, $this->translatorMock],
				['session', null, $this->sessionMock],
			]);

		$this->facadeMock->expects($this->once())->method('configure')
			->with($this->translatorMock, $this->sessionMock);

		$this->facadeMock->expects($this->once())->method('processSubmittedUserInput');
		$this->facadeMock->expects($this->once())->method('prepareDataGrid');
		$this->facadeMock->expects($this->once())->method('prepareUITemplate')
			->willReturn(['dataGrid' => 'value']);

		$templateData = ['key' => 'value'];
		$this->templatePreparerMock->expects($this->once())->method('preparerUITemplate')
			->with(['dataGrid' => 'value'])
			->willReturn($templateData);

		$templateData['this_layout']['data']['create_playlist_contextmenu'] = ['a contextmenu stub'];

		$this->facadeMock->expects($this->once())->method('prepareContextMenu')->willReturn( ['a contextmenu stub']);

		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);

		$this->streamInterfaceMock->expects($this->once())->method('write')
			->with(serialize($templateData));

		$this->responseMock
			->expects($this->once())
			->method('withHeader')
			->with('Content-Type', 'text/html')
			->willReturnSelf();

		$result = $this->controller->show($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $result);
	}

}
