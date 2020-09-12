<?php

namespace App\Tests\Utils;

use App\Utils\FormValidation;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function testIsValidPhone()
    {
        $phone = "05557106054";
        $phone2 = "5557106054";
        $phone3 = "01557106054";

        $this->assertEquals(FormValidation::isValidPhone($phone), true);
        $this->assertEquals(FormValidation::isValidPhone($phone2), false);
        $this->assertEquals(FormValidation::isValidPhone($phone3), false);
    }

    public function testIsValidEmail()
    {
        $email = "admin@admin.com";
        $email2 = "admin@a.com";
        $email3 = "admin@.co";
        $email4 = "a@.com";
        $email5 = "a.com";
        $email6 = "a@com";

        $this->assertEquals(FormValidation::isValidEmail($email), true);
        $this->assertEquals(FormValidation::isValidEmail($email2), true);
        $this->assertEquals(FormValidation::isValidEmail($email3), false);
        $this->assertEquals(FormValidation::isValidEmail($email4), false);
        $this->assertEquals(FormValidation::isValidEmail($email5), false);
        $this->assertEquals(FormValidation::isValidEmail($email6), false);
    }
}
