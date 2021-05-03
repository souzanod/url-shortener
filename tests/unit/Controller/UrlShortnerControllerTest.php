<?php

namespace App\Tests\Controller;

use App\Controller\UrlShortnerController;
use App\Entity\Urls;
use App\Service\UrlShortner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers App\Controller\UrlShortnerController
 */
class UrlShortnerControllerTest extends TestCase
{
    public function testShouldGetShortenedUrl()
    {
        $hash = "abc123ab";
        $expectedUrl = "http://studos.com.br";

        $urlShortenerMock = $this
            ->getMockBuilder(UrlShortner::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrlByHash'])
            ->getMock();

        $urlShortenerMock
            ->expects($this->once())
            ->method('getUrlByHash')
            ->with($hash)
            ->willReturn($expectedUrl);

        $urlShortenerController = new UrlShortnerController($urlShortenerMock);

        $response = $urlShortenerController->index($hash);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testShouldSaveUrl()
    {
        $url = "http://studos.com.br";
        $expectedHash = "abc123ab";

        $expectedUrl = "http://localhost:8080/{$expectedHash}";

        $urlEntityMock = $this->createMock(Urls::class);

        $urlShortenerMock = $this
            ->getMockBuilder(UrlShortner::class)
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
            ->getMockBuilder(UrlShortnerController::class)
            ->setConstructorArgs([$urlShortenerMock])
            ->onlyMethods(['generateUrl', 'json'])
            ->getMock();

        $urlShortenerControllerMock
            ->expects($this->once())
            ->method('generateUrl')
            ->with(
                "shortner_get",
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
