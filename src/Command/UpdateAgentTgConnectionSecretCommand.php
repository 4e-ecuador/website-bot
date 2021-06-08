<?php

namespace App\Command;

use App\Repository\AgentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'UpdateAgentTgConnectionSecret',
    description: 'Update user connection secret for telegram'
)]
class UpdateAgentTgConnectionSecretCommand extends Command
{
    public function __construct(
        private AgentRepository $agentRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $agents = $this->agentRepository->findAll();

        foreach ($agents as $agent) {
            if (!$agent->getTelegramConnectionSecret()) {
                $secret = bin2hex(random_bytes(24));
                $agent->setTelegramConnectionSecret($secret);

                $this->entityManager->persist($agent);
            }
        }

        $this->entityManager->flush();

        $io->success('Database has been updated.');

        return Command::SUCCESS;
    }
}
