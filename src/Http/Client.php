<?php declare(strict_types=1);

namespace SeoAnalyser\Http;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use SeoAnalyser\Exception\InvalidAuthOptionException;
use Tightenco\Collect\Support\Collection;

class Client
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var Collection
     */
    private $auth;

    /**
     * @var string
     */
    private $version;

    /**
     * @param GuzzleClient $client
     */
    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;

        $this->auth = new Collection;
    }

    /**
     * @param  string $url
     * @return ResponseInterface
     */
    public function get(string $url): ResponseInterface
    {
        $urlParts = parse_url($url);

        $options = [];
        if ($this->auth->has($urlParts['host'])) {
            $options = ['auth' => $this->auth->get($urlParts['host'])];
        }

        $options['headers'] = ['User-Agent' => 'SeoAnalyser/'.$this->version];

        return $this->client->get($url, $options);
    }

    /**
     * @param string $version
     */
    public function setCliVersion(string $version)
    {
        $this->version = $version;
    }

    /**
     * @param  array  $options
     */
    public function configAuth(array $options)
    {
        foreach ($options as $option) {
            if (!preg_match('/(.*):(.*)@(.*)/', $option, $matches)) {
                throw new InvalidAuthOptionException(
                    sprintf('Auth option %s is not the right format: user:pwd@host', $option)
                );
            }

            $this->auth->put($matches[3], [$matches[1], $matches[2]]);
        }
    }
}
