<?php

namespace App\Controller;

use App\Service\UrlShortner;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlShortnerController extends AbstractController
{
    private $urlShortner;

    public function __construct(UrlShortner $urlShortner)
    {
        $this->urlShortner = $urlShortner;
    }

    /**
     * @Route("/{hash}", name="shortner_get", methods={"GET"})
     */
    public function index(string $hash): Response
    {
        $url = $this->urlShortner->getUrlByHash($hash);

        return $this->redirect($url);
    }

    /**
     * @Route("/{url}", name="shortner_save", methods={"POST"})
     */
    public function save(string $url): Response
    {
        $hash = $this->urlShortner->createUrlHash($url);

        $this->urlShortner->saveUrl($hash, $url, new DateTime());

        $shortenedUrl = $this->generateUrl(
            "shortner_get",
            ['hash' => $hash],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json($shortenedUrl);
    }
}
