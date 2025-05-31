<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Dru1x\ExpoPush\Tests\Unit\Exception;

use Dru1x\ExpoPush\Exception\RequestExceptionHandler;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class RequestExceptionHandlerTest extends TestCase
{
    protected PushErrorCollection $errors;
    protected RequestExceptionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->errors  = new PushErrorCollection();
        $this->handler = new RequestExceptionHandler(batchSize: 100, errors: $this->errors);
    }

    #[Test]
    public function invoke_with_fatal_request_exception_adds_push_error(): void
    {
        $fatalRequestException = $this->createMock(FatalRequestException::class);

        $handler = $this->handler;
        $handler($fatalRequestException, 1);

        $this->assertCount(1, $this->errors);

        $error = $this->errors->get(0);
        $this->assertEquals(PushErrorCode::Failed, $error->code);
        $this->assertEquals(100, $error->startIndex);
        $this->assertEquals(199, $error->endIndex);
    }

    #[Test]
    public function invoke_with_request_exception_adds_push_error(): void
    {
        $exceptionResponse = $this->createMock(Response::class);
        $exceptionResponse
            ->method('json')
            ->with('errors')
            ->willReturn([
                [
                    'code'    => 'PUSH_TOO_MANY_NOTIFICATIONS',
                    'message' => 'You are trying to send more than 100 push notifications in one request',
                ],
            ]);

        $requestException = $this->createMock(RequestException::class);
        $requestException
            ->method('getResponse')
            ->willReturn($exceptionResponse);

        $handler = $this->handler;
        $handler($requestException, 3);

        $this->assertCount(1, $this->errors);

        $error = $this->errors->get(0);
        $this->assertEquals(PushErrorCode::PushTooManyNotifications, $error->code);
        $this->assertEquals('You are trying to send more than 100 push notifications in one request', $error->message);
        $this->assertEquals(300, $error->startIndex);
        $this->assertEquals(399, $error->endIndex);
    }
}