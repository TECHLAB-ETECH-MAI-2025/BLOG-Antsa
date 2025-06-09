<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleForm;
use App\Repository\ArticleRepository;
use App\Repository\ArticleLikeRepository as RepositoryArticleLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository, PaginatorInterface $paginator): Response
{
    $page = $request->query->getInt('page', 1);

    $queryBuilder = $articleRepository->createQueryBuilder('a')
        ->where('a.deletedAt IS NULL') 
        ->orderBy('a.createdAt', 'DESC'); 

    $articles = $paginator->paginate(
        $queryBuilder,
        $page,
        9
    );

    return $this->render('article/index.html.twig', [
        'articles' => $articles,
    ]);
    }


		#[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
		public function new(Request $request, EntityManagerInterface $entityManager): Response
		{
			$article = new Article();
			$form = $this->createForm(ArticleForm::class, $article);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$article->setCreatedAt(new \DateTime());
				$entityManager->persist($article);
				$entityManager->flush();

				$this->addFlash('success', 'L\'article a été créé avec succès.');
				return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
			}

			return $this->render('article/new.html.twig', [
				'article' => $article,
				'form' => $form,
			]);
		}

		#[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
		public function show(Article $article, Request $request, RepositoryArticleLikeRepository $likeRepository): Response
		{
			// Vérifier si l'utilisateur a déjà aimé cet article
			$ipAddress = $request->getClientIp();
			$isLiked = $likeRepository->findOneBy([
				'article' => $article,
				'ipAddress' => $ipAddress
			]) !== null;

			return $this->render('article/show.html.twig', [
				'article' => $article,
				'is_liked' => $isLiked
			]);
		}

		#[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET','POST'])]
		public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
		{
			$form = $this->createForm(ArticleForm::class, $article);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$entityManager->flush();

				$this->addFlash('success', 'L\'article a été modifié avec succès.');
				return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
			}

			return $this->render('article/edit.html.twig', [
				'article' => $article,
				'form' => $form,
			]);
		}

		#[Route('/{id}/delete', name: 'app_article_delete', methods: ['POST'])]
		public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
		{
    	if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
        $article->setDeletedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $this->addFlash('success', 'L\'article a été supprimé avec succès.');
    	}

    return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
	}

	
    }