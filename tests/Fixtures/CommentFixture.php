<?php

namespace App\Tests\Fixtures;

use App\Entity\Agent;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CommentFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $agentRepo = $manager->getRepository(Agent::class);

        $agent = $agentRepo->find(1);

        $user = new User();
        $user->setEmail('test@example.com');
        $manager->persist($user);

        $comment = new Comment();

        $comment->setAgent($agent);
        $comment->setCommenter($user)
            ->setText('comment')
            ->setDatetime(new \DateTime('now'));

        $manager->persist($comment);

        $manager->flush();
    }
}
