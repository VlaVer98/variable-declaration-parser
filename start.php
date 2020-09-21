<?php
($str = file_get_contents('input.txt')) or die('Не найден файл input.txt');

$pattern = "
/
    (?: \s*?|\c*?)(?:
        (?<type_data>
            (?: 
                bool|wchar_t|char16_t|char32_t|float|double|int|signed|unsigned|short
                (:? 
                    \s+?int
                )??|long|char
            ) | 
            (?: 
                long\s+?
                (?:
                    double|int
                )
            ) | 
            (?: 
                long\s+?long
                (:? 
                    \s+?int
                )?
            ) | 
            (?: 
                (?:
                    signed|unsigned
                )\s*?
                (?: 
                    char|short(?:
                        \s+?int
                    )??|int
                )
            ) | 
            (?: 
                (?:
                    signed|unsigned
                )\s*?long
                (:? 
                    \s+?long
                )??
                (:? 
                    \s*?int
                )??
            )
        )
        \s+?
        (?<name_variables>
            (:? 
                \s*?
                (?<name_variable>
                    [a-zA-Z_][a-zA-Z0-9_]*?
                    (:?
                        \[\d*?\]
                    )*?
                )(?<![\s,]asm|[\s,]auto|[\s,]bool|[\s,]break|[\s,]casecatch|[\s,]char|[\s,]class|[\s,]const|[\s,]const_cast|[\s,]continue|[\s,]default|[\s,]delete|[\s,]do|[\s,]double|[\s,]dynamic_cast|[\s,]else|[\s,]enum|[\s,]explicit|[\s,]export|[\s,]extern|[\s,]false|[\s,]float|[\s,]for|[\s,]friend|[\s,]goto|[\s,]inline|[\s,]int|[\s,]long|[\s,]mutable|[\s,]namespace|[\s,]new|[\s,]operator|[\s,]private|[\s,]protected|[\s,]public|[\s,]register|[\s,]reinterpret_cast|[\s,]return|[\s,]short|[\s,]signed|[\s,]sizeof|[\s,]static|[\s,]static_cast|[\s,]short|[\s,]short|[\s,]signed|[\s,]sizeof|[\s,]static|[\s,]static_cast|[\s,]struct|[\s,]switch|[\s,]template|[\s,]this|[\s,]throw|[\s,]typedef|[\s,]true|[\s,]try|[\s,]typeid|[\s,]typename|[\s,]union|[\s,]voidunion|[\s,]using|[\s,]virtual|[\s,]void)
                \s*?[,;]\s*?
            )+?(?<=;)
        )
    )(?: \s*|\c*)
/x";

$out =  preg_split($pattern ,$str, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_OFFSET_CAPTURE);

if(empty($out)) {
    $repeatName = getRepeatName($pattern, $str);
    if($repeatName)
        print 'Дублирование имени переменной - ' . $repeatName['name'] . ', на строке ' . $repeatName['line'] . ', в позиции: ' . $repeatName['pos'];
    else
        print "Описание корректное";
} else {
    $err = getLineAndPosOfError($str, $out[0][1]);
    print 'Ошибка на строке: ' . $err['line'] . ', в позиции: ' . $err['pos'];
}

function getLineAndPosOfError($str, $posErr) {
    $str = mb_strimwidth($str, 0, $posErr);
    $arr = explode("\n", $str);
    return [
        'line' => count($arr),
        'pos' => iconv_strlen(end($arr)) ?: 1
    ];
}

function getRepeatName($pattern, $str) {
    preg_match_all($pattern, $str, $match, PREG_OFFSET_CAPTURE);
    foreach(array_reverse($match['name_variable']) as $v) {
        $count = 0;
        foreach($match['name_variable'] as $v2)
            if(strcmp($v2[0], $v[0])===0) $count++;
        if($count>=2)
            return getLineAndPosOfError($str, $v[1]) + ['name' => $v[0]];
    }
    return false;
}