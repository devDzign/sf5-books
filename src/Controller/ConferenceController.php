<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    /**
     * @var ConferenceRepository
     */
    private $conferenceRepository;
    /**
     * @var CommentRepository
     */
    private $commentRepository;

    public function __construct(ConferenceRepository $conferenceRepository, CommentRepository $commentRepository)
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @Route("/", name="app.homepage")
     */
    public function index()
    {
        return $this->render(
            'conference/index.html.twig',
            [
                'conferences' => $this->conferenceRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/conference/{id}", name="app.conference_show")
     * @param Conference $conference
     *
     * @return Response
     */
    public function show(Conference $conference): Response
    {

        return $this->render(
            "conference/show.html.twig",
            [
                'conference'=> $conference,
                'comments' => $this->commentRepository->findBy(['conference' => $conference], ['createdAt' => 'ASC']),
            ]
        );
    }
}
