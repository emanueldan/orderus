<?
    define('WEB_ROOT', str_replace('\\', '/', substr(dirname(__FILE__), strlen(rtrim($_SERVER['DOCUMENT_ROOT'], '/\\')))).'/');
    define('SV_ROOT', $_SERVER['DOCUMENT_ROOT'].'/');
    define('URL_SERVER', ($_SERVER['HTTPS']=='on' ? 'https' : 'http').'://'.$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT']==80 || $_SERVER['SERVER_PORT']==443 ? '' : ':'.$_SERVER['SERVER_PORT']));
    define('URL', URL_SERVER.WEB_ROOT);

    $title = 'Orderus';
    $description = 'Orderus Game';
    $keywords = '';
    $image = '';
    
    include 'functions.php';
    include 'head.php';
    XGame::run();
    include 'footer.php';
?>