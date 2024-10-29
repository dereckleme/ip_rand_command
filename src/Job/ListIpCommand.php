<?php

namespace App\Job;

// the name of the command is what users type after "php bin/console"
use App\Service\CacheService;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'ip_rand_command:list_ip_output')]
class ListIpCommand extends Command
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Exibir todos IPS.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ips = $this->cacheService->getCacheValue("ips");

        foreach ($ips as $ip) {
            $output->writeln("<info>$ip</info>");
        }

        return Command::SUCCESS;
    }
}