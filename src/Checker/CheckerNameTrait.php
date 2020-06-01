<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

trait CheckerNameTrait
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return substr(get_class($this), strrpos(get_class($this), '\\') + 1, -7);
    }
}
