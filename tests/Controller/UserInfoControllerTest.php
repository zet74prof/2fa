<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserInfoControllerTest extends WebTestCase
{
    private $entityManager;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    public function testIndexRedirectsUnauthenticated()
    {
        $this->client->request('GET', '/userinfo');
        $this->assertResponseRedirects('/login'); // Assuming standard redirect behavior
    }

    public function testIndexAuthenticated()
    {
        $user = new User();
        $user->setEmail('test_user@example.com');
        $user->setBirthDate((new \DateTime())->modify('-30 years'));
        $user->setContractStartDate((new \DateTime())->modify('-2 years'));

        $passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        $this->client->request('GET', '/userinfo');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'test_user@example.com'); // Assuming email is shown
        // We can add more specific assertions based on the template,
        // but without seeing the template, I can only guess what is displayed.
        // However, I know 'user.contractCategory' is passed, which returns int.
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up using DQL to be database agnostic and avoid hardcoded table names
        if ($this->entityManager) {
            $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}
