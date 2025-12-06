<?php

namespace App\Tests\Command;

use App\Command\AddUserCommand;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddUserCommandTest extends TestCase
{
    private $hasher;
    private $entityManager;
    private $validator;

    protected function setUp(): void
    {
        $this->hasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    public function testExecuteSuccess()
    {
        $command = new AddUserCommand($this->hasher, $this->entityManager, $this->validator);
        $commandTester = new CommandTester($command);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->hasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashed_password');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(User::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $commandTester->execute([
            'email' => 'test@example.com',
            'password' => 'password123',
            '--role' => ['ROLE_ADMIN']
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('User test@example.com successfully created!', $output);
    }

    public function testExecuteInvalidEmail()
    {
        $command = new AddUserCommand($this->hasher, $this->entityManager, $this->validator);
        $commandTester = new CommandTester($command);

        // Simulate validation error
        $violations = new ConstraintViolationList();
        $violations->add(new \Symfony\Component\Validator\ConstraintViolation('Invalid email', null, [], 'invalid-email', 'email', 'invalid-email'));

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $result = $commandTester->execute([
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('The email is not valid!', $output);
    }

    public function testExecuteShortPassword()
    {
        $command = new AddUserCommand($this->hasher, $this->entityManager, $this->validator);
        $commandTester = new CommandTester($command);

        $this->validator->expects($this->never())->method('validate');
        $this->entityManager->expects($this->never())->method('persist');

        $result = $commandTester->execute([
            'email' => 'test@example.com',
            'password' => '123', // Too short
        ]);

        $this->assertEquals(Command::FAILURE, $result);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('You must pass the password (at least 6 characters!)', $output);
    }
}
