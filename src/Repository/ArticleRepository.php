<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Récupération des articles pour un tableau dynamique (DataTable)
     */
    public function findForDatatable(
    int $start,
    int $length,
    array $search,
    string $orderColumn,
    string $orderDir
): array {
    $qb = $this->createQueryBuilder('a');

    if (!empty($search['value'])) {
        $qb->andWhere('a.title LIKE :search')
           ->setParameter('search', '%' . $search['value'] . '%');
    }

    $totalCount = $this->count([]);

    $filteredCount = (clone $qb)
        ->select('COUNT(a.id)')
        ->getQuery()
        ->getSingleScalarResult();

    // Sécurité colonne triée
    $allowedColumns = ['id', 'title', 'createdAt'];
    if (!in_array($orderColumn, $allowedColumns)) {
        $orderColumn = 'id';
    }

    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

    $qb->orderBy('a.' . $orderColumn, $orderDir)
       ->setFirstResult($start)
       ->setMaxResults($length);

    $data = $qb->getQuery()->getResult();

    return [
        'data' => $data,
        'totalCount' => (int) $totalCount,
        'filteredCount' => (int) $filteredCount,
    ];
}



    /**
     * Recherche des articles par titre
     */
    public function searchByTitle(string $query, int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')
            ->where('a.title LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}