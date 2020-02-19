<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\Services\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
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
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ConferenceController constructor.
     *
     * @param ConferenceRepository   $conferenceRepository
     * @param CommentRepository      $commentRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ConferenceRepository $conferenceRepository,
        CommentRepository $commentRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->conferenceRepository = $conferenceRepository;
        $this->commentRepository    = $commentRepository;
        $this->entityManager        = $entityManager;
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
     * @Route("/conference/{slug}", name="app.conference_show")
     *
     * @param Request     $request
     * @param Conference  $conference
     * @param SpamChecker $spamChecker
     * @param string      $photoDir
     *
     * @return Response
     */
    public function show(Request $request, Conference $conference, SpamChecker $spamChecker, string $photoDir): Response
    {

        $comment = new Comment();
        $form    = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {
            $comment->setConference($conference);

            /** @var File $photo */
            if ( $photo = $form['photo']->getData() ) {
                try {
                    $filename = bin2hex((random_bytes(6))).'.'.$photo->guessExtension();
                } catch (\Exception $e) {
                }

                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    // unable to upload the photo, give up
                }
                $comment->setPhotoFilename($filename);
            }
            $this->entityManager->persist($comment);

            $context = [
                'user_ip'    => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer'   => $request->headers->get('referer'),
                'permalink'  => $request->getUri(),
            ];


            dd($spamChecker->getSpamScore($comment, $context));
            if ( 2 === $spamChecker->getSpamScore($comment, $context) ) {
                throw new \RuntimeException('Blatant spam, go away!');
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('app.conference_show', ['slug' => $conference->getSlug()]);
        }

        $offset    = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->commentRepository->getCommentPaginator($conference, $offset);


        return $this->render(
            'conference/show.html.twig',
            [
                'conference'   => $conference,
                'comments'     => $paginator,
                'previous'     => $offset - CommentRepository::PAGINATOR_PER_PAGE,
                'next'         => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
                'comment_form' => $form->createView(),
            ]
        );
    }
}
