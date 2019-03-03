<?php

/*
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity\Comment;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractComment implements CommentInterface
{
    /**
     * Comment id.
     *
     * @var mixed
     */
    protected $id;

    /**
     * Parent comment id.
     *
     * @var CommentInterface
     */
    protected $parent;

    /**
     * All ancestors of the comment.
     *
     * @var string
     *
     * @ORM\Column(name="ancestors", type="string", length=1024, nullable=false)
     */
    protected $ancestors = '';

    /**
     * Comment text.
     *
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     */
    protected $body;

    /**
     * The depth of the comment.
     *
     * @var int
     *
     * @ORM\Column(name="depth", type="integer")
     */
    protected $depth = 0;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * Current state of the comment.
     *
     * @var int
     *
     * @ORM\Column(name="state", type="integer")
     */
    protected $state = 0;

    /**
     * The previous state of the comment.
     *
     * @var int
     */
    protected $previousState = 0;

    /**
     * Should be mapped by the end developer.
     *
     * @var ThreadInterface
     */
    protected $thread;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Comment #' . $this->getId();
    }

    /**
     * Return the comment unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param  string
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return string name of the comment author
     */
    public function getAuthorName()
    {
        return 'Anonymous';
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation date.
     *
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Returns the depth of the comment.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(CommentInterface $parent)
    {
        $this->parent = $parent;

        if (!$parent->getId()) {
            throw new InvalidArgumentException('Parent comment must be persisted.');
        }

        $ancestors = $parent->getAncestors();
        $ancestors[] = $parent->getId();

        $this->setAncestors($ancestors);
    }

    /**
     * @return ThreadInterface
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param ThreadInterface $thread
     */
    public function setThread(ThreadInterface $thread)
    {
        $this->thread = $thread;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        $this->previousState = $this->state;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousState()
    {
        return $this->previousState;
    }

    /**
     * @return array
     */
    public function getAncestors()
    {
        return $this->ancestors ? explode('/', $this->ancestors) : [];
    }

    /**
     * @param  array
     */
    public function setAncestors(array $ancestors)
    {
        $this->ancestors = implode('/', $ancestors);
        $this->depth = count($ancestors);
    }
}
