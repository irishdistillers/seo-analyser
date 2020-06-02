<?php declare(strict_types=1);

namespace SeoAnalyser\Resource;

use JMS\Serializer\Annotation;

/**
 * @Annotation\ExclusionPolicy("all")
 */
class Error
{
    /**
     * @var string
     */
    const SEVERITY_LOW = 'Low';

    /**
     * @var string
     */
    const SEVERITY_NORMAL = 'Normal';

    /**
     * @var string
     */
    const SEVERITY_HIGH = 'High';

    /**
     * @var string
     * @Annotation\Expose
     * @Annotation\Type("string")
     */
    private $severity;

    /**
     * @var string
     * @Annotation\Expose
     * @Annotation\Type("string")
     */
    private $description;

    public function __construct(string $description, string $severity)
    {
        $this->description = $description;
        $this->severity = $severity;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }
}
