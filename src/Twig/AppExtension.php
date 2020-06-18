<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Twig;

use App\Service\IntlDateHelper;
use App\Service\MarkdownHelper;
use App\Service\MedalChecker;
use App\Util\BadgeData;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class AppExtension
 */
class AppExtension extends AbstractExtension
{
    public $roleFilters
        = [
            'ROLE_AGENT'  => 'Agent',
            'ROLE_INTRO_AGENT'  => 'Intro Agent',
            'ROLE_EDITOR' => 'Editor',
            'ROLE_ADMIN'  => 'Admin',
        ];

    /**
     * @var MedalChecker
     */
    private $medalChecker;

    /**
     * @var MarkdownHelper
     */
    private $markdownHelper;
    /**
     * @var IntlDateHelper
     */
    private $intlDateHelper;

    public function __construct(MedalChecker $medalChecker, MarkdownHelper $markdownHelper, IntlDateHelper $intlDateHelper)
    {
        $this->medalChecker = $medalChecker;
        $this->markdownHelper = $markdownHelper;
        $this->intlDateHelper = $intlDateHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('cast_to_array', [$this, 'objectFilter']),
            new TwigFilter('medalLevel', [$this, 'medalLevelFilter']),
            new TwigFilter('medalLevelName', [$this, 'getMedalLevelName']),
            new TwigFilter(
                'translateMedalLevel', [
                    $this,
                    'translateMedalLevelFilter',
                ]
            ),
            new TwigFilter('medalDesc', [$this, 'medalDescFilter']),
            new TwigFilter('stripGmail', [$this, 'stripGmail']),
            new TwigFilter('displayRoles', [$this, 'displayRolesFilter']),
            new TwigFilter('ucfirst', [$this, 'displayUcFirst']),
            new TwigFilter('formatIntlDate', [$this, 'formatIntlDate']),
            new TwigFilter('intDateShort', [$this, 'intDateShort']),
            new TwigFilter(
                'md2html', [
                $this,
                'markdownToHtml',
            ], ['is_safe' => ['html']]
            ),
            new TwigFilter('stripTitle', [$this, 'medalDescFilter']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('medalValue', [$this, 'getMedalValue']),
            new TwigFunction('medalLevel', [$this, 'getMedalLevel']),
            new TwigFunction('medalLevelNames', [$this, 'getMedalLevelNames']),
            new TwigFunction('medalDoubleValue', [$this, 'medalDoubleValue']),
            new TwigFunction('getBadgePath', [$this, 'getBadgePath']),
            new TwigFunction('getChallengePath', [$this, 'getChallengePath']),
            new TwigFunction('getBadgeData', [$this, 'getBadgeData']),
            new TwigFunction('php_version', [$this, 'getPhpVersion']),
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
        $array = (array)$classObject;
        $response = [];

        $className = \get_class($classObject);

        foreach ($array as $k => $v) {
            $response[trim(str_replace($className, '', $k))] = $v;
        }

        return $response;
    }

    public function medalLevelFilter(int $level): string
    {
        return $this->medalChecker->getLevelName($level);
    }

    public function medalDescFilter($medal): string
    {
        return $this->medalChecker->getDescription($medal);
    }

    public function displayRolesFilter(array $roles): string
    {
        $roles = array_diff($roles, ['ROLE_USER']);

        $displayRoles = [];

        foreach ($roles as $role) {
            if (array_key_exists($role, $this->roleFilters)) {
                $displayRoles[] = $this->roleFilters[$role];
            } else {
                $displayRoles[] = $role;
            }
        }

        return implode(', ', $displayRoles);
    }

    public function displayUcFirst(string $string): string
    {
        return ucfirst($string);
    }

    /**
     * Transforms the given Markdown content into HTML content.
     */
    public function markdownToHtml(string $content): string
    {
        return $this->markdownHelper->parse($content);
    }

    public function getMedalValue(string $medal, int $level): int
    {
        return $this->medalChecker->getLevelValue($medal, $level);
    }

    public function getMedalLevel(string $medal, int $value): int
    {
        return $this->medalChecker->getMedalLevel($medal, $value);
    }

    public function getMedalLevelName(int $level): string
    {
        return $this->medalChecker->getMedalLevelName($level);
    }

    public function getMedalLevelNames(): array
    {
        return $this->medalChecker->getMedalLevelNames();
    }

    public function translateMedalLevelFilter(string $level): string
    {
        return $this->medalChecker->translateMedalLevel($level);
    }

    public function medalDoubleValue(string $medal, int $value): int
    {
        return $this->medalChecker->getDoubleValue($medal, $value);
    }

    public function getBadgePath(string $medal, int $level, int $size = 0, string $postFix = '.png'): string
    {
        return $this->medalChecker->getBadgePath($medal, $level, $size, $postFix);
    }

    public function getChallengePath(string $medal, int $level): string
    {
        return $this->medalChecker->getChallengePath($medal, $level);
    }

    public function formatIntlDate(\DateTime $date)
    {
        return $this->intlDateHelper->format($date);
    }

    public function intDateShort(\DateTime $dateTime): string
    {
        return $this->intlDateHelper->formatShort($dateTime);
    }

    public function getBadgeData(string $group, string $badge, $value): BadgeData
    {
        switch ($group) {
            case 'Anomaly':
                $code = 'Anomaly_'.$badge;
                break;
            case 'Event':
                if ('AvenirShard' === $badge) {
                    $code = 'UniqueBadge_AvenirShardChallenge';
                } else {
                    $code = 'EventBadge_'.$badge.'_'.$value;
                }
                break;
            case 'Annual':
                $tier = $this->getMedalLevelName($value);
                $code = 'Badge_'.$badge.'_'.$tier;
                break;
            default:
                throw new \UnexpectedValueException('Unknown group: '.$group);
        }

        return $this->medalChecker->getBadgeData($code);
    }

    public function getPhpVersion()
    {
        return PHP_VERSION;
    }

    public function stripGmail(string $string)
    {
        return str_replace('@gmail.com', '', $string);
    }
}
