<?php

namespace App\Tests\Controller;

use App\Entity\Urls;
use App\Service\UrlShortner;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @covers App\Service\UrlShortner
 */
class UrlShortnerTest extends TestCase
{
    public function testShouldGetUrlByHash()
    {
        $url = "studos.com.br";
        $expectedUrl = "http://studos.com.br";
        
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

        $urlShortner = new UrlShortner($validDuration, $entityManagerMock);

        $actualUrl = $urlShortner->getUrlByHash($expectedHash);

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

        $urlShortner = new UrlShortner($validDuration, $entityManagerMock);

        $this->expectException(NotFoundHttpException::class);

        $urlShortner->getUrlByHash($hash);
    }

    public function testShouldThrowExceptionOnGetUrlByHashWithExpiredDate()
    {
        $expectedUrl = "studos.com.br";
        
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

        $urlShortner = new UrlShortner($validDuration, $entityManagerMock);

        $this->expectException(NotFoundHttpException::class);

        $urlShortner->getUrlByHash($expectedHash);
    }

    public function testShouldSaveUrl()
    {
        $expectedUrl = "studos.com.br";
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

        $urlShortner = new UrlShortner($validDuration, $entityManagerMock);

        $actualEntity = $urlShortner->saveUrl($expectedHash, $expectedUrl, $date);

        $this->assertEquals($expectedEntity, $actualEntity);
    }

    public function testShouldCreateUrlHash()
    {
        $url = "http://studos.com.br";
        $validDuration = "+30 seconds";

        $entityManagerMock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $urlShortner = new UrlShortner($validDuration, $entityManagerMock);

        $actualHash = $urlShortner->createUrlHash($url);

        $hashLen = strlen($actualHash);

        $this->assertMatchesRegularExpression("/^[A-Za-z0-9]*$/", $actualHash);
        $this->assertGreaterThanOrEqual(5, $hashLen);
        $this->assertLessThanOrEqual(10, $hashLen);
    }
}
