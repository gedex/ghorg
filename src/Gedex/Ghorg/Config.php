<?php

namespace Gedex\Ghorg;

class Config {
    /**
     * Config array.
     *
     * @var array
     */
    private $config;

    /**
     * Config filepath.
     *
     * @var string
     */
    private $filePath;

    /**
     * Constructor.
     *
     * @param string $filepath Config's filepath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        if (!file_exists($this->filePath)) {
            $this->config = $this->getDefaultConfig();
            file_put_contents($this->filePath, json_encode($this->config));
        } else {
            $this->config = json_decode(file_get_contents($this->filePath), true);
        }
    }

    /**
     * Get default config properties.
     *
     * @return array Default config
     */
    private function getDefaultConfig()
    {
        return [
            'token' => '',
            'username' => '',
            'password' => '',
            'client_id' => '',
            'client_secret' => '',
            'auth_method' => '',
        ];
    }

    /**
     * Get config value from a given key.
     *
     * @param  string $key Config's key
     * @return mixed       Config's value for the given key
     */
    public function get($key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        return null;
    }

    /**
     * Return all configs
     *
     * @return array
     */
    public function getAll()
    {
        return $this->config;
    }

    /**
     * Set config.
     *
     * @param string $key   Config's key
     * @param mixed  $value Config's value
     *
     * @throws \Exception
     */
    public function set($key, $value)
    {
        $value = trim($value);
        switch ($key) {
            case 'client_secret':
            case 'token':
                if (!preg_match('/[a-zA-Z0-9]{40}/', $value)) {
                    throw new \Exception(sprintf('Invalid value for %s. Value MUST BE in alphabet with 40 chars in length.', $key));
                    return;
                }
                $this->config[$key] = $value;
                break;
            case 'client_id':
            case 'username':
            case 'password':
                if (empty($value)) {
                    throw new \Exception(sprintf('Invalid value for %s.', $key));
                    return;
                }
                if ('client_id' === $key && !preg_match('/[a-zA-Z0-9]{20}/', $value)) {
                    throw new \Exception('Invalid value for client_id. Value MUST BE in alphabet with 40 chars in length.');
                    return;
                }
                $this->config[$key] = $value;
                break;
            case 'auth_method':
                if (!in_array($value, array('token', 'client_id', 'password'))) {
                    throw new \Exception('Unknown authentication method. Valid value is "token", "client_id", or "password".');
                }
                $this->config[$key] = $value;
                break;
        }
        file_put_contents($this->filePath, json_encode($this->config));
    }
}
