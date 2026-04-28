<?php

declare(strict_types=1);

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

class PersonaRepository
{
    public function __construct(private readonly ConnectionPool $connectionPool) {}
    public function findBySegments(array $segments): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_marketingautomation_persona');
        $expressionBuilder = $queryBuilder->expr();
        $persona = $queryBuilder->select('*')
            ->from('tx_marketingautomation_persona', 'persona')
            ->leftJoin(
                'persona',
                'tx_marketingautomation_segment_mm',
                'segment',
                $expressionBuilder->eq('persona.uid', $queryBuilder->quoteIdentifier('segment.uid_foreign'))
            )
            ->where(
                $expressionBuilder->in(
                    'segment.uid_local',
                    $queryBuilder->createNamedParameter($segments, ArrayParameterType::INTEGER)
                )
            )
            ->orderBy('persona.sorting')->setMaxResults(1)->executeQuery()->fetchAllAssociative();

        return $persona[0] ?? [];
    }
}
