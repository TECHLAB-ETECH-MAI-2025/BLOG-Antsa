<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Environment;

class ArticleRepository extends ServiceEntityRepository
{
    /** @var Environment */
    private Environment $twig;

    public function __construct(ManagerRegistry $registry, Environment $twig)
    {
        parent::__construct($registry, Article::class);
        $this->twig = $twig;           
    }

    /**
     * Construit les données attendues par DataTables.
     */
    public function buildDatatable(array $p): array
    {
        /* ------------------------------------------------------------------
         * 1. Paramètres DataTables
         * -----------------------------------------------------------------*/
        $draw      = (int)($p['draw']   ?? 1);
        $start     = (int)($p['start']  ?? 0);
        $length    = (int)($p['length'] ?? 10);
        $searchVal = $p['search']['value'] ?? '';

        /* ------------------------------------------------------------------
         * 2. QueryBuilder (+ filtres + tri + pagination)
         * -----------------------------------------------------------------*/
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')->addSelect('c')
            ->leftJoin('a.comments',   'com')
            ->leftJoin('a.likes',      'l')
            ->where('a.deletedAt IS NULL');

        if ($searchVal !== '') {
            $qb->andWhere('LOWER(a.title) LIKE :s')
               ->setParameter('s', '%'.mb_strtolower($searchVal).'%');
        }

        $columns = ['a.id', 'a.title', 'c.title', 'a.createdAt'];
        $ordCol  = $p['order'][0]['column'] ?? 0;
        $ordDir  = strtoupper($p['order'][0]['dir'] ?? 'DESC');
        $qb->orderBy($columns[$ordCol] ?? 'a.id', $ordDir === 'ASC' ? 'ASC' : 'DESC');

        $qb->setFirstResult($start)
           ->setMaxResults($length);

        /* ------------------------------------------------------------------
         * 3. Récupération des données
         * -----------------------------------------------------------------*/
        $articles = $qb->getQuery()->getResult();          // objets Article
        $total    = $this->count(['deletedAt' => null]);
        $filtered = $searchVal ? count($articles) : $total;

        /* ------------------------------------------------------------------
         * 4. Transformation pour DataTables
         * -----------------------------------------------------------------*/
        $data = array_map(function (Article $a) {
            $categoryTitles   = array_map(fn($c) => $c->getTitle(), $a->getCategories()->toArray());
            $categoriesString = implode(', ', $categoryTitles);

            return [
                'id'            => $a->getId(),
                'title'         => $a->getTitle(),
                'categories'    => $categoriesString,
                'commentsCount' => $a->getComments()->count(),
                'likesCount'    => $a->getLikes()->count(),
                'createdAt'     => $a->getCreatedAt()->format('d/m/Y H:i'),
                'actions'       => $this->twig->render('article/_actions.html.twig', [
                    'article' => $a,
                ]),
            ];
        }, $articles);

        /* ------------------------------------------------------------------
         * 5. Réponse finale
         * -----------------------------------------------------------------*/
        return [
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ];
    }

    /**
     * Recherche d’articles par titre (autocomplete).
     */
    public function searchByTitle(string $q, int $limit = 10): array
    {
        if ($q === '') {
            return [];
        }

        return $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')->addSelect('c')
            ->where('LOWER(a.title) LIKE :q')
            ->setParameter('q', '%'.mb_strtolower($q).'%')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}