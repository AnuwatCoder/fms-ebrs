<?php

use App\Services\Authentication\AuthentikAccessService;

it('allows staff and professors from faculty 11', function (int|string $facultyId, string $accountType) {
    $access = authentikAccessService();

    expect($access->allows([
        'faculty_id' => $facultyId,
        'account_type' => $accountType,
    ]))->toBeTrue();
})->with([
    'staff with integer faculty id' => [11, 'Staff'],
    'professor with string faculty id' => ['11', 'Professor'],
    'case-insensitive account type' => [11, 'professor'],
]);

it('denies claims outside the allowed faculty and account types', function (array $claims) {
    $access = authentikAccessService();

    expect($access->allows($claims))->toBeFalse();
})->with([
    'different faculty' => [['faculty_id' => 12, 'account_type' => 'Staff']],
    'student account' => [['faculty_id' => 11, 'account_type' => 'Student']],
    'missing faculty' => [['account_type' => 'Professor']],
    'missing account type' => [['faculty_id' => 11]],
    'malformed faculty' => [['faculty_id' => '11abc', 'account_type' => 'Staff']],
    'array claim values' => [['faculty_id' => [11], 'account_type' => ['Staff']]],
]);

function authentikAccessService(): AuthentikAccessService
{
    return new AuthentikAccessService(
        allowedFacultyIds: [11],
        allowedAccountTypes: ['Staff', 'Professor'],
    );
}
