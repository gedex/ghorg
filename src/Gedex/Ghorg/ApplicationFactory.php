<?php

namespace Gedex\Ghorg;

class ApplicationFactory {
    const NAME = 'ghorg';

    const VERSION = '@package_version@';

    /**
     * Create application.
     *
     * @return Application
     */
    public function createApplication()
    {
        $configFile = getenv('HOME').'/.ghorg.json';
        $config = new Config($configFile);
        $githubClient = new \Github\Client(
            new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/ghorg-github-api-cache'))
        );

        $authMethod = $config->get('auth_method');
        switch ($authMethod) {
            case 'token':
                $token = $config->get('token');
                if (!empty($token)) {
                    $githubClient->authenticate($token, '', \Github\Client::AUTH_HTTP_TOKEN);
                }
                break;
            case 'client_id':
                $client_id = $config->get('client_id');
                $client_secret = $config->get('client_secret');
                if (!empty($client_id) && !empty($client_secret)) {
                    $githubClient->authenticate($client_id, $client_secret, \Github\Client::AUTH_URL_CLIENT_ID);
                }
                break;
            case 'password':
                $username = $config->get('username');
                $password = $config->get('password');
                if (!empty($username) && !empty($password)) {
                    $githubClient->authenticate($username, $password, \Github\Client::AUTH_HTTP_PASSWORD);
                }
                break;
        }

        $application = new Application(self::NAME, self::VERSION, $config, $githubClient);
        $application->addCommands($this->getDefaultCommands());

        return $application;
    }

    /**
     * Get default commands.
     *
     * @return array Default commands
     */
    protected function getDefaultCommands()
    {
        return [
            new Command\ConfigCommand(),
            new Command\MembersListCommand(),
            new Command\ReposListCommand(),
        ];
    }
}
