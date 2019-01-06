<?php
/*
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace App\Service\Comment;

use App\Entity\Comment\Comment;
use App\Entity\Comment\CommentInterface;
use App\Entity\Comment\ThreadInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
/**
 * Default ORM CommentManager.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class CommentManager extends AbstractCommentManager
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var EntityRepository
     */
    protected $repository;
    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor.
     *
     * @param SortingInterface         $sorter
     * @param ObjectManager            $em
     */
    public function __construct(SortingInterface $sorter, ObjectManager $em)
    {
        parent::__construct($sorter);
        $this->em = $em;
        $this->repository = $em->getRepository(Comment::class);
        $metadata = $em->getClassMetadata(Comment::class);
        $this->class = $metadata->name;
    }
    /**
     * {@inheritdoc}
     */
    public function findCommentsByThread(ThreadInterface $thread, $depth = null, $sorterAlias = null)
    {
        $qb = $this->repository
            ->createQueryBuilder('c')
            ->join('c.thread', 't')
            ->where('t.id = :thread')
            ->orderBy('c.ancestors', 'ASC')
            ->setParameter('thread', $thread->getId());
        if (null !== $depth && $depth >= 0) {
            // Queries for an additional level so templates can determine
            // if the final 'depth' layer has children.
            $qb->andWhere('c.depth < :depth')
                ->setParameter('depth', $depth + 1);
        }
        $comments = $qb
            ->getQuery()
            ->execute();
        if (null !== $sorterAlias) {
            $comments = $this->sorter->sortFlat($comments);
        }
        return $comments;
    }
    /**
     * {@inheritdoc}
     */
    public function findCommentTreeByCommentId($commentId, $sorter = null)
    {
        $qb = $this->repository->createQueryBuilder('c');
        $qb->join('c.thread', 't')
            ->where('LOCATE(:path, CONCAT(\'/\', CONCAT(c.ancestors, \'/\'))) > 0')
            ->orderBy('c.ancestors', 'ASC')
            ->setParameter('path', "/{$commentId}/");
        $comments = $qb->getQuery()->execute();
        if (!$comments) {
            return array();
        }
        $sorter = $this->sorter->getSorter($sorter);
        $trimParents = current($comments)->getAncestors();
        return $this->organiseComments($comments, $sorter, $trimParents);
    }
    /**
     * {@inheritdoc}
     */
    public function findCommentById($id)
    {
        return $this->repository->find($id);
    }
    /**
     * {@inheritdoc}
     */
    public function isNewComment(CommentInterface $comment)
    {
        return !$this->em->getUnitOfWork()->isInIdentityMap($comment);
    }
    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }
    /**
     * Performs persisting of the comment.
     *
     * @param CommentInterface $comment
     */
    protected function doSaveComment(CommentInterface $comment)
    {
        $this->em->persist($comment->getThread());
        $this->em->persist($comment);
        $this->em->flush();
    }
}
