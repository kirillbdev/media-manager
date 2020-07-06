<?php

namespace kirillbdev\MediaManager\Services;

class MediaManagerService
{
    private $charMap = [
        'А' => 'a', 'Б' => 'b', 'В' => 'v', 'Г' => 'g', 'Д' => 'd',
        'Е' => 'e', 'Ё' => 'e', 'Ж' => 'sh', 'З' => 'z', 'И' => 'i',
        'Й' => 'i', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n',
        'О' => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't',
        'У' => 'u', 'Ф' => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch',
        'Ш' => 'sh', 'Щ' => 'shch', 'Ъ' => '', 'Ы' => 'y', 'Ь' => '',
        'Э' => 'e', 'Ю' => 'yu', 'Я' => 'ya',

        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'e', 'ж' => 'sh', 'з' => 'z', 'и' => 'i',
        'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

        'і' => 'i', '&' => 'i',

        ' ' => '-', ',' => '', '.' => '', '?' => '',
        '!' => '', '+' => '', '(' => '', ')' => '', ':' => '',
        ';' => '', '*' => '', '%' => '', '$' => '', '#' => '',
        '/' => '-', '\\' => '-', '|' => '-'
    ];

    public function prepareFilename($name)
    {
        $info = pathinfo($name);

        $result = preg_replace('/\s{2,}/', ' ', trim($info['filename']));

        foreach ($this->charMap as $key => $value) {
            $result = str_replace($key, $value, $result);
        }

        if ( ! empty($info['extension'])) {
            return $result . '.' . $info['extension'];
        }

        return $result;
    }
}