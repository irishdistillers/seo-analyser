<?php declare(strict_types=1);

namespace SeoAnalyser\Sitemap;

use Tightenco\Collect\Support\Collection;

class Sitemap implements ResourceInterface
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var Collection
     */
    protected $locations;

    /**
     * @var Collection
     */
    protected $errors;

    /**
     * @var ResourceInterface
     */
    protected $parent;

    public function __construct(string $url)
    {
        $this->url = $url;

        $this->locations = new Collection;
        $this->errors = new Collection;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function addLocation(Location $location)
    {
        $this->locations->push($location);
    }

    public function getLocations(): Collection
    {
        return $this->locations;
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

    public function hasParent(): bool
    {
        return !empty($this->parent);
    }

    public function setParent(ResourceInterface $parent)
    {
        $this->parent = $parent;
    }

    public function getParent(): ResourceInterface
    {
        return $this->parent;
    }
}
