<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context
{
    use KernelDictionary;

    /**
     * @var array
     */
    private $classes;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $manager;

    /**
     * @var SchemaTool
     */
    private $schemaTool;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     *
     * @param ManagerRegistry $doctrine
     * @param KernelInterface $kernel
     */
    public function __construct(ManagerRegistry $doctrine, KernelInterface $kernel)
    {
        $this->doctrine = $doctrine;
        $this->manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($this->manager);
        $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();

        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario @createSchema
     * Will create the schema before scenario tagged with createSchema.
     */
    public function createDatabase()
    {
        $this->schemaTool->createSchema($this->classes);
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '-n' => true,
            '-e' => 'test',
        ]);
        $output = new BufferedOutput();
        $application->run($input, $output);

        $input = new ArrayInput([
            'command' => 'demo:create_data',
        ]);
        $output = new BufferedOutput();
        $application->run($input, $output);

        $input = new ArrayInput([
            'command' => 'demo:create_users',
        ]);
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    /**
     * @AfterScenario @dropSchema
     * Beware : the annotation in cucumber should also match the case (dropschema is invalid)
     */
    public function dropDatabase()
    {
        $this->schemaTool->dropSchema($this->classes);
    }

    /** @AfterStep */
    public function afterStep(AfterStepScope $event)
    {
        if (!$event->getTestResult()->isPassed()) {
            echo $this->getSession()->getCurrentUrl() . "\n-------";
            if (preg_match('/aside(.*?)footer/', $this->getSession()->getPage()->getContent(), $match) == 1) {
                echo $match[1];
            } else {
              echo $this->getSession()->getPage()->getContent();
            }
        }
    }

    /**
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        $this->visit('/login');
        $this->fillField('_username', $username);
        $this->fillField('_password', \App\Command\CreateTestUsersCommand::DEFAULT_USERS[$username]['password']);
        $this->pressButton('Connexion');
        assert($this->getSession()->getPage()->hasContent("Connecté en tant que"), "Connection as $username suceeded");
    }

    /**
     * @Given /^I see the "([^"]*)" etude page$/
     */
    public function iSeeTheEtudePage($name)
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $repository = $doctrine->getRepository(\App\Entity\Project\Etude::class);

        $etude = $repository->findOneBy([
            'nom' => $name,
        ]);

        $this->visit($this->getContainer()->get('router')->generate('project_etude_voir', ['nom' => $etude->getNom()]));
    }

    /**
     * @Given /^the user "([^"]*)" is active$/
     */
    public function theUserIsActive($username)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $repository = $doctrine->getRepository(\App\Entity\User\User::class);

        $user = $repository->findOneBy([
            'username' => $username,
        ]);
        $user->setEnabled(true);

        $doctrine->getManager()->flush();
    }
}
