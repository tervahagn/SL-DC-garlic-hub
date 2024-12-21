<?php

namespace Tests\Unit\Modules\User;

use App\Framework\Core\Translate\Translator;
use App\Framework\User\UserService;
use App\Framework\Utils\Html\CsrfTokenField;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Framework\Utils\Html\PasswordField;
use App\Framework\Utils\Html\TextField;
use App\Modules\User\EditPasswordController;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use SlimSession\Helper;
use stdClass;

class EditPasswordControllerTest extends TestCase
{
	private FormBuilder $formBuilderMock;
	private UserService $userServiceMock;
	private Request $requestMock;
	private Response $responseMock;
	private EditPasswordController $controller;
	private Helper $sessionMock;
	private Messages $flashMock;
	private Translator $translatorMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->formBuilderMock = $this->createMock(FormBuilder::class);
		$this->userServiceMock = $this->createMock(UserService::class);
		$this->requestMock     = $this->createMock(Request::class);
		$this->responseMock    = $this->createMock(Response::class);
		$this->sessionMock     = $this->createMock(Helper::class);
		$this->flashMock 	   = $this->createMock(Messages::class);
		$this->translatorMock  = $this->createMock(Translator::class);

		$this->controller = new EditPasswordController($this->formBuilderMock, $this->userServiceMock);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditPasswordRedirectsOnSuccess(): void
	{
		$this->requestMock->expects($this->exactly(2))->method('getAttribute')
			->willReturnCallback(function ($attribute)
			{
				return match ($attribute)
				{
					'flash' => $this->flashMock,
					'session' => $this->sessionMock,
					default => null,
				};
			});
		$body = ['csrf_token' => 'valid_token', 'edit_password' => 'newPassword123', 'repeat_password' => 'newPassword123'];
		$this->requestMock->expects($this->once())->method('getParsedBody')->willReturn($body);
		$this->sessionMock->expects($this->exactly(2))->method('get')
			->willReturnCallback(function ($param)
			{
				return match ($param)
				{
					'user' => ['UID' => 1],
					'csrf_token' => 'valid_token',
					default => null,
				};
			});
		$this->userServiceMock->expects($this->once())->method('updatePassword')
			 ->with(1, 'newPassword123')
			 ->willReturn(1);

		$this->flashMock->expects($this->once())->method('addMessage')->with('success', 'User data changed');

		$this->responseMock->expects($this->once())->method('withHeader')
						   ->with('Location', '/user/edit')
						   ->willReturnSelf();

		$this->responseMock->expects($this->once())->method('withStatus')
						   ->with(302)
						   ->willReturnSelf();

		$response = $this->controller->editPassword($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $response);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testPostActionsWithInvalidCsrfToken(): void
	{
		$this->requestMock->expects($this->exactly(2))->method('getAttribute')
						  ->willReturnCallback(function ($attribute)
						  {
							  return match ($attribute)
							  {
								  'flash' => $this->flashMock,
								  'session' => $this->sessionMock,
								  default => null,
							  };
						  });
		$body = ['csrf_token' => 'valid_token', 'edit_password' => 'newPassword123', 'repeat_password' => 'newPassword123'];
		$this->requestMock->expects($this->once())->method('getParsedBody')->willReturn($body);
		$this->sessionMock->expects($this->once())->method('get')
			->with('csrf_token')
			->willReturn('invalid_token');

		$this->userServiceMock->expects($this->never())->method('updatePassword');
		$this->flashMock->expects($this->once())->method('addMessage')->with('error', 'CSRF Token mismatch');

		$this->responseMock->expects($this->once())->method('withHeader')
						   ->with('Location', '/user/edit')
						   ->willReturnSelf();

		$this->responseMock->expects($this->once())->method('withStatus')
						   ->with(302)
						   ->willReturnSelf();

		$response = $this->controller->editPassword($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $response);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testPostActionsWithTooSmallPassword(): void
	{
		$this->requestMock->expects($this->exactly(2))->method('getAttribute')
						  ->willReturnCallback(function ($attribute)
						  {
							  return match ($attribute)
							  {
								  'flash' => $this->flashMock,
								  'session' => $this->sessionMock,
								  default => null,
							  };
						  });
		$body = ['csrf_token' => 'valid_token', 'edit_password' => '123'];
		$this->requestMock->expects($this->once())->method('getParsedBody')->willReturn($body);
		$this->sessionMock->expects($this->once())->method('get')
						  ->with('csrf_token')
						  ->willReturn('valid_token');

		$this->userServiceMock->expects($this->never())->method('updatePassword');
		$this->flashMock->expects($this->once())->method('addMessage')->with('error', 'Password too small');

		$this->responseMock->expects($this->once())->method('withHeader')
						   ->with('Location', '/user/edit')
						   ->willReturnSelf();

		$this->responseMock->expects($this->once())->method('withStatus')
						   ->with(302)
						   ->willReturnSelf();

		$response = $this->controller->editPassword($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $response);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testPostActionsWithDifferentPasswords(): void
	{
		$this->requestMock->expects($this->exactly(2))->method('getAttribute')
						  ->willReturnCallback(function ($attribute)
						  {
							  return match ($attribute)
							  {
								  'flash' => $this->flashMock,
								  'session' => $this->sessionMock,
								  default => null,
							  };
						  });
		$body = ['csrf_token' => 'valid_token', 'edit_password' => 'newPassword123', 'repeat_password' => 'diffPassword123'];
		$this->requestMock->expects($this->once())->method('getParsedBody')->willReturn($body);
		$this->sessionMock->expects($this->once())->method('get')
						  ->with('csrf_token')
						  ->willReturn('valid_token');

		$this->userServiceMock->expects($this->never())->method('updatePassword');
		$this->flashMock->expects($this->once())->method('addMessage')->with('error', 'Password not same');

		$this->responseMock->expects($this->once())->method('withHeader')
						   ->with('Location', '/user/edit')
						   ->willReturnSelf();

		$this->responseMock->expects($this->once())->method('withStatus')
						   ->with(302)
						   ->willReturnSelf();

		$response = $this->controller->editPassword($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $response);
	}

	/**
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testEditPasswordFailsDB(): void
	{
		$this->requestMock->expects($this->exactly(2))->method('getAttribute')
						  ->willReturnCallback(function ($attribute)
						  {
							  return match ($attribute)
							  {
								  'flash' => $this->flashMock,
								  'session' => $this->sessionMock,
								  default => null,
							  };
						  });
		$body = ['csrf_token' => 'valid_token', 'edit_password' => 'newPassword123', 'repeat_password' => 'newPassword123'];
		$this->requestMock->expects($this->once())->method('getParsedBody')->willReturn($body);
		$this->sessionMock->expects($this->exactly(2))->method('get')
						  ->willReturnCallback(function ($param)
						  {
							  return match ($param)
							  {
								  'user' => ['UID' => 1],
								  'csrf_token' => 'valid_token',
								  default => null,
							  };
						  });
		$this->userServiceMock->expects($this->once())->method('updatePassword')
							  ->with(1, 'newPassword123')
							  ->willReturn(0);

		$this->flashMock->expects($this->once())->method('addMessage')->with('error', 'User data could not be changed');

		$this->responseMock->expects($this->once())->method('withHeader')
						   ->with('Location', '/user/edit')
						   ->willReturnSelf();

		$this->responseMock->expects($this->once())->method('withStatus')
						   ->with(302)
						   ->willReturnSelf();

		$response = $this->controller->editPassword($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $response);
	}


	/**
	 * @throws InvalidArgumentException
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testShowFormGeneratesResponse(): void
	{
		$this->requestMock->expects($this->exactly(2))->method('getAttribute')
			  ->willReturnCallback(function ($attribute)
			  {
				  return match ($attribute)
				  {
					  'translator' => $this->translatorMock,
					  'session' => $this->sessionMock,
					  default => null,
				  };
			  });

		$fields = [
				'edit_password' => [
					'type' => FieldType::PASSWORD,
					'id' => 'edit_password',
					'name' => 'edit_password',
					'value' => '',
					'rules' => ['required' => true, 'minlength' => 8],
					'default_value' => ''
				],
				'repeat_password' => [
					'type' => FieldType::PASSWORD,
					'id' => 'repeat_password',
					'name' => 'repeat_password',
					'rules' => ['required' => true, 'minlength' => 8],
					'default_value' => ''
				],
				'csrf_token' => [
					'type' => FieldType::CSRF,
					'id' => 'csrf_token',
					'name' => 'csrf_token',
				]
		];

		$formFieldsMocks = [
			'edit_password' => $this->createMock(PasswordField::class),
			'repeat_password' => $this->createMock(PasswordField::class),
			'csrf_token' => $this->createMock(CsrfTokenField::class)
		];

		$this->formBuilderMock->expects($this->exactly(3))->method('createField')
			->willReturnCallback(function ($param) use ($fields, $formFieldsMocks)
			{
				if ($param === $fields['edit_password'])
					return $formFieldsMocks['edit_password'];
				else if ($param === $fields['repeat_password'])
					return $formFieldsMocks['repeat_password'];
				else if ($param === $fields['csrf_token'])
					return $formFieldsMocks['csrf_token'];
				else
					return null;
			});

		$formFieldsMocks['csrf_token']->expects($this->once())->method('getValue')->willReturn('valid_token');
		$this->sessionMock->expects($this->once())->method('set')->with('csrf_token', 'valid_token');

		$this->responseMock->expects($this->once())
			 ->method('getBody')
			 ->willReturn($this->createMock(StreamInterface::class));


		$this->responseMock->expects($this->once())->method('withHeader')
		     ->with('Content-Type', 'text/html')
			 ->willReturnSelf()
		;
		$response = $this->controller->showForm($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $response);
	}

}
