<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\PostLike;
use App\Form\CommentFormType;
use App\Form\PostFormType;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @return Response
     */
    public function index(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository)
    {
        $form = $this->createForm(PostFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {
            /** @var Post $post */
            $post = $form->getData();
            $post->setUser($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'New post created!');
            return $this->redirectToRoute('post_index');
        }

        $posts = $postRepository->getAllInLastWeek();

        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/view/{id}", name="post_view")
     * @param Post $post
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PostLikeRepository $likeRepository
     * @return Response
     */
    public function show(Post $post, Request $request, EntityManagerInterface $entityManager, PostLikeRepository $likeRepository)
    {
        $form = $this->createForm(CommentFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {
            /** @var Comment $comment */
            $comment = $form->getData();
            $comment->setUser($this->getUser());
            $post->addComment($comment);
            $entityManager->flush();
            return $this->redirectToRoute('post_view', [
                'id' => $post->getId()
            ]);
        }
        $userLikesPost = $likeRepository->findOneBy([
            'user' => $this->getUser(),
            'post' => $post
        ]);
        return $this->render('post/view.html.twig', [
            'post' => $post,
            'commentForm' => $form->createView(),
            'userLikesPost' => $userLikesPost
        ]);
    }

    /**
     * @Security("user == post.getUser()")
     * @Route("/post/{id}/delete", name="post_delete")
     * @param Post $post
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deletePost(Post $post, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($post);
        $entityManager->flush();
        $this->addFlash('success', 'Successfully deleted!');
        return $this->redirectToRoute('post_index');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/post/{id}/like", name="post_like", methods={"POST"})
     * @param Post $post
     * @param EntityManagerInterface $entityManager
     * @param PostLikeRepository $likeRepository
     * @return JsonResponse
     */
    public function likePost(Post $post, EntityManagerInterface $entityManager, PostLikeRepository $likeRepository)
    {
        $like = $likeRepository->findOneBy([
            'user' => $this->getUser(),
            'post' => $post
        ]);

        if (!$like) {
            $like = new PostLike();
            $like->setUser($this->getUser());
            $post->addLike($like);
        } else {
            $post->removeLike($like);
        }

        $entityManager->flush();
        return new JsonResponse([
            'likes' => $post->getLikesCount()
        ]);
    }
}
