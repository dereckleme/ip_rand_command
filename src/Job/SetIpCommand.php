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

#[AsCommand(name: 'ip_rand_command:set_ip_output')]
class SetIpCommand extends Command
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
            ->setDescription('Configurar o IP de saída.')
            ->addArgument('ip', InputArgument::REQUIRED, 'O endereço IP a ser configurado');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Iniciando configuração de IP de saída</info>');

        $ips      = $this->cacheService->getCacheValue("ips");
        $ip       = $input->getArgument('ip');
        $totalIps = explode(",", $ip);

        if ($totalIps > 1) {
            foreach ($totalIps as $ip) {
                if (!filter_var(trim($ip), FILTER_VALIDATE_IP)) {
                    throw new InvalidArgumentException("O endereço IP {$ip} fornecido é inválido.");
                } else if (in_array(trim($ip), $ips)) {
                    throw new InvalidArgumentException("O endereço IP {$ip} já consta inserido.");
                }

                $this->setIps($output, trim($ip), $ips);
            }
        } else {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                throw new InvalidArgumentException('O endereço IP fornecido é inválido.');
            } else if (in_array($ip, $ips)) {
                throw new InvalidArgumentException('O endereço IP já consta inserido.');
            }
        }

        $this->setIps($output, trim($ip), $ips);
        $output->writeln('<info>Lista de Ip atualizada:</info>');

        foreach ($this->cacheService->getCacheValue("ips") as $ip) {
            $output->writeln("<info>{$ip}</info>");
        }

        return Command::SUCCESS;
    }

    public function setIps(OutputInterface $output, mixed $ip, mixed $ips): void
    {
        array_push($ips, $ip);
        $this->cacheService->setCacheValue("ips", $ips);
    }
}