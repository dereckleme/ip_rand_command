<?php

namespace App\Job;

use App\Service\CacheService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'ip_rand_command:rand_scheduling')]
class RandSchedulingCommand extends Command
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
            ->setDescription('Randomizar IPS.');
    }

    /**
     * @param mixed $ipsAvailable
     * @param mixed $ipsSeted
     * @return mixed
     */
    public function setIpSeted(mixed $ipsAvailable, mixed $ipsSeted): string
    {
        $ipRand = array_rand($ipsAvailable);
        array_push($ipsSeted, $ipsAvailable[$ipRand]);
        $this->cacheService->setCacheValue("ips_seted", $ipsSeted);
        return $ipsAvailable[$ipRand];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$this->changeLine('/etc/exim.pl', 4, 'Nova linha 4');
        $this->changeLine('/Users/dereckleme/www/ip_rand_command/exim.pl', 4, $output);

        return Command::SUCCESS;
    }

    protected function changeLine($filePath, $lineNumber, OutputInterface $output) {
        $ipsSeted = $this->cacheService->getCacheValue("ips_seted");
        $ips      = $this->cacheService->getCacheValue("ips");
        $ipsAvailable = array_diff($ips, $ipsSeted);
        $fileContents = file($filePath, FILE_IGNORE_NEW_LINES);

        if (count($ips) >= 1 && count($ipsAvailable) == 0) {
            $ipLast = $this->cacheService->getCacheValue("ip_last");

            if ($ips > 1 && count($ipLast) >= 1)  {
                $this->cacheService->setCacheValue("ips_seted", [$ipLast[0]]);
            } else {
                $this->cacheService->setCacheValue("ips_seted", []);
            }

            $ipsSeted = $this->cacheService->getCacheValue("ips_seted");
            $ips      = $this->cacheService->getCacheValue("ips");
            $ipsAvailable = array_diff($ips, $ipsSeted);
        }

        if (count($ips) == 0) {
            $output->writeln("<info>Não existe ip's disponíveis.</info>");
            return Command::FAILURE;
        }

        if (isset($fileContents[$lineNumber - 1])) {
            $ipSeted = (count($ips) == 1) ? $ips[0] : $this->setIpSeted($ipsAvailable, $ipsSeted);
            $output->writeln("<info>Arquivo alterado para IP: {$ipSeted}</info>");
            $fileContents[$lineNumber - 1] = '@inet = ("'.trim($ipSeted).'");';
            file_put_contents($filePath, implode(PHP_EOL, $fileContents));
            array_push($ipsSeted, $ipSeted);
            $this->cacheService->setCacheValue("ips_seted", $ipsSeted);
            $this->cacheService->setCacheValue("ip_last", [$ipSeted]);
            $this->restartServiceExim($output);
        }
    }

    protected function restartServiceExim(OutputInterface $outputCmd)
    {
        $command = 'sudo systemctl restart exim';
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        if ($returnVar === 0) {
            $outputCmd->writeln("<info>Serviço Exim reiniciado com sucesso.</info>");
        } else {
            $outputCmd->writeln("<error>Falha ao reiniciar o serviço Exim. Código de erro: $returnVar</error>");
        }
    }
}