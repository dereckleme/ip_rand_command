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

#[AsCommand(name: 'ip_rand_command:remove_ip_output')]
class RemoveIpCommand extends Command
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
            ->setDescription('Remover o IP de saída.')
            ->addArgument('ip', InputArgument::REQUIRED, 'O endereço IP a ser removido');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ips = $this->cacheService->getCacheValue("ips");
        $ip       = $input->getArgument('ip');

        if (!in_array($ip, $ips)) {
            throw new InvalidArgumentException("O endereço IP {$ip} fornecido é inválido.");
        }

        $index = array_search($ip, $ips);

        unset($ips[$index]);

        $this->cacheService->setCacheValue("ips", $ips);

        $output->writeln("<info>Enderenço IP: $ip removido com sucesso.</info>");

        return Command::SUCCESS;
    }
}