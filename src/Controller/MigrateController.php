<?php

namespace App\Controller;

use App\Entity\AgentStat;
use App\Repository\AgentStatRepository;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/migrate')]
#[IsGranted('ROLE_AGENT')]
class MigrateController extends BaseController
{
    #[Route('/', name: 'app_migrate')]
    public function index(): Response
    {
        return $this->render('migrate/index.html.twig');
    }

     #[Route('/upload', name: 'app_migrate_upload')]
    public function upload(Request $request): Response
    {
        $token = $request->get("token");

        if (!$this->isCsrfTokenValid('app_migrate_upload', $token))
        {
            return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
        }

        $file = $request->files->get('csvfile');

        if (empty($file))
        {
            return new Response("No file specified",
                Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }

        $agent = $this->getUser()?->getAgent();

        if (!$agent) {
            throw new \UnexpectedValueException(
                'No agent found for current user'
            );
        }

        $csv = Reader::createFromPath($file->getRealPath());
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader();

        /*
         * @TODO VALIDATE THIS %&$·&%/&%& CSV FILE.....
         */

        foreach ($csv as $item) {
            $stat = new AgentStat();
            $stat->setAgent($agent);

            foreach ($header as $index) {
                $method = match ($index) {
                    'epoch' => 'setEpochHackstreaks',
                    default => 'set'.ucfirst($index),
                };

                if (false === method_exists($stat, $method)) {
                    throw new \UnexpectedValueException(
                        'Unknown property: '.$index
                    );
                }

                switch ($index) {
                    case 'datetime':
                        $stat->setDatetime(new \DateTime($item[$index]));
                        break;
                    default:
                        $stat->$method($item[$index]);
                }
            }
        }

        return new Response("File uploaded",  Response::HTTP_OK,
            ['content-type' => 'text/plain']);


        return $this->render('migrate/index.html.twig');
    }

    #[Route('/csv', name: 'app_migrate_get_csv')]
    public function getCsv(AgentStatRepository $agentStatRepository): Response
    {
        $agent = $this->getUser()?->getAgent();

        if (!$agent) {
            throw new \UnexpectedValueException(
                'No agent found for current user'
            );
        }

        $stats = $agentStatRepository->getAgentStatsForCsv($agent);

        $data = [];

        foreach ($stats as $stat) {
            $s = [];
            foreach ($stat as $k => $v) {
                if ($k === 'id') {
                } elseif ($k === 'datetime') {
                    $s[$k] = $v->format('Y-m-d H:i:s');
                } else {
                    if (null === $v) {
                        $v = 0;
                    }
                    $s[$k] = $v;
                }
            }
            $data[] = $s;
        }

        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // We insert the CSV header
        $csv->insertOne(array_keys($data[0]));

        $csv->insertAll($data);

        // Because you are providing the filename you don't have to
        // set the HTTP headers Writer::output can
        // directly set them for you
        // The file is downloadable
        $csv->output('users.csv');
        die;
    }
}
