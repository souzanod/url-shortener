<?php

namespace App\Controller;

use App\Service\UrlShortener;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlShortenerController extends AbstractController
{
    private $urlShortener;

    public function __construct(UrlShortener $urlShortener)
    {
        $this->urlShortener = $urlShortener;
    }

    /**
     * @Route("/{hash}", name="shortener_get", methods={"GET"})
     */
    public function index(string $hash): Response
    {
        $url = $this->urlShortener->getUrlByHash($hash);

        return $this->redirect($url);
    }

    /**
     * @Route("/{url}", name="shortener_save", methods={"POST"})
     */
    public function save(string $url): Response
    {
        $hash = $this->urlShortener->createUrlHash($url);

        $this->urlShortener->saveUrl($hash, $url, new DateTime());

        $shortenedUrl = $this->generateUrl(
            "shortener_get",
            ['hash' => $hash],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json($shortenedUrl);
    }
}
