<?php

namespace SeoAnalyser\Checker;

use SeoAnalyser\Sitemap\Error;
use Symfony\Component\Validator\ConstraintViolationList;

trait ValidatorTrait
{
    public function pushViolationsErrors(
        ConstraintViolationList $violations,
        string $severity
    ) {
        if (0 !== count($violations)) {
            foreach ($violations as $violation) {
                $this->errors->push(
                    new Error(
                        $violation->getMessage(),
                        $severity
                    )
                );
            }
        }
    }
}
