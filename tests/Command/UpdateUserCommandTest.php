<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\EntityManagerWrapper;
use App\Tests\UserUtils;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group cli
 */
class UpdateUserCommandTest extends KernelTestCase
{
    protected const Command = 'app:update-user';

    use EntityManagerWrapper;
    use UserUtils;

    /**
     * Convenience wrapper to execute this test's command.
     *
     * @param Application $application
     * @param array $args
     * @param bool $expectPass
     * @return CommandTester
     */
    protected function executeCommand(Application $application, array $args, bool $expectPass = true): CommandTester
    {
        $command = $application->find(self::Command);
        $commandTester = new CommandTester($command);
        $commandTester->execute($args);

        if ($expectPass) {
            $commandTester->assertCommandIsSuccessful();
        }

        return $commandTester;
    }

    /**
     * @test
     */
    public function change_email(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $this->createUser('me@me.com', 'asdf');

        $tester = $this->executeCommand($application, [
            // pass arguments to the helper
            'email' => 'me@me.com',
            '--email' => 'you@me.com',
        ]);

        // The output of the command in the console.
        $output = $tester->getDisplay();
        $this->assertStringContainsString('User updated', $output);

        /** @var UserRepository $userRepo */
        $userRepo = $this->entityManager()->getRepository(User::class);
        $foundUser = $userRepo->findOneByEmail('you@me.com');

        self::assertEquals('you@me.com', $foundUser->getEmail());
    }

}
