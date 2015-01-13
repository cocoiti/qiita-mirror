<?php

namespace Cocoiti\QiitaMirror\Command;

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

        $itemList = [];
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
                file_put_contents(sprintf('%s/items/%s.json', $backupPath, $item['id']), json_encode($item));
                $itemList[] = [
                    'title' => $item['title'],
                    'id' => $item['id'],
                ];
                $output->writeln($item['title']);
            }
            $page++;
        } while (count($items) >= $perPage);

        file_put_contents(sprintf('%s/items/list.json', $backupPath), json_encode($itemList));

        $page = 1;
        $perPage = 100;

        $projectList = [];
        do {
            $projects = $qiita->api('project.list', [
                'page' => $page,
                'per_page' => $perPage,
            ]);
            foreach ($projects as $project) {
                if (!isset($project['id']) || preg_match('/[^a-zA-Z0-9]+$/', $project['id'])) {
                    $output->writeln(sprintf("invalid data format: %s", $project['id']));
                    continue;
                }
                file_put_contents(sprintf('%s/projects/%s.json', $backupPath, $project['id']), json_encode($project));
                $projectList[] = [
                    'name' => $project['name'],
                    'id' => $project['id'],
                ];
                $output->writeln($project['name']);
            }
            $page++;
        } while (count($project) >= $perPage);

        file_put_contents(sprintf('%s/projects/list.json', $backupPath), json_encode($projectList));
 
    }
}

