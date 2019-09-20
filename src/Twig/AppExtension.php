<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Twig;

use App\Service\MedalChecker;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AppExtension
 */
class AppExtension extends AbstractExtension
{
    /**
     * @var MedalChecker
     */
    private $medalChecker;

    public function __construct(MedalChecker $medalChecker)
    {
        $this->medalChecker = $medalChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('cast_to_array', [$this, 'objectFilter']),
            new TwigFilter('medalLevel', [$this, 'medalLevelFilter']),
        ];
    }

    /**
     * Convert object to array for Twig usage..
     *
     * @param object $classObject
     *
     * @return array
     */
    public function objectFilter($classObject): array
    {
        $array    = (array)$classObject;
        $response = [];

        $className = \get_class($classObject);

        foreach ($array as $k => $v) {
            $response[trim(str_replace($className, '', $k))] = $v;
        }

        return $response;
    }

    public function medalLevelFilter($level)
    {
        return $this->medalChecker->getLevelName($level);

    }
}
