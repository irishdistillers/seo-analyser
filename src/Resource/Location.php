<?php declare(strict_types=1);

namespace SeoAnalyser\Resource;

use JMS\Serializer\Annotation;
use Tightenco\Collect\Support\Collection;

/**
 * @Annotation\ExclusionPolicy("all")
 */
class Location implements ResourceInterface
{
    /**
     * @var string
     * @Annotation\Expose
     * @Annotation\Type("string")
     */
    private $url;

    /**
     * @var Collection
     * @Annotation\Expose
     * @Annotation\Type("iterable<SeoAnalyser\Resource\Error>")
     */
    private $errors;

    /**
     * @var ResourceInterface
     */
    protected $parent;

    public function __construct(string $url, Sitemap $parent)
    {
        $this->url = $url;

        $this->errors = new Collection;
        $this->parent = $parent;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl(): string
    {
        return $this->url;
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
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ResourceInterface
    {
        return $this->parent;
    }
}
