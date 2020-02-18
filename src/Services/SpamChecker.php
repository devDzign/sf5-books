<?php


namespace App\Services;


use App\Entity\Comment;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamChecker
{

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
    public function __construct(HttpClientInterface $client, string  $akismetKey)
    {
        $this->client = $client;
        $this->endpoint = sprintf('https://%s.rest.akismet.com/1.1/comment-check', $akismetKey);
    }

    public function getSpanScore(Comment $comment, array $context): int
    {
        $respose =  $this->client->request('POST', $this->endpoint, [
           'body' => array_merge(
               $context,
               [
                   'blog' => 'http//sf5-books.test',
                   'comment_type' =>'comment',
                   'comment_author' => $comment->getAuthor(),
                   'comment_author_email'  => $comment->getEmail(),
                   'comment_content' => $comment->getText(),
                   'comment_date_gmt' => $comment->getCreatedAt()->format('c'),
                   'blog_lang' => 'en',
                   'blog_charset'=> 'UTF-8',
                   'is_test' => true
               ]
           )
        ]);

        $headers =  $respose->getHeaders();

        if('discard'  === ($headers['k-akismet-pro-tip'][0] ?? '')){
            return 2;
        }

        $content = $respose->getContent();

        if(isset($headers['x-ikismet-debug-help'][0])){
            throw new \RuntimeException(sprintf('Unable to ckeck for spam: %s (%s).',$content, $headers['x-ikismet-debug-help'][0]));
        }

        return 'true' === $content ? 1 : 0;

    }


}