<?php declare(strict_types=1);

namespace SeoAnalyser\Resource;

use JMS\Serializer\Annotation;
use Tightenco\Collect\Support\Collection;

/**
 * @Annotation\ExclusionPolicy("all")
 */
class Sitemap implements ResourceInterface
{
    /**
     * @var string
     * @Annotation\Expose
     * @Annotation\Type("string")
     */
    protected $url;

    /**
     * @var Collection
     * @Annotation\Expose
     * @Annotation\Type("iterable<SeoAnalyser\Resource\Location>")
     */
    protected $locations;

    /**
     * @var Collection
     * @Annotation\Expose
     * @Annotation\Type("iterable<SeoAnalyser\Resource\Error>")
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

    /**
     * {@inheritDoc}
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param Location $location
     */
    public function addLocation(Location $location)
    {
        $this->locations->push($location);
    }

    /**
     * @return Collection<Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    /**
     * {@inheritDoc}
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function addError(Error $error)
    {
        $this->errors->push($error);
    }

    /**
     * {@inheritDoc}
     */
    public function addErrors(Collection $errors)
    {
        $this->errors = $this->errors->merge($errors);
    }

    /**
     * {@inheritDoc}
     */
    public function getErrors(): Collection
    {
        return $this->errors;
    }

    /**
     * {@inheritDoc}
     */
    public function hasParent(): bool
    {
        return !empty($this->parent);
    }

    /**
     * @param ResourceInterface $parent
     */
    public function setParent(ResourceInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ResourceInterface
    {
        return $this->parent;
    }
}
