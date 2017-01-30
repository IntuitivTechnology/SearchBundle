<?php
/**
 * Created by PhpStorm.
 * User: pvassoilles
 * Date: 02/01/17
 * Time: 11:14
 */

namespace IT\SearchBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildDatabaseIndexCommand extends BaseCommand
{

    public function configureCommand()
    {
        $this
            ->setName('search:index:build')
            ->setDescription('Builds the database search index');
    }

    public function executeCommand(InputInterface $input, OutputInterface $output)
    {
        $indexes = $this->getContainer()->get('it_search.database.indexer')->indexContent();
        $this->printMessage('comment', sprintf('%d items indexed', count($indexes)));
    }

}