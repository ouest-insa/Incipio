<?php

namespace Mgate\DashboardBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Mgate\UserBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestUsersCommand extends Command
{
    /** @var ObjectManager */
    private $em;

    public function __construct(ObjectManager $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('demo:create_users')
            ->setDescription('Create some demonstration users')
            ->setHelp('Creates some fake user account for testing purpose.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createUsers($output);

        $output->writeln('Done.');
    }

    /**
     * Create users based on FeatureContext values.
     *
     * @param OutputInterface $output
     */
    private function createUsers(OutputInterface $output)
    {
        foreach (\FeatureContext::DEFAULT_USERS as $user => $attributes) {
            if ('admin' === $user) { // already created by fixtures
                continue;
            }
            $u = new User();
            $u->setUsername($attributes['username']); //mettre le login de l'admin
            $u->setPlainPassword($attributes['password']); //mettre le mdp de l'admin
            $u->setEmail($attributes['username'] . '@local.localdomain.com');
            $u->setEnabled(true);
            $u->setRoles($attributes['roles']);

            $this->em->persist($u);
        }
        $this->em->flush();
        $output->writeln('Test users: Ok');
    }
}
