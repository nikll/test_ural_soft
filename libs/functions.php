<?

/**
 * Обертка над var_dump(), на вход получает любое количество параметров и передает их все в var_dump().
 * вывод результатов работы var_dump() перехватывается из выходного потока и возвращщается как результат работы функции _var_dump()
 *
 * @return string
 */
function _var_dump() {
    ob_start();
    call_user_func_array('var_dump', func_get_args());
    return ob_get_clean();
}

/**
 * эскейпит хтмл (фильтр от XSS)
 *
 * @param $str
 * @return string
 */
function trunc($str) {
    return htmlspecialchars($str, ENT_QUOTES);
}

/**
 * вырезает всю верстку скрипты и комментари оставляя один текст
 *
 * @param string $html
 * @return string
 */
function html2txt($html) {
    return trim(preg_replace(
            [
                '@<script[^>]*?>.*?</script>@si', // Strip out javascript
                '@<[/!]*?[^<>]*?>@si', // Strip out HTML tags
                '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
                '@<![\s\S]*?--[ \t\n\r]*>@' // Strip multi-line comments including CDATA
            ],
            '',
            $html
        ));
}

/**
 * фильтр
 *
 * @param string|int|float $val  переменная для фильтрации
 * @param string           $type тип фильтрации
 * @return bool|float|int|mixed|string
 */
function filter($val, $type = '') {
    switch ($type) {
        case 'int':
            return intval($val);

        case 'float':
            return floatval($val);

        case 'bool':
            return !!$val;

        case 'date':
            if ($val = strtotime($val)) return date('Y-m-d', $val);
            return false;

        case 'time':
            if ($val = strtotime($val)) return date('H:i:s', $val);
            return false;

        case 'timestamp':
        case 'datetime':
            if ($val = strtotime($val)) return date('Y-m-d H:i:s', $val);
            return false;

        case 'email':
        case 'mail':
            return preg_filter(
                '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+ \\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/',
                "\\0",
                $val
            );

        case 'nohtml':
            return html2txt($val);

        case 'url_name':
            return to_url($val);

        case 'html':
        case 'none':
        case 'unescaped':
            return $val;

        case 'str':
        case 'string':
        default:
            return trunc($val);
    }
}

/**
 * запуск сессии
 *
 * @return bool
 */
function startSession() {
    if (session_id()) return false;

    session_set_cookie_params(2592000, '/'); // куку столбим на месяц
    return session_start();
}

/**
 * уничтожение сессии
 */
function destroySession() {
    if (!session_id()) return false;

    // Если есть активная сессия, удаляем куки сессии, и уничтожаем сессию
    $_SESSION = [];
    setcookie(session_name(), session_id(), time() - 3600 * 24 * 7);
    session_unset();
    session_destroy();

    return true;
}
