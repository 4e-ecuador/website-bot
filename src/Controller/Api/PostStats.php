<?php

namespace App\Controller\Api;

use App\Entity\AgentStat;
use App\Entity\User;
use App\Exception\StatsAlreadyAddedException;
use App\Exception\StatsNotAllException;
use App\Exception\TelegramBotMissingChatIdException;
use App\Service\StatsImporter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use UnexpectedValueException;

class PostStats extends AbstractController
{
    private StatsImporter $statsImporter;
    private EntityManagerInterface $entityManager;
    private string $appEnv;

    public function __construct(
        StatsImporter $statsImporter,
        EntityManagerInterface $entityManager, string $appEnv
    ) {
        $this->statsImporter = $statsImporter;
        $this->entityManager = $entityManager;
        $this->appEnv = $appEnv;
    }

    public function __invoke(AgentStat $data): JsonResponse
    {
        /* @type User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new UnexpectedValueException('User not found!');
        }

        $agent = $user->getAgent();

        if (!$agent) {
            throw new UnexpectedValueException('Agent not found!');
        }

        try {
            $data = $this->statsImporter
                ->updateEntityFromCsv($data, $agent, $data->csv);

            $data->csv = null;

            $this->entityManager->persist($data);
            $this->entityManager->flush();

            $result = $this->statsImporter->getImportResult($data);

            if ('test' !== $this->appEnv) {
                $this->statsImporter->sendResultMessages($result, $data, $user);
            }

            return $this->json(['result' => $result]);
        } catch (StatsAlreadyAddedException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (StatsNotAllException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
