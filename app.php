<?
use db\Db;

// настраиваем кодировку на utf8
ini_set('default_charset', 'UTF-8');
ini_set('mbstring.language', 'Russian');
ini_set('mbstring.http_input', 'pass');
ini_set('mbstring.http_output', 'pass');
ini_set('mbstring.internal_encoding', 'UTF-8');
mb_internal_encoding('UTF-8');

// пути поиска файлов приложения
define('ROOT_PATH', dirname(__FILE__).'/');
define('LIBS_PATH', ROOT_PATH.'libs/');
define('TEMPLATES_PATH', ROOT_PATH.'templates/');

// пути для инклюдов
set_include_path(
    get_include_path()
    .PATH_SEPARATOR.LIBS_PATH
    .PATH_SEPARATOR.TEMPLATES_PATH
    .PATH_SEPARATOR.ROOT_PATH
);

/**
 * Автоматическое подключение файла класса
 * @param string $className
 */
function __autoload($className) {
    // убиваем слеши и двоеточия для того чтобы избежать попыток подключения левых файлов, бэкслешы namespace заменяем на слешы (подкаталоги)
    $fileName = str_replace(['/', '..', '\\'], ['', '', '/'], $className).'.php';
    if ($fileName{1} != '/') require_once($fileName);
}

include('config.php');
require_once('functions.php');

startSession();

$db  = new Db($dbServer, $dbUser, $dbPassword, $dbName, $dbPort);
$tpl = new Templater();

if (!isset($_SESSION['author'])) {
    $_SESSION['author'] = '';
    $_SESSION['id']     = 0;
}

// обработка форм
if (isset($_POST['action'])) {
    // проверяем есть ли в сессии автор и не тот ли это автор что и был в сессии
    if (!$_SESSION['author'] || $_SESSION['author'] != $_POST['author']) {
        $_SESSION['author'] = filter($_POST['author']);
        if (empty($_SESSION['author'])) {
            exit($tpl->fetch('error.tpl', ['message' => 'Имя автора не может быть пустым']));
        }

        $authorName = $db->escape($_SESSION['author']);

        // забиваем в базу автора и сразу проверяем был ли он в базе, так гораздо меньше нагрузки на БД чем в варианте с выборкой для проверки
        $db->real_query("INSERT IGNORE INTO `authors` set name = $authorName");

        // если у нас в состоянии драйвера есть не нулевое количество затронутых полей значит инсерт отработал и у нас есть ид нового автора, иначе делаем выборку и сохраняем данные в сессию
        $_SESSION['id'] = ($db->affected_rows ? $db->insert_id : $db->fetch_one("select id from authors where name = $authorName"));
    }

    switch ($_POST['action']) {
        case 'add_theme':
            $db->insert('themes', ['name' => filter($_POST['name']), 'author' => $_SESSION['id']]);
            header('Location: /?theme='.$db->insert_id);
            exit;

        case 'add_message':
            $db->insert('messages', ['theme' => filter($_GET['theme'], 'int'), 'author' => $_SESSION['id'], 'text' => filter($_POST['text'])]);
            break;

        default:
    }
}

// дальше примитивный роутинг
if (empty($_GET['theme'])) {
    // сделал "в лоб" без денормализации, по хорошему тут мемкеш надо для кеширования списка тем с датами обновления и счетчиками сообщений, да и авторов тоже в кеш засунуть
    $themes = $db->fetch_all("
        select
            t.id,
            t.name,
            t.author as author,
            count(m.theme) as cnt,
            m.date as last_date,
            m.author as last_author
        from themes as t
          left join (SELECT theme, author, date FROM `messages` order by date desc) as m on t.id = m.theme
        group by t.id
    ", 'id');


    // собираем в кучу все ид авторов полученных из запроса, можно конечно и в в основной запрос запихать двойную выборку но так оно быстрей отработает
    // да и кеширование, если оно понадобиться в дальнейшем, проще будет прикрутить
    $authors = array_unique(array_filter(
        array_merge(
            array_column($themes, 'author'),
            array_column($themes, 'last_author')
        ))
    );
    if ($authors) {
        $authors = implode(', ', $authors);
        $authors = $db->fetch_column("select id, name, date from authors where id in ($authors)");
    }
    $authors[null] = 'автор удален';

    exit($tpl->fetch('themes.tpl', compact('themes', 'authors')));
}

$theme = $db->escape($_GET['theme']);

// тоже не совсем правильно, нужен кэш со счетчиками и разбить на подзапросы
$messages = $db->fetch_all("
    select
        m.date,
        m.text,
        a.name as author,
        a.date as author_reg,
        ac.cnt as author_cnt_messages
    from messages as m
      left join authors as a on m.author = a.id
      left join (select author, count(*) as cnt from messages as m1 group by m1.author) as ac on ac.author = m.author
    where m.theme = $theme
");
exit($tpl->fetch('messages.tpl', compact('messages')));

