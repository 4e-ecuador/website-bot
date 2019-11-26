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

            $badgeName = substr($item->getBasename(), 0, strrpos($item->getBasename(), '.'));
            if (0 === strpos($badgeName, 'Anomaly_')) {
                $groups['anomaly']['xm'][] = $badgeName;
            } elseif (0 === strpos($badgeName, 'Badge_Innovator_')) {
                $groups['annual']['innovator'][] = $badgeName;
            } elseif (0 === strpos($badgeName, 'Badge_Vanguard_')) {
                $groups['annual']['vanguard'][] = $badgeName;
            } elseif (0 === strpos($badgeName, 'Badge_Sage_')) {
                $groups['annual']['sage'][] = $badgeName;
            } elseif (0 === strpos($badgeName, 'EventBadge_')) {
                $parts = explode('_', $badgeName);
                $groups['event'][$parts[1]][] = $parts[2]??'XX';
            }

            $io->writeln($item->getBasename());
        }

        $io->writeln('$groups =');
        $io->writeln('[');

        foreach ($groups as $type => $subgroups) {
            $io->writeln("'$type' =>");
            $io->writeln('[');
            foreach ($subgroups as $groupName => $items) {
                $io->writeln("'$groupName' =>");
                $io->writeln('[');
                $io->writeln("'".implode("', '", $items)."'");
                $io->writeln('],');
            }
            $io->writeln('],');
        }

        $io->writeln('];');

        $io->success('Finished!');

        return 0;
    }
}
