<?php

use App\Enums\BorrowRequestStatus;
use App\Enums\EquipmentStatus;
use App\Enums\ReturnStatus;

it('allows only declared borrow request transitions', function () {
    expect(BorrowRequestStatus::Draft->canTransitionTo(BorrowRequestStatus::Pending))->toBeTrue()
        ->and(BorrowRequestStatus::Pending->canTransitionTo(BorrowRequestStatus::Approved))->toBeTrue()
        ->and(BorrowRequestStatus::Approved->canTransitionTo(BorrowRequestStatus::Returned))->toBeFalse()
        ->and(BorrowRequestStatus::Returned->allowedTransitions())->toBeEmpty();
});

it('maps return outcomes to equipment statuses', function (ReturnStatus $returnStatus, EquipmentStatus $equipmentStatus) {
    expect($returnStatus->equipmentStatus())->toBe($equipmentStatus);
})->with([
    [ReturnStatus::Normal, EquipmentStatus::Available],
    [ReturnStatus::Damaged, EquipmentStatus::Damaged],
    [ReturnStatus::Lost, EquipmentStatus::Lost],
    [ReturnStatus::MaintenanceRequired, EquipmentStatus::Maintenance],
]);
