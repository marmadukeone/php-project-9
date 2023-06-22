<?php

namespace App;

use SebastianBergmann\Type\MixedType;
use Valitron\Validator;

class UrlChecker
{
    public function valudateUrl(array $url)
    {
        $errors = [];
        $v = new Validator($url);
        $v->rule('required', 'name');
        if (!$v->rule('required', 'name')->validate()) {
            $errors = ['name' => 'Некорректный URL'];
        } elseif (!$v->rule('url', 'name')->rule('lengthMax', 'name', 255)->validate()) {
            $errors = ['name' => 'Некорректный URL'];
        } else {
            $errors = [];
        }
        return $errors;
    }
}
