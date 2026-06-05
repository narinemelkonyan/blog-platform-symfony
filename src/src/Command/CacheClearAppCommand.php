<?php

namespace App\Command;

use App\Repository\ArticleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Clears application-level Redis cache pools.
 */
#[AsCommand(
    name: 'app:cache:clear',
    description: 'Clears application cache pools (popular articles, heavy queries)',
)]
class CacheClearAppCommand extends Command
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->articleRepository->invalidatePopularCache();
        $this->articleRepository->invalidateHeavyCache();

        $io->success('Application cache cleared successfully.');

        return Command::SUCCESS;
    }
}
