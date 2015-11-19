<?php

namespace Gedex\Ghorg\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends AbstractCommand {
    public function configure()
    {
        $this
            ->setName('config')
            ->setDescription('ghorg config')
            ->setDefinition(array(
                new InputOption('list', 'l'),
                new InputArgument('config-key', null, 'Config key'),
                new InputArgument('config-value', null, 'Config value'),
            ))
            ->setHelp(<<<EOT
Set ghorg config that will be saved in <info>~/.ghorg.json</info>.

To set a config key, for example <info>token</info>:

    <info>%command.full_name% token YOUR_GITHUB_TOKEN</info>

If second argument is omitted, then value for that config key is returned.

<comment>Available config keys:</comment>

    <info>token</info>          Your GitHub token
    <info>client_id</info>      GitHub client ID. Used in conjunction with client_secret.
    <info>client_secret</info>  GitHub client secret. Used in conjunction with client_id.
    <info>username</info>       Your GitHub username.
    <info>password</info>       Your GitHub password.
    <info>auth_method</info>    Authentication method. Valid value is "token", "client_id", or "password".

The easiest way is to use personal access token which can be created from:

    https://github.com/settings/tokens

If you're using personal token, set <info>auth_method</info> to <info>token</info>:

    <info>%command.full_name% auth_method token</info>

Using username and password is NOT encouraged as it will be stored as JSON file.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->get('config');

        $configKey = $input->getArgument('config-key');
        $configVal = $input->getArgument('config-value');

        if ($input->getOption('list') || (empty($configKey) && empty($configVal))) {
            $rows = array();
            foreach ($config->getAll() as $key => $val) {
                $rows[] = array($key, $val);
            }

            $table = new Table($output);
            $table
                ->setHeaders(array('Key', 'Value'))
                ->setRows($rows)
            ;
            $table->render();
            exit(0);
        }

        try {
            if (!empty($configVal)) {
                $config->set($configKey, $configVal);
            } else {
                $val = $config->get($configKey);
                if (!empty($val)) {
                    $output->write($val);
                }
            }
            exit(0);
        } catch (\Exception $e) {
            $output->write('<error>'.$e->getMessage().'</error>');
            exit(1);
        }
    }
}
