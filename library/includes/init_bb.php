<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}
if (version_compare(PHP_VERSION, '5.5', '<')) {
    die('TorrentPier requires PHP version 5.5+. Your PHP version ' . PHP_VERSION);
}
if (!defined('BB_SCRIPT')) {
    define('BB_SCRIPT', 'undefined');
}
if (!defined('BB_CFG_LOADED')) {
    trigger_error('File config.php not loaded', E_USER_ERROR);
}

// Define some basic configuration arrays
unset($stopwords, $synonyms_match, $synonyms_replace);
$userdata = $theme = $images = $lang = $nav_links = $bf = $attach_config = [];
$gen_simple_header = false;
$user = null;

// Obtain and encode user IP
$client_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
$user_ip = encode_ip($client_ip);
define('CLIENT_IP', $client_ip);
define('USER_IP', $user_ip);

/**
 * @param $contents
 * @return string
 */
function send_page($contents)
{
    return compress_output($contents);
}

/**
 * @param $contents
 * @return string
 */
function compress_output($contents)
{
    global $bb_cfg;

    if ($bb_cfg['gzip_compress'] && GZIP_OUTPUT_ALLOWED && !defined('NO_GZIP')) {
        if (UA_GZIP_SUPPORTED && strlen($contents) > 2000) {
            header('Content-Encoding: gzip');
            $contents = gzencode($contents, 1);
        }
    }

    return $contents;
}

// Start output buffering
if (!defined('IN_AJAX')) {
    ob_start('send_page');
}

// Cookie params
$c = $bb_cfg['cookie_prefix'];
define('COOKIE_DATA', $c . 'data');
define('COOKIE_FORUM', $c . 'f');
define('COOKIE_MARK', $c . 'mark_read');
define('COOKIE_TOPIC', $c . 't');
define('COOKIE_PM', $c . 'pm');
unset($c);

define('COOKIE_SESSION', 0);
define('COOKIE_EXPIRED', TIMENOW - 31536000);
define('COOKIE_PERSIST', TIMENOW + 31536000);

define('COOKIE_MAX_TRACKS', 90);

/**
 * @param $name
 * @param $val
 * @param int $lifetime
 * @param bool $httponly
 * @return bool
 */
function bb_setcookie($name, $val, $lifetime = COOKIE_PERSIST, $httponly = false)
{
    global $bb_cfg;
    return setcookie($name, $val, $lifetime, $bb_cfg['script_path'], $bb_cfg['cookie_domain'], $bb_cfg['cookie_secure'], $httponly);
}

// Debug options
if (DBG_USER) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
} else {
    unset($_COOKIE['explain']);
}

define('DELETED', -1);

// User Levels
define('USER', 0);
define('ADMIN', 1);
define('MOD', 2);
define('GROUP_MEMBER', 20);
define('CP_HOLDER', 25);
define('EXCLUDED_USERS', implode(',', [GUEST_UID, BOT_UID]));

// User related
define('USER_ACTIVATION_NONE', 0);
define('USER_ACTIVATION_SELF', 1);

// Group settings
define('GROUP_OPEN', 0);
define('GROUP_CLOSED', 1);
define('GROUP_HIDDEN', 2);

// Forum state
define('FORUM_UNLOCKED', 0);
define('FORUM_LOCKED', 1);

// Topic status
define('TOPIC_UNLOCKED', 0);
define('TOPIC_LOCKED', 1);
define('TOPIC_MOVED', 2);

define('TOPIC_WATCH_NOTIFIED', 1);
define('TOPIC_WATCH_UNNOTIFIED', 0);

// Topic types
define('POST_NORMAL', 0);
define('POST_STICKY', 1);
define('POST_ANNOUNCE', 2);

// Search types
define('SEARCH_TYPE_POST', 0);
define('SEARCH_TYPE_TRACKER', 1);

// Ajax error codes
define('E_AJAX_GENERAL_ERROR', 1000);
define('E_AJAX_NEED_LOGIN', 1001);

