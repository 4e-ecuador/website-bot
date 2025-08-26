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
use DateTime;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use UnexpectedValueException;

/**
 * Class AppExtension
 */
class AppExtension extends AbstractExtension
{
    /**
     * @var array<string, string>
     */
    public array $roleFilters
        = [
            'ROLE_AGENT'       => 'Agent',
            'ROLE_INTRO_AGENT' => 'Intro Agent',
            'ROLE_EDITOR'      => 'Editor',
            'ROLE_ADMIN'       => 'Admin',
        ];

    public function __construct(
        private readonly MedalChecker $medalChecker,
        private readonly MarkdownHelper $markdownHelper,
        private readonly IntlDateHelper $intlDateHelper,
        #[Autowire('%env(DEFAULT_TIMEZONE)%')] private readonly string $defaultTimeZone
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('cast_to_array', $this->objectFilter(...)),
            new TwigFilter('medalLevel', $this->medalLevelFilter(...)),
            new TwigFilter('medalLevelName', $this->getMedalLevelName(...)),
            new TwigFilter(
                'translateMedalLevel', $this->translateMedalLevelFilter(...)
            ),
            new TwigFilter('medalDesc', $this->medalDescFilter(...)),
            new TwigFilter('stripGmail', $this->stripGmail(...)),
            new TwigFilter('displayRoles', $this->displayRolesFilter(...)),
            new TwigFilter('ucfirst', $this->displayUcFirst(...)),
            new TwigFilter('formatIntlDate', $this->formatIntlDate(...)),
            new TwigFilter('intDateShort', $this->intlDateShort(...)),
            new TwigFilter(
                'md2html', $this->markdownToHtml(...), ['is_safe' => ['html']]
            ),
            new TwigFilter('stripTitle', $this->medalDescFilter(...)),
            new TwigFilter('escape_bytecode', $this->escapeBytecode(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('medalValue', $this->getMedalValue(...)),
            new TwigFunction('medalLevel', $this->getMedalLevel(...)),
            new TwigFunction('medalLevelNames', $this->getMedalLevelNames(...)),
            new TwigFunction('medalDoubleValue', $this->medalDoubleValue(...)),
            new TwigFunction('getBadgePath', $this->getBadgePath(...)),
            new TwigFunction('getChallengePath', $this->getChallengePath(...)),
            new TwigFunction('getBadgeData', $this->getBadgeData(...)),
            new TwigFunction('getBadgeName', $this->getBadgeName(...)),
            new TwigFunction('intlDate', $this->intlDate(...)),
            new TwigFunction('defaultTimeZone', $this->getDefaultTimeZone(...)),
        ];
    }

    /**
     * Convert object to array for Twig usage..
     *
     * @return array<string, string|int>
     */
    public function objectFilter(object $classObject): array
    {
        $array = (array)$classObject;
        $response = [];

        $className = $classObject::class;

        foreach ($array as $k => $v) {
            $response[trim(str_replace($className, '', $k))] = $v;
        }

        return $response;
    }

    public function medalLevelFilter(int $level): string
    {
        return $this->medalChecker->getLevelName($level);
    }

    public function medalDescFilter(string $medal): string
    {
        return $this->medalChecker->getDescription($medal);
    }

    /**
     * @param array<string> $roles
     */
    public function displayRolesFilter(array $roles): string
    {
        $roles = array_diff($roles, ['ROLE_USER']);

        $displayRoles = [];

        foreach ($roles as $role) {
            $displayRoles[] = array_key_exists($role, $this->roleFilters)
                ? $this->roleFilters[$role] : $role;
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

    /**
     * @return array<int, string>
     */
    public function getMedalLevelNames(): array
    {
        return $this->medalChecker->getMedalLevelNames();
    }

    public function translateMedalLevelFilter(int $level): string
    {
        return $this->medalChecker->translateMedalLevel($level);
    }

    public function medalDoubleValue(string $medal, int $value): int
    {
        return $this->medalChecker->getDoubleValue($medal, $value);
    }

    public function getBadgePath(
        string $medal,
        int $level,
        int $size = 0,
        string $postFix = '.png'
    ): string {
        return $this->medalChecker->getBadgePath(
            $medal,
            $level,
            $size,
            $postFix
        );
    }

    public function getChallengePath(string $medal, int $level): string
    {
        return $this->medalChecker->getChallengePath($medal, $level);
    }

    public function formatIntlDate(DateTime $date): bool|string
    {
        return $this->intlDateHelper->format($date);
    }

    public function intlDateShort(DateTime $dateTime): string
    {
        return $this->intlDateHelper->formatShort($dateTime);
    }

    public function intlDate(DateTime $date, string $format): string
    {
        return $this->intlDateHelper->formatCustom($date, $format);
    }

    public function getBadgeName(
        string $group,
        string $badge,
        int|string $value
    ): string {
        return match ($group) {
            'anomaly' => 'anomaly_'.$badge.($value ? '_'.$value : ''),
            'event' => 'event_badge_'.$badge.($value ? '_'.$value : ''),
            'annual' => 'badge_'.$badge.'_'.$this->getMedalLevelName(
                    (int)$value
                ),
            default => throw new UnexpectedValueException(
                'Unknown group: '.$group
            ),
        };
    }

    public function getBadgeData(
        string $group,
        string $badge,
        int|string $value
    ): BadgeData {
        return $this->medalChecker->getBadgeData(
            $this->getBadgeName($group, $badge, $value)
        );
    }

    public function stripGmail(string $string): string
    {
        return str_replace('@gmail.com', '', $string);
    }

    public function escapeBytecode(string $string): string
    {
        return str_replace('%', "\\x", $string);
    }

    public function getDefaultTimeZone(): string
    {
        return $this->defaultTimeZone;
    }
}
