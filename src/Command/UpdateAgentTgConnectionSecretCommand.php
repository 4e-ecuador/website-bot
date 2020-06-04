<?php

namespace App\Command;

use App\Repository\AgentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateAgentTgConnectionSecretCommand extends Command
{
    protected static $defaultName = 'UpdateAgentTgConnectionSecret';

    /**
     * @var AgentRepository
     */
    private $agentRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(AgentRepository $agentRepository, EntityManagerInterface $entityManager)
    {
        $this->agentRepository = $agentRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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

        return 0;
    }
}
