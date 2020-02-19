<?php

namespace App\Tests\Controller;


use Symfony\Component\Panther\PantherTestCase;

class ConferenceControllerBrowserTest extends PantherTestCase
{
    public function testIndex()
    {
        $client  = static::createPantherClient([
            'external_base_uri' => 'http://127.0.0.1:8000'
        ]);

       $client->request('GET', '/');

        $this->assertSelectorTextContains('h2', 'Give your feedback!');
    }

}
