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

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractThread implements ThreadInterface
{
    /**
     * Id, a unique string that binds the comments together in a thread (tree).
     * It can be a url or really anything unique.
     *
     * @var string
     */
    protected $id;

    /**
     * Tells if new comments can be added in this thread.
     *
     * @var bool
     *
     * @ORM\Column(name="is_commentable", type="boolean")
     */
    protected $commentable = true;

    /**
     * Denormalized number of comments.
     *
     * @var int
     *
     * @ORM\Column(name="num_comments", type="integer")
     */
    protected $numComments = 0;

    /**
     * Denormalized date of the last comment.
     *
     * @var DateTime
     *
     * @ORM\Column(name="last_comment_at", type="datetime", nullable=true)
     */
    protected $lastCommentAt = null;

    /**
     * Url of the page where the thread lives.
     *
     * @var string
     *
     * @ORM\Column(name="permalink", type="string", length=127)
     */
    protected $permalink;

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Comment thread #'.$this->getId();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPermalink()
    {
        return $this->permalink;
    }

    /**
     * @param  string
     *
     * @return void
     */
    public function setPermalink($permalink)
    {
        $this->permalink = $permalink;
    }

    /**
     * @return bool
     */
    public function isCommentable()
    {
        return $this->commentable;
    }

    /**
     * @param  bool
     *
     * @return void
     */
    public function setCommentable($isCommentable)
    {
        $this->commentable = (bool) $isCommentable;
    }

    /**
     * Gets the number of comments.
     *
     * @return int
     */
    public function getNumComments()
    {
        return $this->numComments;
    }

    /**
     * Sets the number of comments.
     *
     * @param int $numComments
     */
    public function setNumComments($numComments)
    {
        $this->numComments = intval($numComments);
    }

    /**
     * Increments the number of comments by the supplied
     * value.
     *
     * @param int $by Value to increment comments by
     *
     * @return int The new comment total
     */
    public function incrementNumComments($by = 1)
    {
        return $this->numComments += intval($by);
    }

    /**
     * @return DateTime
     */
    public function getLastCommentAt()
    {
        return $this->lastCommentAt;
    }

    /**
     * @param  DateTime
     *
     * @return void
     */
    public function setLastCommentAt($lastCommentAt)
    {
        $this->lastCommentAt = $lastCommentAt;
    }
}
