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
                )(?<!asm|auto|bool|break|casecatch|char|class|const|const_cast|continue|default|delete|do|double|dynamic_cast|else|enum|explicit|export|extern|false|float|for|friend|goto|inline|int|long|mutable|namespace|new|operator|private|protected|public|register|reinterpret_cast|return|short|signed|sizeof|static|static_cast|short|short|signed|sizeof|static|static_cast|struct|switch|template|this|throw|typedef|true|try|typeid|typename|union|voidunion|using|virtual|void)
                \s*?[,;]\s*?
            )+?(?<=;)
        )
    )(?: \s*?|\c*?)
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
        'pos' => iconv_strlen(end($arr))
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