<?php

declare(strict_types=1);

namespace App\RepositoryInsights\Domain\ValueObject;

use Webmozart\Assert\Assert;
final readonly class ContributorIdentity
{
    private const string NO_EMAIL_DOMAIN = 'no-email.local';

    public string $value;

    private function __construct(string $value)
    {
        Assert::notEmpty($value);
        Assert::maxLength($value, 320);
        $this->value = $value;
    }

    public static function fromEmailAndName(?string $email, string $name): self
    {
        $name = trim($name);
        Assert::notEmpty($name, 'A commit must have an author name.');
        Assert::maxLength($name, 255);

        $email = $email !== null ? trim($email) : null;
        if ($email !== null && $email !== '') {
            return new self(mb_strtolower($email));
        }

        return new self(mb_strtolower($name) . '@' . self::NO_EMAIL_DOMAIN);
    }

    public static function fromValue(string $value): self
    {
        return new self($value);
    }

    public function isSynthetic(): bool
    {
        return str_ends_with($this->value, '@' . self::NO_EMAIL_DOMAIN);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
