<?php declare(strict_types=1);

namespace SeoAnalyser\Sitemap;

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
     * @Annotation\Type("iterable<SeoAnalyser\Sitemap\Error>")
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

    public function hasParent(): bool
    {
        return true;
    }

    public function getParent(): ResourceInterface
    {
        return $this->parent;
    }
}
