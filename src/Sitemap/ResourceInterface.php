<?php declare(strict_types=1);

namespace SeoAnalyser\Sitemap;

use Tightenco\Collect\Support\Collection;

interface ResourceInterface
{
    public function getUrl();

    public function hasErrors(): bool;

    public function addError(Error $error);

    public function addErrors(Collection $errors);

    public function getErrors(): Collection;
}
