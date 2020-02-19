<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        echo $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback!');
    }

    public function testConferencePage()
    {
        $client = static::createClient();

        $crawler  =  $client->request('GET', '/');

        $this->assertCount(2, $crawler->filter('h4'));

        $client->clickLink('View');

        echo $client->getResponse();

        $this->assertPageTitleContains('Amsterdam');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Amsterdam 2020');
        $this->assertSelectorNotExists('div:contains("there are 1 comments")');
    }

    public function testCommentSubmission()
    {
        $client = static::createClient();

        $crawler  =  $client->request('GET', '/conference/amsterdam-2020');

        $client->submitForm('Submit', [
            'comment_form[author]'=>'Mourad',
            'comment_form[text]'=> 'Some feedback from automated functional test',
            'comment_form[email]'=>'mourad@example.com',
            'comment_form[photo]'=>dirname(__DIR__,2).'/public/images/under-construction.gif',
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorExists('div:contains("There are 2 comments")');

    }
}
