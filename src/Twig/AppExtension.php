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

/**
 * Class AppExtension
 */
class AppExtension
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
        #[Autowire(env: 'DEFAULT_TIMEZONE')] private readonly string $defaultTimeZone
    ) {
    }

    /**
     * Convert object to array for Twig usage..
     *
     * @return array<string, string|int>
     */
    #[\Twig\Attribute\AsTwigFilter(name: 'cast_to_array')]
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

    #[\Twig\Attribute\AsTwigFilter(name: 'medalLevel')]
    public function medalLevelFilter(int $level): string
    {
        return $this->medalChecker->getLevelName($level);
    }

    #[\Twig\Attribute\AsTwigFilter(name: 'medalDesc')]
    #[\Twig\Attribute\AsTwigFilter(name: 'stripTitle')]
    public function medalDescFilter(string $medal): string
    {
        return $this->medalChecker->getDescription($medal);
    }

    /**
     * @param array<string> $roles
     */
    #[\Twig\Attribute\AsTwigFilter(name: 'displayRoles')]
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

    #[\Twig\Attribute\AsTwigFilter(name: 'ucfirst')]
    public function displayUcFirst(string $string): string
    {
        return ucfirst($string);
    }

    /**
     * Transforms the given Markdown content into HTML content.
     */
    #[\Twig\Attribute\AsTwigFilter(name: 'md2html', isSafe: ['html'])]
    public function markdownToHtml(string $content): string
    {
        return $this->markdownHelper->parse($content);
    }

    #[\Twig\Attribute\AsTwigFunction(name: 'medalValue')]
    public function getMedalValue(string $medal, int $level): int
    {
        return $this->medalChecker->getLevelValue($medal, $level);
    }

    #[\Twig\Attribute\AsTwigFunction(name: 'medalLevel')]
    public function getMedalLevel(string $medal, int $value): int
    {
        return $this->medalChecker->getMedalLevel($medal, $value);
    }

    #[\Twig\Attribute\AsTwigFilter(name: 'medalLevelName')]
    public function getMedalLevelName(int $level): string
    {
        return $this->medalChecker->getMedalLevelName($level);
    }

    /**
     * @return array<int, string>
     */
    #[\Twig\Attribute\AsTwigFunction(name: 'medalLevelNames')]
    public function getMedalLevelNames(): array
    {
        return $this->medalChecker->getMedalLevelNames();
    }

    #[\Twig\Attribute\AsTwigFilter(name: 'translateMedalLevel')]
    public function translateMedalLevelFilter(int $level): string
    {
        return $this->medalChecker->translateMedalLevel($level);
    }

    #[\Twig\Attribute\AsTwigFunction(name: 'medalDoubleValue')]
    public function medalDoubleValue(string $medal, int $value): int
    {
        return $this->medalChecker->getDoubleValue($medal, $value);
    }

    #[\Twig\Attribute\AsTwigFunction(name: 'getBadgePath')]
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

    #[\Twig\Attribute\AsTwigFunction(name: 'getChallengePath')]
    public function getChallengePath(string $medal, int $level): string
    {
        return $this->medalChecker->getChallengePath($medal, $level);
    }

    #[\Twig\Attribute\AsTwigFilter(name: 'formatIntlDate')]
    public function formatIntlDate(DateTime $date): bool|string
    {
        return $this->intlDateHelper->format($date);
    }

    #[\Twig\Attribute\AsTwigFilter(name: 'intDateShort')]
    public function intlDateShort(DateTime $dateTime): string
    {
        return $this->intlDateHelper->formatShort($dateTime);
    }

    #[\Twig\Attribute\AsTwigFunction(name: 'intlDate')]
    public function intlDate(DateTime $date, string $format): string
    {
        return $this->intlDateHelper->formatCustom($date, $format);
    }

    #[\Twig\Attribute\AsTwigFunction(name: 'getBadgeName')]
    public function getBadgeName(
        string $group,
        string $badge,
        int|string $value
    ): string {
        return $this->medalChecker->getBadgeName($group, $badge, $value);
    }

    #[\Twig\Attribute\AsTwigFunction(name: 'getBadgeData')]
    public function getBadgeData(
        string $group,
        string $badge,
        int|string $value
    ): BadgeData {
        return $this->medalChecker->getBadgeData(
            $this->getBadgeName($group, $badge, $value)
        );
    }

    #[\Twig\Attribute\AsTwigFilter(name: 'stripGmail')]
    public function stripGmail(string $string): string
    {
        return str_replace('@gmail.com', '', $string);
    }

    #[\Twig\Attribute\AsTwigFilter(name: 'escape_bytecode')]
    public function escapeBytecode(string $string): string
    {
        return str_replace('%', "\\x", $string);
    }

    #[\Twig\Attribute\AsTwigFunction(name: 'defaultTimeZone')]
    public function getDefaultTimeZone(): string
    {
        return $this->defaultTimeZone;
    }
}
