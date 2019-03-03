<?php

/*
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\Comment;

use App\Entity\Comment\CommentInterface;
use App\Entity\Comment\Thread;
use App\Entity\Comment\ThreadInterface;
use App\Form\Comment\CommentableThreadType;
use App\Form\Comment\CommentType;
use App\Form\Comment\DeleteCommentType;
use App\Form\Comment\ThreadType;
use App\Service\Comment\CommentManagerInterface;
use App\Service\Comment\ThreadManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Restful controller for the Threads.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class ThreadController extends AbstractController
{
    const VIEW_FLAT = 'flat';

    const VIEW_TREE = 'tree';

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="fos_comment_get_thread", path="/api/thread/{id}", methods={"GET"})
     *
     * Gets the thread for a given id.
     *
     * @param Thread              $thread
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    public function getThreadAction(Thread $thread, SerializerInterface $serializer)
    {
        $thread = $serializer->serialize($thread, 'json');

        return new JsonResponse($thread);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="fos_comment_get_threads", path="/api/threads", methods={"GET"})
     *
     * Gets the threads for the specified ids.
     *
     * @param Request                $request
     * @param ThreadManagerInterface $threadManager
     * @param SerializerInterface    $serializer
     *
     * @return Response
     */
    public function getThreadsActions(Request $request, ThreadManagerInterface $threadManager,
                                      SerializerInterface $serializer)
    {
        $ids = $request->query->get('ids');

        if (null === $ids) {
            throw new NotFoundHttpException('Cannot query threads without id\'s.');
        }

        $threads = $threadManager->findThreadsBy(['id' => $ids]);

        return new JsonResponse($serializer->serialize($threads, 'json'));
    }

    /**
     * Creates a new Thread from the submitted data.
     *
     * @param Request                $request       The current request
     * @param ThreadManagerInterface $threadManager
     *
     * @return Response
     */
    public function postThreadsAction(Request $request, ThreadManagerInterface $threadManager)
    {
        $thread = $threadManager->createThread();
        $form = $this->createForm(ThreadType::class, $thread);
        $form->setData($thread);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (null !== $threadManager->findThreadById($thread->getId())) {
                $this->onCreateThreadErrorDuplicate($form);
            }

            // Add the thread
            $threadManager->saveThread($thread);

            return $this->onCreateThreadSuccess($form);
        }

        return $this->onCreateThreadError($form);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="fos_comment_edit_thread_commentable", path="/api/threads/{id}/commentable/edit", methods={"GET"})
     *
     * Get the edit form the open/close a thread.
     *
     * @param Request                $request       Current request
     * @param mixed                  $id            Thread id
     * @param ThreadManagerInterface $threadManager
     *
     * @return Response
     */
    public function editThreadCommentableAction(Request $request, $id, ThreadManagerInterface $threadManager)
    {
        $thread = $threadManager->findThreadById($id);

        if (null === $thread) {
            throw new NotFoundHttpException(sprintf("Thread with id '%s' could not be found.", $id));
        }

        $thread->setCommentable($request->query->get('value', 1));

        $form = $this->createForm(CommentableThreadType::class, $thread, ['method' => 'PATCH']);
        $form->setData($thread);

        return $this->render('Comment/Thread/commentable.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'isCommentable' => $thread->isCommentable(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="fos_comment_patch_thread_commentable", path="/api/threads/{id}/comments/{commentId}/state", methods={"PATCH"})
     *
     * Edits the thread.
     *
     * @param Request                $request       Currenty request
     * @param mixed                  $id            Thread id
     * @param ThreadManagerInterface $threadManager
     *
     * @return Response
     */
    public function patchThreadCommentableAction(Request $request, $id, ThreadManagerInterface $threadManager)
    {
        $thread = $threadManager->findThreadById($id);

        if (null === $thread) {
            throw new NotFoundHttpException(sprintf("Thread with id '%s' could not be found.", $id));
        }

        $form = $this->createForm(ThreadType::class, $thread);
        $form->setData($thread);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $threadManager->saveThread($thread);

            return $this->onOpenThreadSuccess($form);
        }

        return $this->onOpenThreadError($form);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="fos_comment_new_thread_comments", path="/api/threads/{id}/comments/new", methods={"GET"})
     *
     * Presents the form to use to create a new Comment for a Thread.
     *
     * @param Request                 $request
     * @param string                  $id
     * @param ThreadManagerInterface  $threadManager
     * @param CommentManagerInterface $commentManager
     *
     * @return Response
     */
    public function newThreadCommentsAction(Request $request, $id, ThreadManagerInterface $threadManager,
                                            CommentManagerInterface $commentManager)
    {
        $thread = $threadManager->findThreadById($id);
        if (!$thread) {
            throw new NotFoundHttpException(sprintf('Thread with identifier of "%s" does not exist', $id));
        }

        $comment = $commentManager->createComment($thread);

        $parent = $this->getValidCommentParent($thread, $request->query->get('parentId'), $commentManager);

        $form = $this->createForm(CommentType::class, $comment);
        $form->setData($comment);

        return $this->render('Comment/Thread/comment_new.html.twig', [
            'form' => $form->createView(),
            'first' => 0 === $thread->getNumComments(),
            'thread' => $thread,
            'parent' => $parent,
            'id' => $id,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="fos_comment_get_thread_comment", path="/api/threads/{id}/comments/{commentId}", methods={"GET"})
     *
     * Get a comment of a thread.
     *
     * @param string                  $id             Id of the thread
     * @param mixed                   $commentId      Id of the comment
     * @param ThreadManagerInterface  $threadManager
     * @param CommentManagerInterface $commentManager
     *
     * @return Response
     */
    public function getThreadCommentAction($id, $commentId, ThreadManagerInterface $threadManager,
                                           CommentManagerInterface $commentManager)
    {
        $thread = $threadManager->findThreadById($id);
        $comment = $commentManager->findCommentById($commentId);
        $parent = null;

        if (null === $thread || null === $comment || $comment->getThread() !== $thread) {
            throw new NotFoundHttpException(sprintf("No comment with id '%s' found for thread with id '%s'", $commentId,
                $id));
        }

        $ancestors = $comment->getAncestors();
        if (count($ancestors) > 0) {
            $parent = $this->getValidCommentParent($thread, $ancestors[count($ancestors) - 1], $commentManager);
        }

        return $this->render('Comment/Thread/comment.html.twig',
            ['comment' => $comment, 'thread' => $thread, 'parent' => $parent,
             'depth' => $comment->getDepth(),
            ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="fos_comment_remove_thread_comment", path="/api/threads/{id}/comments/{commentId}/remove", methods={"GET"})
     *
     * Get the delete form for a comment.
     *
     * @param Request                 $request        Current request
     * @param string                  $id             Id of the thread
     * @param mixed                   $commentId      Id of the comment
     * @param ThreadManagerInterface  $threadManager
     * @param CommentManagerInterface $commentManager
     *
     * @return Response
     */
    public function removeThreadCommentAction(Request $request, $id, $commentId, ThreadManagerInterface $threadManager,
                                              CommentManagerInterface $commentManager)
    {
        $thread = $threadManager->findThreadById($id);
        $comment = $commentManager->findCommentById($commentId);

        if (null === $thread || null === $comment || $comment->getThread() !== $thread) {
            throw new NotFoundHttpException(sprintf("No comment with id '%s' found for thread with id '%s'", $commentId,
                $id));
        }

        $form = $this->createForm(DeleteCommentType::class, $comment, ['method' => 'DELETE']);
        $comment->setState($request->query->get('value', $comment::STATE_DELETED));
        $form->setData($comment);

        return $this->render('Comment/Thread/comment_remove.html.twig',
            ['form' => $form->createView(), 'id' => $id, 'commentId' => $commentId]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="fos_comment_patch_thread_comment_state", path="/api/threads/{id}/comments/{commentId}/state", methods={"PATCH"})
     *
     * Edits the comment state.
     *
     * @param Request                 $request        Current request
     * @param mixed                   $id             Thread id
     * @param mixed                   $commentId      Id of the comment
     * @param ThreadManagerInterface  $threadManager
     * @param CommentManagerInterface $commentManager
     *
     * @return Response
     */
    public function patchThreadCommentStateAction(Request $request, $id, $commentId,
                                                  ThreadManagerInterface $threadManager,
                                                  CommentManagerInterface $commentManager)
    {
        $thread = $threadManager->findThreadById($id);
        $comment = $commentManager->findCommentById($commentId);

        if (null === $thread || null === $comment || $comment->getThread() !== $thread) {
            throw new NotFoundHttpException(sprintf("No comment with id '%s' found for thread with id '%s'", $commentId,
                $id));
        }

        $form = $this->createForm(DeleteCommentType::class, $comment, ['method' => 'DELETE']);
        $form->setData($comment);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (false !== $commentManager->saveComment($comment)) {
                return $this->onRemoveThreadCommentSuccess($form, $id);
            }
        }

        return $this->onRemoveThreadCommentError($form, $id);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="fos_comment_edit_thread_comment", path="/api/threads/{id}/comments/{commentId}/edit", methods={"GET"})
     *
     * Presents the form to use to edit a Comment for a Thread.
     *
     * @param string                  $id             Id of the thread
     * @param mixed                   $commentId      Id of the comment
     * @param ThreadManagerInterface  $threadManager
     * @param CommentManagerInterface $commentManager
     *
     * @return Response
     */
    public function editThreadCommentAction($id, $commentId, ThreadManagerInterface $threadManager,
                                            CommentManagerInterface $commentManager)
    {
        $thread = $threadManager->findThreadById($id);
        $comment = $commentManager->findCommentById($commentId);

        if (null === $thread || null === $comment || $comment->getThread() !== $thread) {
            throw new NotFoundHttpException(sprintf("No comment with id '%s' found for thread with id '%s'", $commentId,
                $id));
        }

        $form = $this->createForm(CommentType::class, $comment, ['method' => 'PUT']);
        $form->setData($comment);

        return $this->render('Comment/Thread/comment_edit.html.twig',
            ['form' => $form->createView(), 'comment' => $comment]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="fos_comment_put_thread_comments", path="/api/threads/{id}/comments/{commentId}", methods={"PUT"})
     *
     * Edits a given comment.
     *
     * @param Request                 $request        Current request
     * @param string                  $id             Id of the thread
     * @param mixed                   $commentId      Id of the comment
     * @param ThreadManagerInterface  $threadManager
     * @param CommentManagerInterface $commentManager
     *
     * @return Response
     */
    public function putThreadCommentsAction(Request $request, $id, $commentId, ThreadManagerInterface $threadManager,
                                            CommentManagerInterface $commentManager)
    {
        $thread = $threadManager->findThreadById($id);
        $comment = $commentManager->findCommentById($commentId);

        if (null === $thread || null === $comment || $comment->getThread() !== $thread) {
            throw new NotFoundHttpException(sprintf("No comment with id '%s' found for thread with id '%s'", $commentId,
                $id));
        }

        $form = $this->createForm(CommentType::class, $comment, ['method' => 'PUT']);
        $form->setData($comment);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (false !== $commentManager->saveComment($comment)) {
                return $this->onEditCommentSuccess($form, $id);
            }
        }

        return $this->onEditCommentError($form, $id);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="fos_comment_get_thread_comments", path="/api/threads/{id}/comments", methods={"GET"})
     *
     * Get the comments of a thread. Creates a new thread if none exists.
     *
     * @param Request                 $request        Current request
     * @param string                  $id             Id of the thread
     * @param ThreadManagerInterface  $threadManager
     * @param ValidatorInterface      $validator
     * @param CommentManagerInterface $commentManager
     *
     * @return Response
     */
    public function getThreadCommentsAction(Request $request, $id, ThreadManagerInterface $threadManager,
                                            ValidatorInterface $validator,
                                            CommentManagerInterface $commentManager)
    {
        $displayDepth = $request->query->get('displayDepth');
        $sorter = $request->query->get('sorter');
        $thread = $threadManager->findThreadById($id);

        // We're now sure it is no duplicate id, so create the thread
        if (null === $thread) {
            $permalink = $request->query->get('permalink');

            $thread = $threadManager->createThread();
            $thread->setId($id);
            $thread->setPermalink($permalink);

            // Validate the entity
            $errors = $validator->validate($thread, null, ['NewThread']);
            if (count($errors) > 0) {
                $response = new Response('', Response::HTTP_BAD_REQUEST);

                return $this->render('Comment/Thread/errors.html.twig', ['errors' => $errors], $response);
            }

            // Decode the permalink for cleaner storage (it is encoded on the client side)
            $thread->setPermalink(urldecode($permalink));

            // Add the thread
            $threadManager->saveThread($thread);
        }

        $viewMode = $request->query->get('view', 'tree');
        switch ($viewMode) {
            case self::VIEW_FLAT:
                $comments = $commentManager->findCommentsByThread($thread, $displayDepth, $sorter);

                // We need nodes for the api to return a consistent response, not an array of comments
                $comments = array_map(function ($comment) {
                    return ['comment' => $comment, 'children' => []];
                },
                    $comments
                );
                break;
            case self::VIEW_TREE:
            default:
                $comments = $commentManager->findCommentTreeByThread($thread, $sorter, $displayDepth);
                break;
        }

        return $this->render('Comment/Thread/comments.html.twig', [
            'comments' => $comments,
            'displayDepth' => $displayDepth,
            'sorter' => 'date',
            'thread' => $thread,
            'view' => $viewMode,
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="fos_comment_post_thread_comments", path="/api/threads/{id}/comments", methods={"POST"})
     *
     * Creates a new Comment for the Thread from the submitted data.
     *
     * @param Request                 $request        The current request
     * @param string                  $id             The id of the thread
     * @param ThreadManagerInterface  $threadManager
     * @param CommentManagerInterface $commentManager
     *
     * @return Response
     */
    public function postThreadCommentsAction(Request $request, $id, ThreadManagerInterface $threadManager,
                                             CommentManagerInterface $commentManager)
    {
        $thread = $threadManager->findThreadById($id);
        if (!$thread) {
            throw new NotFoundHttpException(sprintf('Thread with identifier of "%s" does not exist', $id));
        }

        if (!$thread->isCommentable()) {
            throw new AccessDeniedException(sprintf('Thread "%s" is not commentable', $id));
        }

        $parent = $this->getValidCommentParent($thread, $request->query->get('parentId'), $commentManager);
        $comment = $commentManager->createComment($thread, $parent);

        $form = $form = $this->createForm(CommentType::class, $comment, ['method' => 'POST']);
        $form->setData($comment);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $comment->setAuthor($this->getUser());
            if (false !== $commentManager->saveComment($comment)) {
                return $this->onCreateCommentSuccess($form, $id);
            }
        }

        return $this->onCreateCommentError($form, $id, $parent);
    }

    /**
     * Forwards the action to the comment view on a successful form submission.
     *
     * @param FormInterface $form Form with the error
     * @param string        $id   Id of the thread
     *
     * @return Response
     */
    protected function onCreateCommentSuccess(FormInterface $form, $id)
    {
        return $this->forward($this->routeToControllerName('fos_comment_get_thread_comment'),
            ['id' => $id, 'commentId' => $form->getData()->getId()])
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Returns a HTTP_BAD_REQUEST response when the form submission fails.
     *
     * @param FormInterface    $form   Form with the error
     * @param string           $id     Id of the thread
     * @param CommentInterface $parent Optional comment parent
     *
     * @return Response
     */
    protected function onCreateCommentError(FormInterface $form, $id, CommentInterface $parent = null)
    {
        return $this->render('Comment/Thread/comment_new.html.twig',
            ['form' => $form->createView(),
             'id' => $id,
             'parent' => $parent,
            ], new Response('', Response::HTTP_BAD_REQUEST));
    }

    /**
     * Forwards the action to the thread view on a successful form submission.
     *
     * @param FormInterface $form
     *
     * @return Response
     */
    protected function onCreateThreadSuccess(FormInterface $form)
    {
        return $this->forward($this->routeToControllerName('fos_comment_get_thread'),
            ['id' => $form->getData()->getId()])
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Returns a HTTP_BAD_REQUEST response when the form submission fails.
     *
     * @param FormInterface $form
     *
     * @return Response
     */
    protected function onCreateThreadError(FormInterface $form)
    {
        return $this->render('Comment/Thread/new.html.twig', ['form' => $form->createView()],
            new Response('', Response::HTTP_BAD_REQUEST));
    }

    /**
     * Returns a HTTP_BAD_REQUEST response when the Thread creation fails due to a duplicate id.
     *
     * @param FormInterface $form
     *
     * @return Response
     */
    protected function onCreateThreadErrorDuplicate(FormInterface $form)
    {
        return new Response(sprintf("Duplicate thread id '%s'.", $form->getData()->getId()),
            Response::HTTP_BAD_REQUEST);
    }

    /**
     * Forwards the action to the comment view on a successful form submission.
     *
     * @param FormInterface $form Form with the error
     * @param string        $id   Id of the thread
     *
     * @return Response
     */
    protected function onEditCommentSuccess(FormInterface $form, $id)
    {
        return $this->forward($this->routeToControllerName('fos_comment_get_thread_comment'),
            ['id' => $id, 'commentId' => $form->getData()->getId()])->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Returns a HTTP_BAD_REQUEST response when the form submission fails.
     *
     * @param FormInterface $form Form with the error
     * @param string        $id   Id of the thread
     *
     * @return Response
     */
    protected function onEditCommentError(FormInterface $form, $id)
    {
        return $this->render('Comment/Thread/comment_edit.html.twig',
            ['form' => $form->createView(),
             'comment' => $form->getData(),
             'id' => $id,
            ], new Response('', Response::HTTP_BAD_REQUEST));
    }

    /**
     * Forwards the action to the open thread edit view on a successful form submission.
     *
     * @param FormInterface $form
     *
     * @return Response
     */
    protected function onOpenThreadSuccess(FormInterface $form)
    {
        return $this->forward($this->routeToControllerName('fos_comment_edit_thread_commentable'),
            ['id' => $form->getData()->getId(), 'value' => !$form->getData()->isCommentable()])
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Returns a HTTP_BAD_REQUEST response when the form submission fails.
     *
     * @param FormInterface $form
     *
     * @return Response
     */
    protected function onOpenThreadError(FormInterface $form)
    {
        return $this->render('Comment/Thread/commentable.html.twig',
            ['form' => $form->createView(),
             'id' => $form->getData()->getId(),
             'isCommentable' => $form->getData()->isCommentable(),
            ], new Response('', Response::HTTP_BAD_REQUEST));
    }

    /**
     * Forwards the action to the comment view on a successful form submission.
     *
     * @param FormInterface $form Comment delete form
     * @param int           $id   Thread id
     *
     * @return Response
     */
    protected function onRemoveThreadCommentSuccess(FormInterface $form, $id)
    {
        return $this->forward($this->routeToControllerName('fos_comment_get_thread_comment'),
            ['id' => $id, 'commentId' => $form->getData()->getId()])->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Returns a HTTP_BAD_REQUEST response when the form submission fails.
     *
     * @param FormInterface $form Comment delete form
     * @param int           $id   Thread id
     *
     * @return Response
     */
    protected function onRemoveThreadCommentError(FormInterface $form, $id)
    {
        return $this->render('Comment/Thread/comment_remove.html.twig',
            ['form' => $form->createView(),
             'id' => $id,
             'commentId' => $form->getData()->getId(),
             'value' => $form->getData()->getState(),
            ], new Response(Response::HTTP_BAD_REQUEST));
    }

    /**
     * Checks if a comment belongs to a thread. Returns the comment if it does.
     *
     * @param ThreadInterface         $thread         Thread object
     * @param mixed                   $commentId      Id of the comment
     * @param CommentManagerInterface $commentManager
     *
     * @return CommentInterface|null The comment
     */
    private function getValidCommentParent(ThreadInterface $thread, $commentId, CommentManagerInterface $commentManager)
    {
        if (null !== $commentId) {
            $comment = $commentManager->findCommentById($commentId);
            if (!$comment) {
                throw new NotFoundHttpException(sprintf('Parent comment with identifier "%s" does not exist',
                    $commentId));
            }

            if ($comment->getThread() !== $thread) {
                throw new NotFoundHttpException('Parent comment is not a comment of the given thread.');
            }

            return $comment;
        }

        return null;
    }

    /**
     * Allow to forward to a route name instead of a controller method name.
     *
     * @param $routename
     *
     * @return mixed
     */
    private function routeToControllerName($routename)
    {
        $routes = $this->get('router')->getRouteCollection();

        return $routes->get($routename)->getDefaults()['_controller'];
    }
}
