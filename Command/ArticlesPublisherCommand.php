<?php

namespace AppBundle\Command;

use SuluArticlesPublisherBundle\Services\ArticlesPublisherManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ArticlesPublisherCommand extends Command
{
    /**
     * @var ArticlesPublisherManager $manager
     */
    private $manager;

    /**
     * ArticlesPublisherCommand constructor.
     * @param ArticlesPublisherManager $manager
     */
    public function __construct(ArticlesPublisherManager $manager)
    {
        parent::__construct();
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            ->setName("sulu:articles:publish")
            ->setDescription('Publish every article after dump import.')
            ->setHelp('If previously published articles were removed from content var, execute this command to restore them.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Sulu\Component\DocumentManager\Exception\DocumentManagerException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager->publishArticles();
        $output->writeln('Articles were successfully published!');
    }
}