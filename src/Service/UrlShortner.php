<?php

namespace App\Service;

use App\Entity\Urls;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlShortner
{
    private $validDuration;
    private $em;

    public function __construct(string $validDuration, EntityManagerInterface $em)
    {
        $this->validDuration = $validDuration;
        $this->em            = $em;
    }

    public function getUrlByHash(string $hash): string
    {
        $urlEntity = $this->em->getRepository('App:Urls')
            ->findOneBy(['hash' => $hash]);

        if (! $urlEntity instanceof Urls) {
            throw new NotFoundHttpException();
        }

        $expirationDate = $urlEntity->getExpiresAt();

        if (new DateTime() > $expirationDate) {
            throw new NotFoundHttpException();
        }

        $url = "http://{$urlEntity->getUrl()}";

        return $url;
    }

    public function saveUrl(string $hash, string $url, DateTime $date): Urls
    {
        $date->modify($this->validDuration);

        $newUrl = new Urls();
        $newUrl
            ->setHash($hash)
            ->setUrl($url)
            ->setExpiresAt($date);

        $this->em->persist($newUrl);
        $this->em->flush();

        return $newUrl;
    }

    public function createUrlHash(string $url)
    {
        return substr(md5(uniqid($url, true)), 0, 10);
    }
}
