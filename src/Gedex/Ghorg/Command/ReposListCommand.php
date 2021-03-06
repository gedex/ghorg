<?php

namespace Gedex\Ghorg\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReposListCommand extends AbstractCommand
{

    private $defaultDisplayedFields = array('name', 'description', 'language');

    public function configure()
    {
        $this
            ->setName('repos:list')
            ->setDescription('List of repositories in the organization')
            ->setDefinition(array(
                new InputArgument('org', InputArgument::REQUIRED, 'GitHub org'),
                new InputOption('fields', 'f', InputOption::VALUE_REQUIRED, 'Fields to display'),
                new InputOption('filter', 'F', InputOption::VALUE_REQUIRED, 'Filter the repos'),
                new InputOption('order', 'o', InputOption::VALUE_REQUIRED, 'Sort order. ASC or DESC'),
                new InputOption('orderby', 'b', InputOption::VALUE_REQUIRED, 'Order the repos'),
                new InputOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit returned repos'),
            ))
            ->setHelp(<<<EOT
List all repositories of an organization.

<comment>Filter parameters</comment>
TODO

<comment>Order by</comment>
TODO
EOT
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $githubClient = $this->get('githubClient');
        $paginator = new \Github\ResultPager($githubClient);
        $org = $input->getArgument('org');

        $filter = $input->getOption('filter');
        $filterArgs = array();
        if (!empty($filter)) {
            try {
                $filterArgs = $this->parseFilterString($filter);
            } catch (\Exception $e) {
                $output->writeln('<error>'.$e->getMessage().'</error>');
                exit(1);
            }
        }

        $orgReposApi = $githubClient->api('organization');
        $repos = $paginator->fetchAll($orgReposApi, 'repositories', array($org, $this->reposTypeApi));

        $fields = $input->getOption('fields');
        if (!empty($fields)) {
            $fields = array_map('trim', explode(',', $fields));
        } else {
            $fields = $this->defaultDisplayedFields;
        }

        $rows = array();
        foreach ($repos as $repo) {
            $repo = $this->flatten_array($repo);
            $row = array();
            foreach ($fields as $field) {
                if (!isset($repo[$field])) {
                    $repo[$field] = '';
                }
                if (is_bool($repo[$field])) {
                    $repo[$field] = $repo[$field] ? 'true' : 'false';
                }
                $row[$field] = $repo[$field];
            }
            $rows[] = $row;
        }

        $sortKey = null;
        if (($orderBy = $input->getOption('orderby')) && in_array($orderBy, $fields)) {
            $sortKey = $orderBy;
        }
        $sortOrder = 'DESC';
        if (($order = $input->getOption('order')) && in_array(strtolower($order), array('asc', 'desc'))) {
            $sortOrder = $order;
        }

        $rows = \Arrch\Arrch::find(
            $rows,
            array(
                'where' => $filterArgs,
                'sort_key' => $sortKey,
                'sort_order' => $sortOrder,
            )
        );

        if (($limit = intval($input->getOption('limit')))) {
            $rows = array_slice($rows, 0, $limit);
        }

        $table = new Table($output);
        $table
            ->setHeaders($fields)
            ->setRows($rows)
        ;
        $table->render();
        exit(0);
    }
}
