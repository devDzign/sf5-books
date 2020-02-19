<?php


namespace App\Fixture;


use App\Entity\Admin;
use App\Entity\Comment;
use App\Entity\Conference;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AppFixtures extends Fixture
{

    /**
     * @var EncoderFactoryInterface
     */
    private $encoder;

    public function __construct(EncoderFactoryInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $amstardam = new Conference();

        $amstardam
            ->setCity('Amsterdam')
            ->setYear('2020')
            ->setIsInternational(true)
            ;

        $manager->persist($amstardam);


        $paris = new Conference();
        $paris
            ->setCity('Paris')
            ->setYear('2020')
            ->setIsInternational(false);

        $manager->persist($paris);

        $comment1 = new Comment();

        $comment1
            ->setConference($amstardam)
            ->setAuthor('Fabien')
            ->setEmail('mourad@exemple.com')
            ->setText('This was a great conference.');

        $manager->persist($comment1);

        $admin = new Admin();

        $admin
            ->setRoles(['ROLE_ADMIN'])
            ->setUsername('admin')
            ->setPassword($this->encoder->getEncoder(Admin::class)->encodePassword('admin', null));

        $manager->persist($admin);

        $manager->flush();
    }
}