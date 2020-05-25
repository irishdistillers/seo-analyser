<?php declare(strict_types=1);

namespace SeoAnalyser\Exception;

use Symfony\Component\Console\Exception\ExceptionInterface;

class InvalidOptionException extends \InvalidArgumentException implements ExceptionInterface
{
}
