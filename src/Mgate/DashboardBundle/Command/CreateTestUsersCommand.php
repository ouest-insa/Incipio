<?php

namespace Mgate\DashboardBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Mgate\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestUsersCommand extends ContainerAwareCommand
{
    /** user by tests, should remain public */
    public const DEFAULT_USERS = [
        'admin' => ['username' => 'admin', 'password' => 'admin', 'roles' => ['ROLE_SUPER_ADMIN']],
        'eleve' => ['username' => 'eleve', 'password' => 'eleve', 'roles' => ['ROLE_ELEVE']],
        'suiveur' => ['username' => 'suiveur', 'password' => 'suiveur', 'roles' => ['ROLE_SUIVEUR']],
        'treso' => ['username' => 'treso', 'password' => 'treso', 'roles' => ['ROLE_TRESO']],
        'rgpd' => ['username' => 'rgpd', 'password' => 'rgpd', 'roles' => ['ROLE_RGPD']],
        'ca' => ['username' => 'ca', 'password' => 'ca', 'roles' => ['ROLE_CA']],
    ];

    /** @var ObjectManager */
    private $em;

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
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');

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
        foreach (self::DEFAULT_USERS as $user => $attributes) {
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
