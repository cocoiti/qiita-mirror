<?php

namespace Cocoiti\QiitaMirror\Controllr;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Qiita\Qiita;

class DumpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('dump')
            ->setDescription('dump data from qiita team')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();
        $accessToken = $app['config']['qiita_access_token'];
        $baseUrl = $app['config']['qiita_url'];
        $backupPath = $app['config']['backup_path'];

        $qiita = new Qiita($accessToken);
        $qiita->setBaseUrl($baseUrl);

        $page = 1;
        $perPage = 100;

        do {
            $items = $qiita->api('item.list', [
                'page' => $page,
                'per_page' => $perPage,
            ]);
            foreach ($items as $item) {
                if (!isset($item['id']) || preg_match('/[^a-zA-Z0-9]+$/', $item['id'])) {
                    $output->writeln(sprintf("invalid data format: %s", $item['id']));
                    continue;
                }
                file_put_contents(sprintf('%s/%s.json', $backupPath, $item['id']), json_encode($item));
                $output->writeln($item['title']);
            }
            $page++;
        } while (count($items) >= $perPage);

    }
}

