<?php

namespace App\Utils;

class FormValidation
{
    public static function isValidPhone($phone){
        if (strlen($phone) == 11 && substr($phone,0, 2) == "05"){
            return true;
        }

        return false;
    }

    public static function isValidEmail($email){
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }
}