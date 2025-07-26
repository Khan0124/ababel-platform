<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Admin;
use App\Models\Lab;
use App\Models\Patient;

class ModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Set up test database connection
        // This would typically use a test database
    }
    
    public function testAdminCreation()
    {
        $admin = new Admin();
        $admin->name = 'Test Admin';
        $admin->email = 'test@admin.com';
        $admin->password = 'password123';
        $admin->role = 'admin';
        
        $this->assertEquals('Test Admin', $admin->name);
        $this->assertEquals('test@admin.com', $admin->email);
        $this->assertEquals('admin', $admin->role);
    }
    
    public function testPatientAgeCalculation()
    {
        $patient = new Patient();
        $patient->date_of_birth = '1990-01-01';
        
        $age = $patient->getAge();
        $this->assertIsInt($age);
        $this->assertGreaterThan(30, $age);
    }
    
    public function testLabSubscriptionStatus()
    {
        $lab = new Lab();
        $lab->subscription_end = date('Y-m-d', strtotime('+30 days'));
        
        $this->assertTrue($lab->isSubscriptionActive());
        
        $lab->subscription_end = date('Y-m-d', strtotime('-1 day'));
        $this->assertFalse($lab->isSubscriptionActive());
    }
}