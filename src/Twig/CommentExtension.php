<?php
/*
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Twig;

use App\Entity\Comment\CommentInterface;
use App\Entity\Comment\ThreadInterface;
use App\Service\Comment\Acl\CommentAclInterface;
use App\Service\Comment\Acl\ThreadAclInterface;


/**
 * Extends Twig to provide some helper functions for the CommentBundle.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class CommentExtension extends \Twig_Extension
{
    protected $commentAcl;
    protected $threadAcl;

    public function __construct(CommentAclInterface $commentAcl = null, ThreadAclInterface $threadAcl = null)
    {
        $this->commentAcl = $commentAcl;
        $this->threadAcl = $threadAcl;
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('fos_comment_deleted', array($this, 'isCommentDeleted')),
            new \Twig_SimpleTest('fos_comment_in_state', array($this, 'isCommentInState')),
        );
    }

    /**
     * Check if the state of the comment is deleted.
     *
     * @param CommentInterface $comment
     *
     * @return bool
     *
     * @deprecated Use isCommentInState instead
     */
    public function isCommentDeleted(CommentInterface $comment)
    {
        return $this->isCommentInState($comment, $comment::STATE_DELETED);
    }

    /**
     * Checks if comment is in given state.
     *
     * @param CommentInterface $comment
     * @param int              $state CommentInterface::STATE_*
     *
     * @return bool
     */
    public function isCommentInState(CommentInterface $comment, $state)
    {
        return $comment->getState() === $state;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('fos_comment_can_comment', array($this, 'canComment')),
            new \Twig_SimpleFunction('fos_comment_can_delete_comment', array($this, 'canDeleteComment')),
            new \Twig_SimpleFunction('fos_comment_can_edit_comment', array($this, 'canEditComment')),
            new \Twig_SimpleFunction('fos_comment_can_edit_thread', array($this, 'canEditThread')),
            new \Twig_SimpleFunction('fos_comment_can_comment_thread', array($this, 'canCommentThread')),
        );
    }

    /*
     * Checks if the current user is able to comment. Checks if they
     * can create root comments if no $comment is provided, otherwise
     * checks if they can reply to a given comment if supplied.
     *
     * @param  CommentInterface|null $comment
     * @return bool                  If the user is able to comment
     */
    public function canComment(CommentInterface $comment = null)
    {
        if (null !== $comment
            && null !== $comment->getThread()
            && !$comment->getThread()->isCommentable()
        ) {
            return false;
        }
        if (null === $this->commentAcl) {
            return true;
        }
        if (null === $comment) {
            return $this->commentAcl->canCreate();
        }

        return $this->commentAcl->canReply($comment);
    }

    /**
     * Checks if the current user is able to delete a comment.
     *
     * @param CommentInterface $comment
     *
     * @return bool
     */
    public function canDeleteComment(CommentInterface $comment)
    {
        if (null === $this->commentAcl) {
            return false;
        }

        return $this->commentAcl->canDelete($comment);
    }

    /**
     * Checks if the current user is able to edit a comment.
     *
     * @param CommentInterface $comment
     *
     * @return bool If the user is able to comment
     */
    public function canEditComment(CommentInterface $comment)
    {
        if (!$comment->getThread()->isCommentable()) {
            return false;
        }
        if (null === $this->commentAcl) {
            return false;
        }

        return $this->commentAcl->canEdit($comment);
    }

    /**
     * Checks if the thread can be edited.
     *
     * Will use the specified ACL, or return true otherwise.
     *
     * @param ThreadInterface $thread
     *
     * @return bool
     */
    public function canEditThread(ThreadInterface $thread)
    {
        if (null === $this->threadAcl) {
            return false;
        }

        return $this->threadAcl->canEdit($thread);
    }

    /**
     * Checks if the thread can be commented.
     *
     * @param ThreadInterface $thread
     *
     * @return bool
     */
    public function canCommentThread(ThreadInterface $thread)
    {
        return $thread->isCommentable()
            && (null === $this->commentAcl || $this->commentAcl->canCreate());
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'fos_comment';
    }
}
