<?php

namespace Tests\Unit;

use App\Services\Finance\AccountingService;
use Tests\TestCase;

class AccountingServiceTest extends TestCase
{
    public function test_fails_on_unbalanced_entry()
    {
        $service = new AccountingService();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Journal entry is not balanced");

        $service->postEntry([], [
            ['account_uuid' => 'acc-1', 'debit' => 100, 'credit' => 0],
            ['account_uuid' => 'acc-2', 'debit' => 0, 'credit' => 50] // Unbalanced
        ]);
    }
}
