<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function buildDatatable(array $p): array
{
    $draw      = (int)($p['draw']   ?? 1);
    $start     = (int)($p['start']  ?? 0);
    $length    = (int)($p['length'] ?? 10);
    $searchVal = $p['search']['value'] ?? '';

    $qb = $this->createQueryBuilder('a')
        ->leftJoin('a.categories','c')->addSelect('c')
        ->leftJoin('a.comments','com')
        ->leftJoin('a.likes','l')
        ->where('a.deletedAt IS NULL');

    if ($searchVal) {
        $qb->andWhere('LOWER(a.title) LIKE :s')
           ->setParameter('s','%'.mb_strtolower($searchVal).'%');
    }

    $columns = ['a.id','a.title','c.title','a.createdAt'];
    $ordCol  = $p['order'][0]['column'] ?? 0;
    $ordDir  = strtoupper($p['order'][0]['dir'] ?? 'DESC');
    $qb->orderBy($columns[$ordCol] ?? 'a.id', $ordDir === 'ASC' ? 'ASC' : 'DESC');

    $qb->setFirstResult($start)->setMaxResults($length);

    $rows = $qb->getQuery()->getResult();
    $total= $this->count(['deletedAt'=>null]);
    $fil  = $searchVal ? count($rows) : $total;

    $data = array_map(function(Article $a) {
        $categoryTitles = array_map(fn($c) => $c->getTitle(), $a->getCategories()->toArray());
        $categoriesString = implode(', ', $categoryTitles);

        return [
            'id'           => $a->getId(),
            'title'        => $a->getTitle(),
            'categories'   => $categoriesString,
            'commentsCount'=> $a->getComments()->count(),
            'likesCount'   => $a->getLikes()->count(),
            'createdAt'    => $a->getCreatedAt()->format('d/m/Y H:i'),
            'actions'      => sprintf(
                '<a href="/article/%d" class="btn btn-sm btn-info">Voir</a> '.
                '<a href="/article/%d/edit" class="btn btn-sm btn-primary">Modifier</a> '.
                '<a href="/article/%d/delete" class="btn btn-sm btn-danger">Supprimer</a>',
                $a->getId(), $a->getId(), $a->getId()
            ),
        ];
    }, $rows);

    return [
        'draw'            => $draw,
        'recordsTotal'    => $total,
        'recordsFiltered' => $fil,
        'data'            => $data,
    ];
    }


    public function searchByTitle(string $q,int $limit=10): array
    {
        if ($q==='') return [];

        return $this->createQueryBuilder('a')
            ->leftJoin('a.categories','c')->addSelect('c')
            ->where('LOWER(a.title) LIKE :q')
            ->setParameter('q','%'.mb_strtolower($q).'%')
            ->orderBy('a.createdAt','DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
