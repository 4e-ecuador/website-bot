<?php

namespace App\Command;

use App\Repository\AgentRepository;
use App\Repository\MapGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetMapGroupCommand extends Command
{
    protected static $defaultName = 'setMapGroup';
    /**
     * @var AgentRepository
     */
    private $agentRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var MapGroupRepository
     */
    private $mapGroupRepository;

    public function __construct(EntityManagerInterface $entityManager, AgentRepository $agentRepository, MapGroupRepository $mapGroupRepository)
    {
        $this->agentRepository = $agentRepository;
        $this->entityManager = $entityManager;
        $this->mapGroupRepository = $mapGroupRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $mapGroup = $this->mapGroupRepository->findOneBy(['name' => '4E']);

        if (!$mapGroup) {
            throw new \UnexpectedValueException('4E map group not found :(');
        }

        // var_dump($mapGroup->getName());
        $agents = $this->agentRepository->findAll();

        foreach ($agents as $agent) {
            $agent->setMapGroup($mapGroup);

            $this->entityManager->persist($agent);
        }

        $this->entityManager->flush();

        $io->success('Finished');

        return 0;
    }
}
