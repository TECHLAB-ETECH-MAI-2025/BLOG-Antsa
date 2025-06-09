<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Entity\Comment;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/article', name: 'api_articles')]
class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleRepository      $articleRepo,
        private CommentRepository      $commentRepo,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/datatable', name: 'datatable', methods: ['GET', 'POST'])]
    public function datatable(Request $request): JsonResponse
    {
    $params = $request->request->all();
    $resp = $this->articleRepo->buildDatatable($params);

    return $this->json($resp, 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }


    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $q     = trim($request->query->get('q',''));
        $rows  = $this->articleRepo->searchByTitle($q, 10);

        return $this->json([
            'results' => array_map(fn(Article $a)=>[
                'id'         => $a->getId(),
                'title'      => $a->getTitle(),
                'categories' => $a->getCategories(),
            ], $rows)
        ]);
    }

    #[Route('/{id}/like', name: 'like', methods: ['POST'])]
    public function toggleLike(Article $article): JsonResponse
    {
        $count = $this->articleRepo->toggleLike($this->getUser(), $article);

        return $this->json([
            'success'    => true,
            'liked'      => $article->isLikedBy($this->getUser()),
            'likesCount' => $count,
        ]);
    }

    #[Route('/{id}/comment', name: 'comment', methods: ['POST'])]
    public function addComment(Request $req, Article $article): JsonResponse
    {
        $content = trim($req->request->get('content',''));
        if ($content==='') {
            return $this->json(['error'=>'empty'], 400);
        }

        $comment = (new Comment())
            ->setArticle($article)
            ->setAuthor($this->getUser())
            ->setContent($content)
            ->setCreatedAt(new \DateTimeImmutable());

        $this->commentRepo->save($comment, true);

        return $this->json([
            'id'        => $comment->getId(),
            'content'   => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('d/m/Y H:i'),
            'user'      => $comment->getAuthor(),
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $req, Article $article): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        if (!isset($data['title'])) {
            return $this->json(['error'=>'Missing title'],400);
        }
        $article->setTitle($data['title']);
        $this->em->flush();

        return $this->json(['success'=>true]);
    }
}