<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeBadgeGroupsCommand extends Command
{
    protected static $defaultName = 'makeBadgeGroups';

    /**
     * @var string
     */
    private $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir.'/assets/images/badges';

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

        $groups = [];

        foreach (new \DirectoryIterator($this->rootDir) as $item) {
            if ($item->isDot()) {
                continue;
            }

            $badgeName = $item->getBasename();

            if (0 === strpos($badgeName, 'Anomaly_')) {
                $groups['anomaly'][] = $badgeName;
            }

            $io->writeln($item->getBasename());
        }

        var_dump($groups);

        foreach ($groups as $type => $items) {
            $io->writeln('[');
            $io->writeln("'$type' =>");
            $io->writeln("'".implode("', '", $items)."'");
            $io->writeln(']');
        }

        $io->success('Finished!');

        $d = [
            'anomaly' =>
                'Anomaly_NemesisMyriad.png',
            'Anomaly_Shonin.png',
            'Anomaly_RecursionPrime.png',
            'Anomaly_Interitus.png',
            'Anomaly_Umbra.png',
            'Anomaly_ViaNoir.png',
            'Anomaly_AbaddonPrime.png',
            'Anomaly_Persepolis.png',
            'Anomaly_Darsana.png',
            'Anomaly_Initio.png',
            'Anomaly_DarsanaPrime.png',
            'Anomaly_ViaLux.png',
            'Anomaly_Obsidian.png',
            'Anomaly_AegisNova.png',
            'Anomaly_Helios.png',
            'Anomaly_CassandraPrime.png',
            'Anomaly_Abaddon.png',
            'Anomaly_EXO5.png',
            'Anomaly_13MAGNUSReawakens.png',
            'Anomaly_Recursion.png',
        ];

        return 0;
    }
}
