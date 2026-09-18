<?php

namespace App\Services\Authentication;

use Illuminate\Support\Str;

class AuthentikAccessService
{
    /**
     * @param  list<int|string>  $allowedFacultyIds
     * @param  list<string>  $allowedAccountTypes
     */
    public function __construct(
        private array $allowedFacultyIds,
        private array $allowedAccountTypes,
    ) {}

    /** @param array<string, mixed> $claims */
    public function allows(array $claims): bool
    {
        $facultyId = $this->facultyId($claims['faculty_id'] ?? null);
        $accountType = $this->accountType($claims['account_type'] ?? null);

        if ($facultyId === null || $accountType === null) {
            return false;
        }

        $allowedFacultyIds = array_values(array_filter(
            array_map($this->facultyId(...), $this->allowedFacultyIds),
            fn (?int $value): bool => $value !== null,
        ));
        $allowedAccountTypes = array_values(array_filter(
            array_map($this->accountType(...), $this->allowedAccountTypes),
            fn (?string $value): bool => $value !== null,
        ));

        return in_array($facultyId, $allowedFacultyIds, true)
            && in_array($accountType, $allowedAccountTypes, true);
    }

    private function facultyId(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' && ctype_digit($value) ? (int) $value : null;
    }

    private function accountType(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Str::lower(trim($value));
    }
}
