<?php
/**
 * Created by PhpStorm.
 * User: pvasouilles
 * Date: 09/06/2016
 * Time: 17:06
 */

namespace IT\SearchBundle\Command;


use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BaseCommand
 *
 * Abstract command class that can be user in all other commands.
 * Provides some utilities like debug and fake mode, messages print or print of the total exec time.
 *
 * @package TaskBundle\Command
 */
abstract class BaseCommand extends ContainerAwareCommand
{

    /** @var bool $debug */
    protected $debug;

    /** @var bool $fake */
    protected $fake;

    /** @var EntityManager $em */
    protected $em;

    /** @var OutputInterface $output */
    protected $output;

    /** @var float $startTime */
    protected $startTime;


    /**
     * Configuration of commands extending this class
     *
     * @return mixed
     */
    abstract public function configureCommand();

    /**
     * Execution of commands extending this class
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    abstract public function executeCommand(InputInterface $input, OutputInterface $output);

    /**
     * Base command configuration.
     * Profives 2 options : debug and fake mode
     */
    protected function configure()
    {
        $this
            // Debug option : When you use the method "printMessage", the message is only printed if the debug mode is used
            ->addOption(
                'debug',
                'd',
                InputOption::VALUE_NONE,
                'Debug mode'
            )
            // Fake option : Tool provided for the commands development. Usually, fake mode doesn't save objects or send emails
            ->addOption(
                'fake',
                'f',
                InputOption::VALUE_NONE,
                'Fake mode'
            );

        $this->configureCommand();
    }

    /**
     * Base command execution.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->debug = $input->getOption('debug');
        $this->fake = $input->getOption('fake');
        $this->em = $this->getContainer()->get('doctrine')->getManager();


        $this->startTime = microtime(true);
        $this->printMessage('info', 'Début du traitement');


        try {
            $this->executeCommand($input, $output);
        } catch (\Exception $e) {
            $this->printMessage('error', $e->getMessage());
            $this->printMessage('error', $e->getTraceAsString());
            throw $e;
        }

    }

    /**
     * Prints a message to the command line.
     * The message is printed only if the DEBUG option is TRUE.
     *
     * @param $level
     * @param $message
     */
    protected function printMessage($level, $message)
    {
        if ($this->debug) {
            $date = new \DateTime('now');
            $this->output->writeln(($this->fake ? '[fake] ' : '') . sprintf('<%s>[%s] - %s</%s>', $level, $date->format('d-m-Y H:i:s'), $message, $level));
        }
    }

    /**
     * Destructor. Prints the total execution time.
     */
    public function __destruct()
    {
        $this->printMessage('info', 'Fin du traitement');
        $tps = microtime(true) - $this->startTime;
        $this->printMessage('info', sprintf('Temps d\'execution : %f sec.', $tps));
    }

}