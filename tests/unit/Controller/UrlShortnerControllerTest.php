<?php

namespace App\Tests\Controller;

use App\Controller\UrlShortenerController;
use App\Entity\Urls;
use App\Service\UrlShortener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers App\Controller\UrlShortenerController
 */
class UrlShortenerControllerTest extends TestCase
{
    public function testShouldGetShortenedUrl()
    {
        $hash = "abc123ab";
        $expectedUrl = "http://youtube.com.br";

        $urlShortenerMock = $this
            ->getMockBuilder(UrlShortener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrlByHash'])
            ->getMock();

        $urlShortenerMock
            ->expects($this->once())
            ->method('getUrlByHash')
            ->with($hash)
            ->willReturn($expectedUrl);

        $urlShortenerController = new UrlShortenerController($urlShortenerMock);

        $response = $urlShortenerController->index($hash);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testShouldSaveUrl()
    {
        $url = "http://youtube.com.br";
        $expectedHash = "abc123ab";

        $expectedUrl = "http://localhost:8080/{$expectedHash}";

        $urlEntityMock = $this->createMock(Urls::class);

        $urlShortenerMock = $this
            ->getMockBuilder(UrlShortener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['saveUrl', 'createUrlHash'])
            ->getMock();

        $urlShortenerMock
            ->expects($this->once())
            ->method('createUrlHash')
            ->with($url)
            ->willReturn($expectedHash);

        $urlShortenerMock
            ->expects($this->once())
            ->method('saveUrl')
            ->willReturn($urlEntityMock);

        $urlShortenerControllerMock = $this
            ->getMockBuilder(UrlShortenerController::class)
            ->setConstructorArgs([$urlShortenerMock])
            ->onlyMethods(['generateUrl', 'json'])
            ->getMock();

        $urlShortenerControllerMock
            ->expects($this->once())
            ->method('generateUrl')
            ->with(
                "shortener_get",
                ['hash' => $expectedHash],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($expectedUrl);

        $urlShortenerControllerMock
            ->expects($this->once())
            ->method('json')
            ->with($expectedUrl)
            ->willReturn(new JsonResponse($expectedUrl));

        $response = $urlShortenerControllerMock->save($url);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
