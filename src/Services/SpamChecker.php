<?php


namespace App\Services;


use App\Entity\Comment;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamChecker
{

    public const NOT_SPAM = 0;
    public const MAYBE_SPAM = 1;
    public const BLATANT_SPAM = 2;

    public const AKISMET_URL = 'https://%s.rest.akismet.com/1.1/comment-check';

    /**
     * @var HttpClientInterface
     */
    private $client;
    /**
     * @var string
     */
    private $akismetKey;

    /**
     * @var string
     */
    private $endpoint;


    /**
     * SpamChecker constructor.
     *
     * @param HttpClientInterface $client
     * @param string              $akismetKey
     */
    public function __construct(HttpClientInterface $client, string $akismetKey)
    {
        $this->client   = $client;
        $this->endpoint = sprintf(self::AKISMET_URL, $akismetKey);
    }


    /**
     * @param Comment $comment
     * @param array   $context
     *
     * @return int
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     *
     */
    public function getSpamScore(Comment $comment, array $context): int
    {
        $response = $this->client->request(
            'POST',
            $this->endpoint,
            [
                'body' => array_merge(
                    $context,
                    [
                        'blog'                 => 'http//sf5-books.test',
                        'comment_type'         => 'comment',
                        'comment_author'       => $comment->getAuthor(),
                        'comment_author_email' => $comment->getEmail(),
                        'comment_content'      => $comment->getText(),
                        'comment_date_gmt'     => $comment->getCreatedAt()->format('c'),
                        'blog_lang'            => 'en',
                        'blog_charset'         => 'UTF-8',
                        'is_test'              => true,
                    ]
                ),
            ]
        );

        $headers = $response->getHeaders();

        if ( 'discard' === ($headers['x-akismet-pro-tip'][0] ?? '') ) {
            return self::BLATANT_SPAM;
        }

        $content = $response->getContent();

        if ( isset($headers['x-akismet-debug-help'][0]) ) {
            throw new \RuntimeException(sprintf('Unable to ckeck for spam: %s (%s).', $content, $headers['x-akismet-debug-help'][0]));
        }

        return ('true' === $content) ? self::MAYBE_SPAM : self::NOT_SPAM;

    }


}