// Private messaging
define('PRIVMSGS_READ_MAIL', 0);
define('PRIVMSGS_NEW_MAIL', 1);
define('PRIVMSGS_SENT_MAIL', 2);
define('PRIVMSGS_SAVED_IN_MAIL', 3);
define('PRIVMSGS_SAVED_OUT_MAIL', 4);
define('PRIVMSGS_UNREAD_MAIL', 5);
define('HAVE_UNREAD_PM', 1);
define('HAVE_NEW_PM', 2);

define('USERNAME_MIN_LENGTH', 3);

// URL PARAMETERS (hardcoding allowed)
define('POST_CAT_URL', 'c');
define('POST_FORUM_URL', 'f');
define('POST_GROUPS_URL', 'g');
define('POST_POST_URL', 'p');
define('POST_TOPIC_URL', 't');
define('POST_USERS_URL', 'u');

// Download Modes
define('INLINE_LINK', 1);
define('PHYSICAL_LINK', 2);

// Categories
define('NONE_CAT', 0);
define('IMAGE_CAT', 1);

// Misc
define('ADMIN_MAX_ATTACHMENTS', 50);
define('THUMB_DIR', 'thumbs');
define('MODE_THUMBNAIL', 1);

// Quota Types
define('QUOTA_UPLOAD_LIMIT', 1);
define('QUOTA_PM_LIMIT', 2);

// Torrents
define('TOR_STATUS_NORMAL', 0);
define('TOR_STATUS_FROZEN', 1);

// Gender
define('MALE', 1);
define('FEMALE', 2);
define('NOGENDER', 0);

// Poll
# 1 - обычный опрос
define('POLL_FINISHED', 2);

// Group avatars
define('GROUP_AVATAR_MASK', 999000);

// Иконки статусов
$bb_cfg['tor_icons'] = [
    TOR_NOT_APPROVED => '<span class="tor-icon tor-not-approved">*</span>',
    TOR_CLOSED => '<span class="tor-icon tor-closed">x</span>',
    TOR_APPROVED => '<span class="tor-icon tor-approved">&radic;</span>',
    TOR_NEED_EDIT => '<span class="tor-icon tor-need-edit">?</span>',
    TOR_NO_DESC => '<span class="tor-icon tor-no-desc">!</span>',
    TOR_DUP => '<span class="tor-icon tor-dup">D</span>',
    TOR_CLOSED_CPHOLD => '<span class="tor-icon tor-closed-cp">&copy;</span>',
    TOR_CONSUMED => '<span class="tor-icon tor-consumed">&sum;</span>',
    TOR_DOUBTFUL => '<span class="tor-icon tor-approved">#</span>',
    TOR_CHECKING => '<span class="tor-icon tor-checking">%</span>',
    TOR_TMP => '<span class="tor-icon tor-dup">T</span>',
    TOR_PREMOD => '<span class="tor-icon tor-dup">&#8719;</span>',
];

// Запрет на скачивание
$bb_cfg['tor_frozen'] = [
    TOR_CHECKING => true,
    TOR_CLOSED => true,
    TOR_CLOSED_CPHOLD => true,
    TOR_CONSUMED => true,
    TOR_DUP => true,
    TOR_NO_DESC => true,
    TOR_PREMOD => true,
];

// Разрешение на скачку автором, если закрыто на скачивание.
$bb_cfg['tor_frozen_author_download'] = [
    TOR_CHECKING => true,
    TOR_NO_DESC => true,
    TOR_PREMOD => true,
];

// Запрет на редактирование головного сообщения
$bb_cfg['tor_cannot_edit'] = [
    TOR_CHECKING => true,
    TOR_CLOSED => true,
    TOR_CONSUMED => true,
    TOR_DUP => true,
];

// Запрет на создание новых раздач если стоит статус недооформлено/неоформлено/сомнительно
$bb_cfg['tor_cannot_new'] = [TOR_NEED_EDIT, TOR_NO_DESC, TOR_DOUBTFUL];

