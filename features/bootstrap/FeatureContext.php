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
     * This handler doesn't seems to be executed before each scenario, but only when a scenario
     * is annotated with createSchema. Tried without BeforeScenario and it was not working as expected, so keep it.
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
            echo $this->getSession()->getCurrentUrl() . "\n\n-------";
            echo substr($this->getSession()->getPage()->getContent(), 100, 200); // the title of the page
            echo "\n\n------";
            echo substr($this->getSession()->getPage()->getContent(), 13500, 15000);
        }
    }

    /**
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        $this->visit('/login');
        $this->fillField('_username', $username);
        $this->fillField('_password', \Mgate\DashboardBundle\Command\CreateTestUsersCommand::DEFAULT_USERS[$username]['password']);
        $this->pressButton('Connexion');
    }

    /**
     * @Given /^I see the "([^"]*)" etude page$/
     */
    public function iSeeTheEtudePage($name)
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $repository = $doctrine->getRepository('Mgate\EtudeBundle\Entity\Etude');

        $etude = $repository->findOneBy([
            'nom' => $name,
        ]);

        $this->visit($this->getContainer()->get('router')->generate('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]));
    }

    /**
     * @Given /^the user "([^"]*)" is active$/
     */
    public function theUserIsActive($username)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $repository = $doctrine->getRepository('Mgate\UserBundle\Entity\User');

        $user = $repository->findOneBy([
            'username' => $username,
        ]);
        $user->setEnabled(true);

        $doctrine->getManager()->flush();
    }
}
