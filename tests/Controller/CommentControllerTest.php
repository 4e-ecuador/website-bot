<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Entity\Comment;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class CommentControllerTest extends WebTestCase
{
    private function getUser(): User
    {
        /** @var User $user */
        $user = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        return $user;
    }

    private function getComment(): Comment
    {
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $comment = $em->getRepository(Comment::class)->findOneBy([]);
        if (!$comment instanceof Comment) {
            // Create a comment if none exists (in case fixture was deleted by a previous run)
            $agent = $this->getAgent();
            $user = $this->getUser();
            $comment = new Comment();
            $comment->setAgent($agent)->setCommenter($user)->setText('test')->setDatetime(new \DateTime());
            $em->persist($comment);
            $em->flush();
        }

        return $comment;
    }

    private function getAgent(): Agent
    {
        /** @var Agent $agent */
        $agent = static::getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Agent::class)
            ->findOneBy([]);

        return $agent;
    }

    public function testIndexRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/comment/');
        self::assertResponseRedirects();
    }

    public function testIndexRendersForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/comment/');
        self::assertResponseIsSuccessful();
    }

    public function testNewCommentRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_GET, '/comment/new');
        self::assertResponseIsSuccessful();
    }

    public function testShowCommentRendersPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $comment = $this->getComment();
        $client->request(Request::METHOD_GET, '/comment/'.$comment->getId());
        self::assertResponseIsSuccessful();
    }

    public function testEditCommentRendersForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $comment = $this->getComment();
        $client->request(Request::METHOD_GET, '/comment/'.$comment->getId().'/edit');
        self::assertResponseIsSuccessful();
    }

    public function testDeleteCommentWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $comment = $this->getComment();
        $client->request(Request::METHOD_DELETE, '/comment/'.$comment->getId(), [
            '_token' => 'invalid',
        ]);
        self::assertResponseRedirects('/comment/');
    }

    public function testGetSingleCommentReturnsJson(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $comment = $this->getComment();
        $client->request(Request::METHOD_POST, '/comment/fetch', [
            'comment_id' => $comment->getId(),
        ]);
        self::assertResponseIsSuccessful();
        self::assertJson((string) $client->getResponse()->getContent());
    }

    public function testGetCommentsByAgentReturnsJson(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $agent = $this->getAgent();
        $client->request(Request::METHOD_POST, '/comment/get-comments-by-agent', [
            'agent_id' => $agent->getId(),
        ]);
        self::assertResponseIsSuccessful();
        self::assertJson((string) $client->getResponse()->getContent());
    }

    public function testGetCommentsByAgentWithInvalidId(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_POST, '/comment/get-comments-by-agent', [
            'agent_id' => 99999999,
        ]);
        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('', $data['comments']);
    }

    public function testGetSingleWithInvalidIdReturns404(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());
        $client->request(Request::METHOD_POST, '/comment/fetch', [
            'comment_id' => 999999999,
        ]);
        self::assertResponseStatusCodeSame(404);
    }

    public function testEditCommentFormSubmission(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $comment = $this->getComment();
        $crawler = $client->request(Request::METHOD_GET, '/comment/'.$comment->getId().'/edit');
        self::assertResponseIsSuccessful();

        // The edit page has the edit form first, then the delete form
        $form = $crawler->filter('form')->first()->form();
        $form['comment[text]'] = 'Updated comment text';
        $client->submit($form);

        self::assertResponseRedirects('/comment/');
    }

    public function testDeleteCommentWithValidToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        // Create a comment specifically for deletion
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $agent = $this->getAgent();
        $user = $this->getUser();

        $comment = new \App\Entity\Comment();
        $comment->setAgent($agent)->setCommenter($user)->setText('To be deleted')->setDatetime(new \DateTime());
        $em->persist($comment);
        $em->flush();

        // Get CSRF token from show page
        $crawler = $client->request(Request::METHOD_GET, '/comment/'.$comment->getId());
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request(Request::METHOD_DELETE, '/comment/'.$comment->getId(), [
            '_token' => $token,
        ]);
        self::assertResponseRedirects('/comment/');
    }
}