// Разрешение на ответ релизера, если раздача исправлена.
$bb_cfg['tor_reply'] = [TOR_NEED_EDIT, TOR_NO_DESC, TOR_DOUBTFUL];

// Если такой статус у релиза, то статистика раздачи будет скрыта
$bb_cfg['tor_no_tor_act'] = [
    TOR_CLOSED => true,
    TOR_DUP => true,
    TOR_CLOSED_CPHOLD => true,
    TOR_CONSUMED => true,
];

$dl_link_css = [
    DL_STATUS_RELEASER => 'genmed',
    DL_STATUS_WILL => 'dlWill',
    DL_STATUS_DOWN => 'leechmed',
    DL_STATUS_COMPLETE => 'seedmed',
    DL_STATUS_CANCEL => 'dlCancel',
];

$dl_status_css = [
    DL_STATUS_RELEASER => 'genmed',
    DL_STATUS_WILL => 'dlWill',
    DL_STATUS_DOWN => 'dlDown',
    DL_STATUS_COMPLETE => 'dlComplete',
    DL_STATUS_CANCEL => 'dlCancel',
];

// Table names
define('BUF_TOPIC_VIEW', 'buf_topic_view');
define('BUF_LAST_SEEDER', 'buf_last_seeder');
define('BB_ATTACH_CONFIG', 'bb_attachments_config');
define('BB_ATTACHMENTS_DESC', 'bb_attachments_desc');
define('BB_ATTACHMENTS', 'bb_attachments');
define('BB_AUTH_ACCESS_SNAP', 'bb_auth_access_snap');
define('BB_AUTH_ACCESS', 'bb_auth_access');
define('BB_BANLIST', 'bb_banlist');
define('BB_BT_DLSTATUS', 'bb_bt_dlstatus');
define('BB_BT_DLSTATUS_SNAP', 'bb_bt_dlstatus_snap');
define('BB_BT_LAST_TORSTAT', 'bb_bt_last_torstat');
define('BB_BT_LAST_USERSTAT', 'bb_bt_last_userstat');
define('BB_BT_TORHELP', 'bb_bt_torhelp');
define('BB_BT_TORSTAT', 'bb_bt_torstat');
define('BB_CATEGORIES', 'bb_categories');
define('BB_CONFIG', 'bb_config');
define('BB_CRON', 'bb_cron');
define('BB_DISALLOW', 'bb_disallow');
define('BB_EXTENSION_GROUPS', 'bb_extension_groups');
define('BB_EXTENSIONS', 'bb_extensions');
define('BB_FORUMS', 'bb_forums');
define('BB_GROUPS', 'bb_groups');
define('BB_LOG', 'bb_log');
define('BB_POLL_USERS', 'bb_poll_users');
define('BB_POLL_VOTES', 'bb_poll_votes');
define('BB_POSTS_SEARCH', 'bb_posts_search');
define('BB_POSTS', 'bb_posts');
define('BB_POSTS_TEXT', 'bb_posts_text');
define('BB_POSTS_HTML', 'bb_posts_html');
define('BB_PRIVMSGS', 'bb_privmsgs');
define('BB_PRIVMSGS_TEXT', 'bb_privmsgs_text');
define('BB_QUOTA_LIMITS', 'bb_quota_limits');
define('BB_QUOTA', 'bb_attach_quota');
define('BB_RANKS', 'bb_ranks');
define('BB_SEARCH_REBUILD', 'bb_search_rebuild');
define('BB_SEARCH', 'bb_search_results');
define('BB_SESSIONS', 'bb_sessions');
define('BB_SMILIES', 'bb_smilies');
define('BB_TOPIC_TPL', 'bb_topic_tpl');
define('BB_TOPICS', 'bb_topics');
define('BB_TOPICS_WATCH', 'bb_topics_watch');
define('BB_USER_GROUP', 'bb_user_group');
define('BB_USERS', 'bb_users');
define('BB_WORDS', 'bb_words');

define('TORRENT_EXT', 'torrent');

