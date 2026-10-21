<?php

namespace SteveLacey\UiAvatars;

class Initials
{
    public static function generate(string $name, int $length = 2, bool $uppercase = true): string
    {
        $name = trim($name);
        if ($uppercase) {
            $name = mb_strtoupper($name);
        }
        $name = preg_replace('/[!@#$%^&*(),.?":{}|<>_]/u', '', $name);
        $name = trim(trim($name, '-'));
        $names = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);

        if (count($names) < $length) {
            $names = array_merge(...array_map(fn ($part) => explode('-', $part), $names));
        }

        if (count($names) === 1) {
            return mb_substr($name, 0, $length);
        }

        $initials = '';
        $assignedNames = 0;
        $start = 0;
        for ($index = 0; $index < $length; $index++) {
            $nameIndex = $index;
            if (($index === $length - 1 && $index > 0) || $index > count($names) - 1) {
                $nameIndex = count($names) - 1;
            }
            if ($assignedNames >= count($names)) {
                $start++;
            }
            $initials .= mb_substr($names[$nameIndex] ?? '', $start, 1);
            $assignedNames++;
        }

        return mb_substr($initials, 0, $length);
    }
}
