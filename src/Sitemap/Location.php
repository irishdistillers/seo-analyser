<?php declare(strict_types=1);

namespace SeoAnalyser\Sitemap;

use Tightenco\Collect\Support\Collection;

class Location implements ResourceInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var Collection
     */
    private $errors;

    public function __construct(string $url)
    {
        $this->url = $url;

        $this->errors = new Collection;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function addError(Error $error)
    {
        $this->errors->push($error);
    }

    public function addErrors(Collection $errors)
    {
        $this->errors = $this->errors->merge($errors);
    }

    public function getErrors(): Collection
    {
        return $this->errors;
    }
}