define('TOPIC_DL_TYPE_NORMAL', 0);
define('TOPIC_DL_TYPE_DL', 1);

define('SHOW_PEERS_COUNT', 1);
define('SHOW_PEERS_NAMES', 2);
define('SHOW_PEERS_FULL', 3);

define('SEARCH_ID_LENGTH', 12);
define('SID_LENGTH', 20);
define('LOGIN_KEY_LENGTH', 12);
define('USERNAME_MAX_LENGTH', 25);
define('USEREMAIL_MAX_LENGTH', 40);

define('PAGE_HEADER', INC_DIR . '/page_header.php');
define('PAGE_FOOTER', INC_DIR . '/page_footer.php');

define('CAT_URL', 'index.php?c=');
define('DOWNLOAD_URL', 'dl.php?id=');
define('FORUM_URL', 'viewforum.php?f=');
define('GROUP_URL', 'group.php?g=');
define('LOGIN_URL', $bb_cfg['login_url']);
define('MODCP_URL', 'modcp.php?f=');
define('PM_URL', $bb_cfg['pm_url']);
define('POST_URL', 'viewtopic.php?p=');
define('POSTING_URL', $bb_cfg['posting_url']);
define('PROFILE_URL', 'profile.php?mode=viewprofile&amp;u=');
define('BONUS_URL', 'profile.php?mode=bonus');
define('TOPIC_URL', 'viewtopic.php?t=');

define('USER_AGENT', strtolower($_SERVER['HTTP_USER_AGENT']));

define('HTML_SELECT_MAX_LENGTH', 60);
define('HTML_WBR_LENGTH', 12);

define('HTML_CHECKED', ' checked="checked" ');
define('HTML_DISABLED', ' disabled="disabled" ');
define('HTML_READONLY', ' readonly="readonly" ');
define('HTML_SELECTED', ' selected="selected" ');

define('HTML_SF_SPACER', '&nbsp;|-&nbsp;');

// $GPC
define('KEY_NAME', 0);   // position in $GPC['xxx']
define('DEF_VAL', 1);
define('GPC_TYPE', 2);

define('GET', 1);
define('POST', 2);
define('COOKIE', 3);
define('REQUEST', 4);
define('CHBOX', 5);
define('SELECT', 6);

if (!empty($banned_user_agents)) {
    foreach ($banned_user_agents as $agent) {
        if (false !== strpos(USER_AGENT, $agent)) {
            $filename = 'Download files by using browser';
            $output = '@';
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            die($output);
        }
    }
}

// Functions
function send_no_cache_headers()
{
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
}

/**
 * @param string $output
 */
function bb_exit($output = '')
{
    if ($output) {
        echo $output;
    }
    exit;
}

/**
 * @param $var
 * @param string $title
 * @param bool $print
 * @return string
 */
function prn_r($var, $title = '', $print = true)
{
    $r = '<pre>' . ($title ? "<b>$title</b>\n\n" : '') . htmlspecialchars(print_r($var, true)) . '</pre>';
    if ($print) {
        echo $r;
    }
    return $r;
}

/**
 * @param $txt
 * @param bool $double_encode
 * @param int $quote_style
 * @param string $charset
 * @return string
 */
function htmlCHR($txt, $double_encode = false, $quote_style = ENT_QUOTES, $charset = 'UTF-8')
{
    return (string)htmlspecialchars($txt, $quote_style, $charset, $double_encode);
}

/**
 * @param $txt
 * @param int $quote_style
 * @param string $charset
 * @return string
 */
function html_ent_decode($txt, $quote_style = ENT_QUOTES, $charset = 'UTF-8')
{
    return (string)html_entity_decode($txt, $quote_style, $charset);
}

/**
 * @param string $path
 * @return string
 */
function make_url($path = '')
{
    return FULL_URL . preg_replace('#^\/?(.*?)\/?$#', '\1', $path);
}

require INC_DIR . '/functions.php';
require INC_DIR . '/sessions.php';

$bb_cfg = array_merge(bb_get_config(BB_CONFIG), $bb_cfg);

