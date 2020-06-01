<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Resource\Error;
use Symfony\Component\Validator\ConstraintViolationList;

trait ValidatorTrait
{
    /**
     * @param  ConstraintViolationList $violations
     * @param  string                  $severity
     */
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
