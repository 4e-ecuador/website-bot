<?php

namespace App\Controller;

use App\Entity\AgentStat;
use App\Service\AgentStatCsvExporter;
use App\Service\CustomMedalsExporter;
use DateTime;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UnexpectedValueException;

#[IsGranted('ROLE_AGENT')]
class MigrateController extends BaseController
{
    #[Route('/migrate/', name: 'app_migrate')]
    public function index(): Response
    {
        return $this->render('migrate/index.html.twig');
    }

    #[Route('/migrate/upload', name: 'app_migrate_upload')]
    public function upload(Request $request): Response
    {
        $token = $request->request->getString('token') ?: null;

        if (!$this->isCsrfTokenValid('app_migrate_upload', $token)) {
            return new Response(
                'Operation not allowed',
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']
            );
        }

        $file = $request->files->get('csvfile');

        if (empty($file)) {
            return new Response(
                'No file specified',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['content-type' => 'text/plain']
            );
        }

        $agent = $this->getUser()?->getAgent();

        if (!$agent) {
            throw new UnexpectedValueException('No agent found for current user');
        }

        $csv = Reader::from($file->getRealPath());
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader();

        /*
         * @TODO VALIDATE THIS %&$·&%/&%& CSV FILE.....
         */

        foreach ($csv as $item) {
            $stat = new AgentStat();
            $stat->setAgent($agent);
            $this->applyStatRow($stat, $header, $item);
        }

        return new Response(
            'File uploaded',
            Response::HTTP_OK,
            ['content-type' => 'text/plain']
        );
    }

    /**
     * @param array<int, string>    $header
     * @param array<string, string> $item
     */
    private function applyStatRow(AgentStat $stat, array $header, array $item): void
    {
        foreach ($header as $index) {
            $method = match ($index) {
                'epoch' => 'setEpochHackstreaks',
                default => 'set'.ucfirst($index),
            };

            if (false === method_exists($stat, $method)) {
                throw new UnexpectedValueException('Unknown property: '.$index);
            }

            match ($index) {
                'datetime' => $stat->setDatetime(
                    new DateTime($item[$index])
                ),
                default => $stat->$method($item[$index]),
            };
        }
    }

    #[Route('/migrate/csv', name: 'app_migrate_get_csv')]
    public function getCsv(AgentStatCsvExporter $exporter): Response
    {
        $agent = $this->getUser()?->getAgent();
        if (!$agent) {
            throw new UnexpectedValueException('No agent found for current user');
        }

        $response = new Response($exporter->export($agent), Response::HTTP_OK, ['content-type' => 'text/csv; charset=utf-8']);
        // Agent names are ASCII: no UTF-8 file name needed
        $response->headers->set('content-disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            sprintf('agent-stats-%s.csv', $agent->getNickname())
        ));

        return $response;
    }

    #[Route('/migrate/custom-medals', name: 'app_migrate_get_custom_medals')]
    public function getCustomMedals(CustomMedalsExporter $exporter): Response
    {
        $agent = $this->getUser()?->getAgent();
        if (!$agent) {
            throw new UnexpectedValueException('No agent found for current user');
        }

        // Objects for empty groups too, as they are stored
        $response = new JsonResponse($exporter->export($agent), Response::HTTP_OK, [], false);
        $response->setEncodingOptions(JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
        $response->headers->set('content-disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            sprintf('agent-custom-medals-%s.json', $agent->getNickname())
        ));

        return $response;
    }
}
