<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Repository\AgentStatRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Writer;
use SplTempFileObject;

/**
 * An agent's stats as CSV, oldest first: one column per agent_stat column (the table's column
 * names, e.g. mind_controller), without the id and the agent. Other apps (AgentTools) import it.
 */
class AgentStatCsvExporter
{
    public const string DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private readonly AgentStatRepository $statRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Only the header without stats.
     */
    public function export(Agent $agent): string
    {
        $columns = $this->columns();

        $csv = Writer::from(new SplTempFileObject());
        $csv->insertOne(array_values($columns));

        foreach ($this->statRepository->getAgentStatsForCsv($agent, 'ASC') as $stat) {
            $row = [];
            foreach (array_keys($columns) as $field) {
                $value = $stat[$field] ?? null;
                $row[] = $value instanceof DateTimeInterface ? $value->format(self::DATETIME_FORMAT) : $value;
            }

            $csv->insertOne($row);
        }

        return $csv->toString();
    }

    /**
     * The exported fields in their mapping's order, with their column names.
     *
     * @return array<string, string> field name => column name
     */
    private function columns(): array
    {
        $metadata = $this->entityManager->getClassMetadata(AgentStat::class);

        $columns = [];
        foreach ($metadata->getFieldNames() as $field) {
            if ('id' !== $field) {
                $columns[$field] = $metadata->getColumnName($field);
            }
        }

        return $columns;
    }
}
