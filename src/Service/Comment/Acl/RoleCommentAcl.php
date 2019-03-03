<?php
/*
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Service\Comment\Acl;

use App\Entity\Comment\Comment;
use App\Entity\Comment\CommentInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Implements Role checking using the Symfony Security component.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class RoleCommentAcl implements CommentAclInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * The role that will grant create permission for a comment.
     *
     * @var string
     */
    private $createRole;

    /**
     * The role that will grant view permission for a comment.
     *
     * @var string
     */
    private $viewRole;

    /**
     * The role that will grant edit permission for a comment.
     *
     * @var string
     */
    private $editRole;

    /**
     * The role that will grant delete permission for a comment.
     *
     * @var string
     */
    private $deleteRole;

    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->createRole = 'ROLE_SUIVEUR';
        $this->viewRole = 'ROLE_SUIVEUR';
        $this->editRole = 'ROLE_SUIVEUR';
        $this->deleteRole = 'ROLE_ADMIN';
    }

    /**
     * Checks if the Security token has an appropriate role to create a new Comment.
     *
     * @return bool
     */
    public function canCreate()
    {
        return $this->authorizationChecker->isGranted($this->createRole);
    }

    /**
     * Checks if the Security token is allowed to view the specified Comment.
     *
     * @param CommentInterface $comment
     *
     * @return bool
     */
    public function canView(CommentInterface $comment)
    {
        return $this->authorizationChecker->isGranted($this->viewRole);
    }

    /**
     * Checks if the Security token is allowed to reply to a parent comment.
     *
     * @param CommentInterface|null $parent
     *
     * @return bool
     */
    public function canReply(CommentInterface $parent = null)
    {
        if (null !== $parent) {
            return $this->canCreate() && $this->canView($parent);
        }

        return $this->canCreate();
    }

    /**
     * Checks if the Security token has an appropriate role to edit the supplied Comment.
     *
     * @param CommentInterface $comment
     *
     * @return bool
     */
    public function canEdit(CommentInterface $comment)
    {
        return $this->authorizationChecker->isGranted($this->editRole);
    }

    /**
     * Checks if the Security token is allowed to delete a specific Comment.
     *
     * @param CommentInterface $comment
     *
     * @return bool
     */
    public function canDelete(CommentInterface $comment)
    {
        return $this->authorizationChecker->isGranted($this->deleteRole);
    }

    /**
     * Role based Acl does not require setup.
     *
     * @param CommentInterface $comment
     */
    public function setDefaultAcl(CommentInterface $comment)
    {
    }

    /**
     * Role based Acl does not require setup.
     */
    public function installFallbackAcl()
    {
    }

    /**
     * Role based Acl does not require setup.
     */
    public function uninstallFallbackAcl()
    {
    }
}
