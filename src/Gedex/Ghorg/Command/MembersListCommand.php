<?php

namespace Gedex\Ghorg\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MembersListCommand extends AbstractCommand
{

    private $defaultDisplayedFields = array('id', 'login', 'type', 'site_admin');

    public function configure()
    {
        $this
            ->setName('members:list')
            ->setDescription('List of members in the organization')
            ->setDefinition(array(
                new InputArgument('org', InputArgument::REQUIRED, 'GitHub org'),
                new InputOption('detail', 'd', InputOption::VALUE_NONE, 'Get detailed information on user. Require another API request for each user'),
                new InputOption('fields', 'f', InputOption::VALUE_REQUIRED, 'Fields to display'),
                new InputOption('filter', 'F', InputOption::VALUE_REQUIRED, 'Filter the members'),
                new InputOption('order', 'o', InputOption::VALUE_REQUIRED, 'Sort order. ASC or DESC'),
                new InputOption('orderby', 'b', InputOption::VALUE_REQUIRED, 'Order the members'),
                new InputOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit returned members'),
            ))
            ->setHelp(<<<EOT
List all users who are members of an organization. If you've set <info>auth_method</info>
in config then both concealed and public members might be displayed if you're
member of the org too.

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

        $orgMembersApi = $githubClient->api('organization')->members();
        $members = $paginator->fetchAll($orgMembersApi, 'all', array($org, null, $this->membersFilterApi));

        $fields = $input->getOption('fields');
        if (!empty($fields)) {
            $fields = array_map('trim', explode(',', $fields));
        } else {
            $fields = $this->defaultDisplayedFields;
        }

        $detail = $input->getOption('detail');

        $rows = array();
        foreach ($members as $member) {
            if ($detail) {
                $member = $githubClient->api('user')->show($member['login']);
            }
            $member = $this->flatten_array($member);
            $row = array();
            foreach ($fields as $field) {
                if (!isset($member[$field])) {
                    $member[$field] = '';
                }
                if (is_bool($member[$field])) {
                    $member[$field] = $member[$field] ? 'true' : 'false';
                }
                $row[$field] = $member[$field];
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
