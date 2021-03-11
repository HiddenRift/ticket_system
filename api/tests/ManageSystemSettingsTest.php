<?php

namespace SupportTicketManager;
require_once __DIR__ . '/../resources/ManageSystemSettings.php';

use PHPUnit\Framework\TestCase;

class ManageSystemSettingsTest extends TestCase
{

    public function test__construct()
    {

    }

    public function testBulkInsertUsers()
    {

    }

    public function testSetEmailAccountDetails()
    {

    }

    public function testSetDefaultEmailPreferences()
    {

    }

    public function testGetDefaultEmailPreferences()
    {
        $instance = new ManageSystemSettings();
        $result = $instance->getEmailAccountDetails();
        print_r($result);
    }
}
