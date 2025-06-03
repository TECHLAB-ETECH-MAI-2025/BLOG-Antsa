<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ArticleController extends AbstractController
{
    #[Route('/article/datatable', name: 'api_article_datatable', methods: ['POST'])]
    public function index(Request $request, ArticleRepository $articleRepository): JsonResponse
    {
        $draw = $request->query->getInt('draw', 1);
        $start = $request->query->getInt('start', 0);
        $length = $request->query->getInt('length', 10);
        $searchValue = $request->query->get('search')['value'] ?? '';

        $queryBuilder = $articleRepository->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')
            ->leftJoin('a.comments', 'com')
            ->leftJoin('a.likes', 'l')
            ->where('a.deletedAt IS NULL');

        if ($searchValue) {
            $queryBuilder->andWhere('a.title LIKE :search')
                ->setParameter('search', '%' . $searchValue . '%');
        }

        $totalRecords = $articleRepository->count(['deletedAt' => null]);
        $filteredRecords = clone $queryBuilder;

        $articles = $queryBuilder
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $filteredCount = count($filteredRecords->getQuery()->getResult());

        $data = [];

        foreach ($articles as $article) {
            $urlShow = $this->generateUrl('app_article_show', ['id' => $article->getId()]);
            $urlEdit = $this->generateUrl('app_article_edit', ['id' => $article->getId()]);

            $data[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'categories' => implode(', ', array_map(fn($cat) => $cat->getTitle(), $article->getCategories()->toArray())),
                'commentsCount' => count($article->getComments()),
                'likesCount' => count($article->getLikes()),
                'createdAt' => $article->getCreatedAt()?->format('Y-m-d H:i:s'),
                'actions' =>
                    '<a href="' . $urlShow . '" class="btn btn-sm btn-info">Voir</a> ' .
                    '<a href="' . $urlEdit . '" class="btn btn-sm btn-primary">Modifier</a> ' .
                    '<button class="btn btn-sm btn-danger" data-id="' . $article->getId() . '">Supprimer</button>'
            ];
        }

        return new JsonResponse([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredCount,
            'data' => $data
        ]);
    }

    #[Route('/{id}', name: 'api_article_update', methods: ['PUT'])]
    public function update(Request $request, Article $article, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'])) {
            return new JsonResponse(['error' => 'Missing title'], 400);
        }

        $article->setTitle($data['title']);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}