$user = new TorrentPier\Legacy\Common\User();
$userdata =& $user->data;

if (DBG_USER) {
    require INC_DIR . '/functions_dev.php';
}

$html = new TorrentPier\Legacy\Common\Html();
$log_action = new TorrentPier\Legacy\LogAction();

// TODO temporarily 'cat_forums' always enqueued
$datastore->enqueue(array('cat_forums'));

// Дата старта вашего проекта
if (!$bb_cfg['board_startdate']) {
    bb_update_config(array('board_startdate' => TIMENOW));
    DB()->query("UPDATE " . BB_USERS . " SET user_regdate = " . TIMENOW . " WHERE user_id IN(2, " . EXCLUDED_USERS_CSV . ")");
}

// Cron
if ((empty($_POST) && !defined('IN_ADMIN') && !defined('IN_AJAX') && !file_exists(CRON_RUNNING) && ($bb_cfg['cron_enabled'] || defined('START_CRON'))) || defined('FORCE_CRON')) {
    if (TIMENOW - $bb_cfg['cron_last_check'] > $bb_cfg['cron_check_interval']) {
        // Update cron_last_check
        bb_update_config(array('cron_last_check' => (TIMENOW + 10)));

        define('CRON_LOG_ENABLED', true);  // global ON/OFF
        define('CRON_FORCE_LOG', false); // always log regardless of job settings

        define('CRON_DIR', INC_DIR . '/cron/');
        define('CRON_JOB_DIR', CRON_DIR . 'jobs/');
        define('CRON_LOG_DIR', 'cron'); // inside LOG_DIR
        define('CRON_LOG_FILE', 'cron');  // without ext

        bb_log(date('H:i:s - ') . getmypid() . ' -x-- DB-LOCK try' . LOG_LF, CRON_LOG_DIR . '/cron_check');

        if (DB()->get_lock('cron', 1)) {
            bb_log(date('H:i:s - ') . getmypid() . ' --x- DB-LOCK OBTAINED !!!!!!!!!!!!!!!!!' . LOG_LF, CRON_LOG_DIR . '/cron_check');

            sleep(2);
            require(CRON_DIR . 'cron_init.php');

            DB()->release_lock('cron');
        }
    }
}

// Exit if board is disabled via ON/OFF trigger or by admin
if (($bb_cfg['board_disable'] || file_exists(BB_DISABLED)) && !defined('IN_ADMIN') && !defined('IN_AJAX') && !defined('IN_LOGIN')) {
    header('HTTP/1.0 503 Service Unavailable');
    if ($bb_cfg['board_disable']) {
        // admin lock
        send_no_cache_headers();
        bb_die('BOARD_DISABLE');
    } elseif (file_exists(BB_DISABLED)) {
        // trigger lock
        cron_release_deadlock();
        send_no_cache_headers();
        bb_die('BOARD_DISABLE_CRON');
    }
}

/**
 * Снятие блокировки крона
 */
function cron_release_deadlock()
{
    if (file_exists(CRON_RUNNING)) {
        if (TIMENOW - filemtime(CRON_RUNNING) > 2400) {
            cron_enable_board();
            cron_release_file_lock();
        }
    }
}

/**
 * Блокировка крона
 */
function cron_release_file_lock()
{
    rename(CRON_RUNNING, CRON_ALLOWED);
    cron_touch_lock_file(CRON_ALLOWED);
}

/**
 * @param $lock_file
 */
function cron_touch_lock_file($lock_file)
{
    file_write(make_rand_str(20), $lock_file, 0, true, true);
}

/**
 * Включение форума (при блокировке крона)
 */
function cron_enable_board()
{
    if (file_exists(BB_DISABLED)) {
        rename(BB_DISABLED, BB_ENABLED);
    }
}

/**
 * Отключение форума (при блокировке крона)
 */
function cron_disable_board()
{
    if (file_exists(BB_ENABLED)) {
        rename(BB_ENABLED, BB_DISABLED);
    }
}
