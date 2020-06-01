<?php declare(strict_types=1);

namespace SeoAnalyser\Resource;

use Tightenco\Collect\Support\Collection;

interface ResourceInterface
{
    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @return boolean
     */
    public function hasErrors(): bool;

    /**
     * @param Error $error
     */
    public function addError(Error $error);

    /**
     * @param Collection<Error> $errors
     */
    public function addErrors(Collection $errors);

    /**
     * @return Collection<Error>
     */
    public function getErrors(): Collection;

    /**
     * @return boolean
     */
    public function hasParent(): bool;

    /**
     * @return ResourceInterface
     */
    public function getParent(): ResourceInterface;
}
