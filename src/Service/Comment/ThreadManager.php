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

use App\Entity\Comment\Thread;
use App\Entity\Comment\ThreadInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Default ORM ThreadManager.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class ThreadManager extends AbstractThreadManager implements ThreadManagerInterface
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
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(Thread::class);

        $metadata = $em->getClassMetadata(Thread::class);
        $this->class = $metadata->name;
    }

    /**
     * Finds one comment thread by the given criteria.
     *
     * @param array $criteria
     *
     * @return ThreadInterface
     */
    public function findThreadBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findThreadsBy(array $criteria)
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * Finds all threads.
     *
     * @return array of ThreadInterface
     */
    public function findAllThreads()
    {
        return $this->repository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function isNewThread(ThreadInterface $thread)
    {
        return !$this->em->getUnitOfWork()->isInIdentityMap($thread);
    }

    /**
     * Returns the fully qualified comment thread class name.
     *
     * @return string
     **/
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Saves a thread.
     *
     * @param ThreadInterface $thread
     */
    protected function doSaveThread(ThreadInterface $thread)
    {
        $this->em->persist($thread);
        $this->em->flush();
    }
}
