<?php

namespace Tests\Unit\Modules\Users\Controller;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\Datatable\DatatableFacadeInterface;
use App\Framework\Utils\Datatable\DatatableTemplatePreparer;
use App\Modules\Users\Controller\ShowDatatableController;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ShowDatatableControllerTest extends TestCase
{
	private readonly ShowDatatableController $controller;
	private readonly DatatableFacadeInterface $facadeMock;
	private readonly DatatableTemplatePreparer $templatePreparerMock;
	private readonly ServerRequestInterface $requestMock;
	private readonly ResponseInterface $responseMock;
	private readonly Translator $translatorMock;
	private readonly Session $sessionMock;

	private $responseBodyMock;

	protected function setUp(): void
	{
		$this->facadeMock           = $this->createMock(DatatableFacadeInterface::class);
		$this->templatePreparerMock = $this->createMock(DatatableTemplatePreparer::class);
		$this->requestMock          = $this->createMock(ServerRequestInterface::class);
		$this->responseMock         = $this->createMock(ResponseInterface::class);
		$this->responseBodyMock     = $this->createMock(StreamInterface::class);
		$this->translatorMock       = $this->createMock(Translator::class);
		$this->sessionMock          = $this->createMock(Session::class);

		$this->responseMock->method('getBody')->willReturn($this->responseBodyMock);

		$this->controller = new ShowDatatableController($this->facadeMock, $this->templatePreparerMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testShowMethodReturnsResponseWithSerializedTemplateData(): void
	{
		$templateData = ['key' => 'value'];

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

		$this->templatePreparerMock->expects($this->once())->method('preparerUITemplate')
			->with(['dataGrid' => 'value'])
			->willReturn($templateData);

		$this->responseBodyMock->expects($this->once())->method('write')
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