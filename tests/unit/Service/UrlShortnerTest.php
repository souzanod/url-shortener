<?php

namespace App\Tests\Controller;

use App\Entity\Urls;
use App\Service\UrlShortener;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @covers App\Service\UrlShortener
 */
class UrlShortenerTest extends TestCase
{
    public function testShouldGetUrlByHash()
    {
        $url = "youtube.com.br";
        $expectedUrl = "http://youtube.com.br";
        
        $expectedHash = "abc123ab";
        $expectedEntity = new Urls();

        $validDuration = "+30 seconds";

        $expectedDate = new DateTime();
        $expectedDate->modify($validDuration);

        $expectedEntity
            ->setHash($expectedHash)
            ->setUrl($url)
            ->setExpiresAt($expectedDate);

        $objRepositoryMock = $this
            ->getMockBuilder(ObjectRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneBy'])
            ->getMockForAbstractClass();

        $objRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['hash' => $expectedHash])
            ->willReturn($expectedEntity);

        $entityManagerMock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRepository'])
            ->getMockForAbstractClass();
            
        $entityManagerMock
            ->expects($this->once())
            ->method('getRepository')
            ->with('App:Urls')
            ->willReturn($objRepositoryMock);

        $urlShortener = new UrlShortener($validDuration, $entityManagerMock);

        $actualUrl = $urlShortener->getUrlByHash($expectedHash);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testShouldThrowExceptionOnGetUrlByHashWithNullResult()
    {
        $hash = "abc123ab";

        $validDuration = "+30 seconds";

        $objRepositoryMock = $this
            ->getMockBuilder(ObjectRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneBy'])
            ->getMockForAbstractClass();

        $objRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['hash' => $hash])
            ->willReturn(null);

        $entityManagerMock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRepository'])
            ->getMockForAbstractClass();
            
        $entityManagerMock
            ->expects($this->once())
            ->method('getRepository')
            ->with('App:Urls')
            ->willReturn($objRepositoryMock);

        $urlShortener = new UrlShortener($validDuration, $entityManagerMock);

        $this->expectException(NotFoundHttpException::class);

        $urlShortener->getUrlByHash($hash);
    }

    public function testShouldThrowExceptionOnGetUrlByHashWithExpiredDate()
    {
        $expectedUrl = "youtube.com.br";
        
        $expectedHash = "abc123ab";
        $expectedEntity = new Urls();

        $validDuration = "+30 seconds";

        $expectedDate = new DateTime('2009-02-15 15:16:17');

        $expectedEntity
            ->setHash($expectedHash)
            ->setUrl($expectedUrl)
            ->setExpiresAt($expectedDate);

        $objRepositoryMock = $this
            ->getMockBuilder(ObjectRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneBy'])
            ->getMockForAbstractClass();

        $objRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['hash' => $expectedHash])
            ->willReturn($expectedEntity);

        $entityManagerMock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRepository'])
            ->getMockForAbstractClass();
            
        $entityManagerMock
            ->expects($this->once())
            ->method('getRepository')
            ->with('App:Urls')
            ->willReturn($objRepositoryMock);

        $urlShortener = new UrlShortener($validDuration, $entityManagerMock);

        $this->expectException(NotFoundHttpException::class);

        $urlShortener->getUrlByHash($expectedHash);
    }

    public function testShouldSaveUrl()
    {
        $expectedUrl = "youtube.com.br";
        $expectedHash = "abc123ab";
        $expectedEntity = new Urls();

        $validDuration = "+30 seconds";
        
        $date = new DateTime();
        $expectedDate = clone($date);
        $expectedDate->modify($validDuration);

        $expectedEntity
            ->setHash($expectedHash)
            ->setUrl($expectedUrl)
            ->setExpiresAt($expectedDate);

        $entityManagerMock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($expectedEntity);

        $entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $urlShortener = new UrlShortener($validDuration, $entityManagerMock);

        $actualEntity = $urlShortener->saveUrl($expectedHash, $expectedUrl, $date);

        $this->assertEquals($expectedEntity, $actualEntity);
    }

    public function testShouldCreateUrlHash()
    {
        $url = "http://youtube.com.br";
        $validDuration = "+30 seconds";

        $entityManagerMock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $urlShortener = new UrlShortener($validDuration, $entityManagerMock);

        $actualHash = $urlShortener->createUrlHash($url);

        $hashLen = strlen($actualHash);

        $this->assertMatchesRegularExpression("/^[A-Za-z0-9]*$/", $actualHash);
        $this->assertGreaterThanOrEqual(5, $hashLen);
        $this->assertLessThanOrEqual(10, $hashLen);
    }
}
