<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserAdminCommand extends Command
{
    protected static $defaultName = 'user-admin';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Administer Users');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $io->title('KuKu\'s User Admin');

        $this->showMenu($input, $output);

        return 0;
    }

    private function showMenu(
        InputInterface $input,
        OutputInterface $output
    ): void {
        $io = new SymfonyStyle($input, $output);

        $users = $this->entityManager->getRepository(User::class)->findAll();

        $io->text(
            sprintf('There are %d users in the database.', count($users))
        );

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select an option (defaults to exit)',
            [
                'List Users',
                'Create User',
                'Create Admin User',
                'Edit User',
                'Delete User',
                'Exit',
            ],
            5
        );
        $question->setErrorMessage('Choice %s is invalid.');

        $answer = $helper->ask($input, $output, $question);
        $output->writeln($answer);

        try {
            switch ($answer) {
                case 'List Users':
                    $this->renderUsersTable($output, $users);
                    $this->showMenu($input, $output);
                    break;
                case 'Create User':
                    $username = $helper->ask(
                        $input,
                        $output,
                        new Question('Username: ')
                    );
                    $email = $helper->ask(
                        $input,
                        $output,
                        new Question('Email: ')
                    );
                    $this->createUser($username, $email, []);
                    $io->success('User created');
                    $this->showMenu($input, $output);
                    break;
                case 'Create Admin User':
                    $email = $helper->ask(
                        $input,
                        $output,
                        new Question('Email: ')
                    );
                    $this->createUser('admin', $email, ['ROLE_ADMIN']);
                    $io->success('Admin User created');
                    $this->showMenu($input, $output);
                    break;
                case 'Edit User':
                    $io->text('Edit not implemented yet :(');
                    $this->showMenu($input, $output);
                    break;
                case 'Delete User':
                    $io->text('Delete not implemented yet :(');
                    $this->showMenu($input, $output);
                    break;
                case 'Exit':
                    $io->text('have Fun =;)');
                    break;
                default:
                    throw new \UnexpectedValueException(
                        'Unknown answer: '.$answer
                    );
            }
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            $this->showMenu($input, $output);
        }
    }

    private function renderUsersTable(
        OutputInterface $output,
        array $users
    ): void {
        $table = new Table($output);
        $table->setHeaders(['ID', 'Username', 'email', 'Roles']);

        /* @type User $user */
        foreach ($users as $user) {
            $table->addRow(
                [
                    $user->getId(),
                    $user->getUsername(),
                    $user->getEmail(),
                    implode(", ", $user->getRoles()),
                ]
            );
        }
        $table->render();
    }

    private function createUser(
        string $username,
        string $email,
        array $roles
    ): void {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($email)
            ->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
