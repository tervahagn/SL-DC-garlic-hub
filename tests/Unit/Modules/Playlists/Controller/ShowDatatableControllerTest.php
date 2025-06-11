<?php

namespace Tests\Unit\Modules\Playlists\Controller;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\Datatable\DatatableTemplatePreparer;
use App\Modules\Playlists\Controller\ShowDatatableController;
use App\Modules\Playlists\Helper\Datatable\ControllerFacade as Facade;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ShowDatatableControllerTest extends TestCase
{
	private readonly ShowDatatableController $controller;
	private readonly Facade&MockObject $facadeMock;
	private readonly DatatableTemplatePreparer&MockObject $templatePreparerMock;
	private readonly ServerRequestInterface&MockObject $requestMock;
	private readonly ResponseInterface&MockObject $responseMock;
	private readonly Translator&MockObject $translatorMock;
	private readonly Session&MockObject $sessionMock;
	private readonly StreamInterface&MockObject $streamInterfaceMock;

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

		$this->responseMock->expects($this->once())->method('withHeader')
			->with('Content-Type', 'text/html')
			->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')
		    ->with(200);

		$response = $this->controller->show($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

}
