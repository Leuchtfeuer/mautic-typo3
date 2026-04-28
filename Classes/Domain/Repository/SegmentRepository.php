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

use Doctrine\DBAL\Exception;
use Leuchtfeuer\Mautic\Mautic\AuthorizationFactory;
use Mautic\Api\Segments;
use Mautic\Exception\ContextNotFoundException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

class SegmentRepository extends AbstractRepository
{
    /**
     * @var Segments
     */
    protected $segmentsApi;
    public function __construct(AuthorizationFactory $authorizationFactory, private readonly ConnectionPool $connectionPool, private readonly Context $context)
    {
        parent::__construct($authorizationFactory);
    }

    /**
     * @throws ContextNotFoundException
     */
    #[\Override]
    protected function injectApis(): void
    {
        /** @var Segments $segmentsApi */
        $segmentsApi = $this->getApi('segments');
        $this->segmentsApi = $segmentsApi;
    }

    public function findAll(): array
    {
        $segments = $this->segmentsApi->getList();

        return $segments['lists'] ?? [];
    }

    public function synchronizeSegments(): void
    {
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable('tx_marketingautomation_segment');
        $queryBuilder->getRestrictions()->removeAll();

        $result = $queryBuilder->select('*')->from('tx_marketingautomation_segment')->executeQuery();

        $availableSegments = [];
        while ($row = $result->fetchAssociative()) {
            $availableSegments[$row['uid']] = $row;
        }
        $result->free();

        $queryBuilder->update('tx_marketingautomation_segment')->set('deleted', 1)->executeStatement();

        $segments = $this->findAll();
        foreach ($segments as $segment) {
            $dateAdded = empty($segment['dateAdded']) ? new \DateTime()
                : \DateTime::createFromFormat('Y-m-d\TH:i:sP', $segment['dateAdded']);
            if (!empty($segment['dateModified'])) {
                $dateModified = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $segment['dateModified']);
            } else {
                $dateModified = \DateTime::createFromFormat('U', (string)$this->context->getPropertyFromAspect('date', 'timestamp'));
            }

            if (!isset($availableSegments[$segment['id']])) {
                $insertQueryBuilder = $this->connectionPool
                    ->getQueryBuilderForTable('tx_marketingautomation_segment');
                $insertQueryBuilder->insert('tx_marketingautomation_segment')->values([
                    'uid' => (int)$segment['id'],
                    'crdate' => $dateAdded->getTimestamp(),
                    'tstamp' => $dateModified->getTimestamp(),
                    'deleted' => (int)!$segment['isPublished'],
                    'title' => $segment['name'],
                ])->executeStatement();
            } else {
                $updateQueryBuilder = $this->connectionPool
                    ->getQueryBuilderForTable('tx_marketingautomation_segment');
                $updateQueryBuilder->update('tx_marketingautomation_segment')
                    ->where(
                        $updateQueryBuilder->expr()->eq(
                            'uid',
                            $updateQueryBuilder->createNamedParameter($segment['id'], Connection::PARAM_INT)
                        )
                    )
                    ->set('crdate', $dateAdded->getTimestamp())
                    ->set('tstamp', $dateModified->getTimestamp())
                    ->set('deleted', (int)!$segment['isPublished'])->set('title', $segment['name'])->executeStatement();
            }
        }
    }
}
