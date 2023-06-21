<?php

namespace App;

use SebastianBergmann\Type\MixedType;
use Valitron\Validator;

class UrlChecker
{
    public function valudateUrl($url)
    {
        $errors = [];
        $v = new Validator($url);
        $v->rule('required', 'name');
        if (!$v->rule('required', 'name')->validate()) {
            $errors = ['name' => 'URL must not be empty'];
       } elseif (!$v->rule('url', 'name')->rule('lengthMax', 'name', 255)->validate()) {
           $errors = ['name' => 'Length must be max 255'];
       } else {
           $errors = [];
       }
       return $errors;
    }
}