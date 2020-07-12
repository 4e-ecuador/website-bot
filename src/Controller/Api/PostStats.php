<?php

namespace App\Controller\Api;

use App\Entity\AgentStat;
use App\Entity\User;
use App\Exception\InvalidCsvException;
use App\Exception\StatsAlreadyAddedException;
use App\Exception\StatsNotAllException;
use App\Service\StatsImporter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use TelegramBot\Api\HttpException;
use UnexpectedValueException;

class PostStats extends AbstractController
{
    private StatsImporter $statsImporter;
    private EntityManagerInterface $entityManager;

    public function __construct(
        StatsImporter $statsImporter,
        EntityManagerInterface $entityManager
    ) {
        $this->statsImporter = $statsImporter;
        $this->entityManager = $entityManager;
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
            $statEntry = $this->statsImporter
                ->updateEntryFromCsv($data, $agent, $data->csv);

            $data->csv = null;

            $this->entityManager->persist($statEntry);
            $this->entityManager->flush();

            $result = $this->statsImporter->getImportResult($statEntry);

            $this->statsImporter
                ->sendResultMessages($result, $statEntry, $user);

            return $this->json(['result' => $result], Response::HTTP_CREATED);
        } catch (StatsAlreadyAddedException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (StatsNotAllException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_PRECONDITION_REQUIRED
            );
        } catch (InvalidCsvException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        } catch (HttpException $e) {
            // Telegram bot failed :(
            if (isset($result)) {
                // But we have a result so.. 201
                return $this->json(
                    ['result' => $result, 'error' => $e->getMessage()],
                    Response::HTTP_CREATED
                );
            }

            // No result :(
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        } catch (Exception $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
