<?php
/**
 * Model
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Connects to the database using PDO
 * @param string $host database host
 * @param string $db database name
 * @param string $user database user
 * @param string $pass database password
 * @return pdo object
 */
function connect_db($host, $db, $user, $pass){
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        echo sprintf("Failed to connect. %s",$e->getMessage());
    }
    return $pdo;
}

/**
 * Pretty Print Array
 * @param $input
 */
function p_print($input){
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Get array with all listed series from the database
 * @param object $pdo database object
 * @return array Associative array with all series
 */
function get_series($pdo){
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->fetchAll();

    return $series;
}

/**
 * Generates an array with serie information
 * @param object $pdo db object
 * @param int $serie_id id from the serie
 * @return mixed
 */
function get_serieinfo($pdo, $serie_id){
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$serie_id]);
    $serie_info = $stmt->fetch();
    $serie_info_exp = Array();

    /* Create array with htmlspecialchars */
    foreach ($serie_info as $key => $value){
        $serie_info_exp[$key] = htmlspecialchars($value);
    }
    return $serie_info_exp;
}

/**
 * Add serie to the database
 * @param object $pdo db object
 * @param array $serie_info post array
 * @return array with message feedback
 */
function add_serie($pdo, $serie_info){
    /* Check if all fields are set */
    if (
        empty($serie_info['Name']) or
        empty($serie_info['Creator']) or
        empty($serie_info['Seasons']) or
        empty($serie_info['Abstract'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    /* Check data type */
    if (!is_numeric($serie_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    /* Check if serie already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$serie_info['Name']]);
    $serie = $stmt->rowCount();
    if ($serie){
        return [
            'type' => 'danger',
            'message' => 'This series was already added.'
        ];
    }

    /* Add Serie */
    $stmt = $pdo->prepare("INSERT INTO series (name, creator, seasons, abstract) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $serie_info['Name'],
        $serie_info['Creator'],
        $serie_info['Seasons'],
        $serie_info['Abstract']
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted ==  1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' added to Series Overview.", $serie_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series was not added. Try it again.'
        ];
    }
}

/**
 * Updates a serie in the database using post array
 * @param object $pdo db object
 * @param array $serie_info post array
 * @return array
 */
function update_serie($pdo, $serie_info){
    /* Check if all fields are set */
    if (
        empty($serie_info['Name']) or
        empty($serie_info['Creator']) or
        empty($serie_info['Seasons']) or
        empty($serie_info['Abstract']) or
        empty($serie_info['serie_id'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    /* Check data type */
    if (!is_numeric($serie_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    /* Get current series name */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$serie_info['serie_id']]);
    $serie = $stmt->fetch();
    $current_name = $serie['name'];

    /* Check if serie already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$serie_info['Name']]);
    $serie = $stmt->fetch();
    if ($serie_info['Name'] == $serie['name'] and $serie['name'] != $current_name){
        return [
            'type' => 'danger',
            'message' => sprintf("The name of the series cannot be changed. %s already exists.", $serie_info['Name'])
        ];
    }

    /* Update Serie */
    $stmt = $pdo->prepare("UPDATE series SET name = ?, creator = ?, seasons = ?, abstract = ? WHERE id = ?");
    $stmt->execute([
        $serie_info['Name'],
        $serie_info['Creator'],
        $serie_info['Seasons'],
        $serie_info['Abstract'],
        $serie_info['serie_id']
    ]);
    $updated = $stmt->rowCount();
    if ($updated ==  1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was edited!", $serie_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'The series was not edited. No changes were detected'
        ];
    }
}

/**
 * Removes a series with a specific series-ID
 * @param object $pdo db object
 * @param int $serie_id id of the to be deleted series
 * @return array
 */
function remove_serie($pdo, $serie_id){
    /* Get series info */
    $serie_info = get_serieinfo($pdo, $serie_id);

    /* Delete Serie */
    $stmt = $pdo->prepare("DELETE FROM series WHERE id = ?");
    $stmt->execute([$serie_id]);
    $deleted = $stmt->rowCount();
    if ($deleted ==  1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was removed!", $serie_info['name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'An error occurred. The series was not removed.'
        ];
    }
}

/**
 * Count the number of series listed on Series Overview
 * @param PDO $pdo database object
 * @return mixed
 */
function count_series($pdo){
    /* Get series */
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->rowCount();
    return $series;
}

/**
 * Changes the HTTP Header to a given location
 * @param string $location location to be redirected to
 */
function redirect($location){
    header(sprintf('Location: %s', $location));
    die();
}
