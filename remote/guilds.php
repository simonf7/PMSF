<?php
/**
 * Display the guilds each user belongs to
 */

if (!isset($_GET['secret']) || $_GET['secret'] !== 'hoOUkjsb*(5bKJGFKA385fg42') {
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden';
    exit(0);
}

/**
 * Connect to database
 */
$pdo = new \PDO(
    'mysql:host=10.169.0.224;port=3306;dbname=pogonorw_mad;charset=utf8',
    'pogonorw_mad',
    'dammit',
    []
);

/**
 * Query database
 */
$query = $pdo->query('SELECT user, discord_guilds, FROM_UNIXTIME(last_loggedin) AS last FROM users WHERE discord_guilds IS NOT NULL ORDER BY last_loggedin DESC');
$users = $query->fetchAll(PDO::FETCH_ASSOC);
if (!$users) {
    die('No users found!');
}
?>
<html>

<body>
    <table>
        <tr>
            <th>User</th>
            <th>Last used</th>
            <th>Discords</th>
        </tr>
        <?php
    foreach ($users as $user) {
        $guilds = json_decode($user['discord_guilds'], true); ?>
        <tr valign="top">
            <td>
                <?=$user['user']?>
            </td>
            <td>
                <?=$user['last']?>
            </td>
            <td>
                <ul><?php
                foreach ($guilds as $guild) {
                    ?>
                    <li>
                        <?=$guild['name'] . ' ('.$guild['id'].')'?>
                    </li>
                    <?php
                } ?>
                </ul>
            </td>
        </tr>
        <?php
    }
    ?>
    </table>
</body>

</html>