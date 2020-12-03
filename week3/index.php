<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt20_week3', 'ddwt20', 'ddwt20');

/* Create Router instance */
$router = new \Bramus\Router\Router();

// Add routes here
$router->mount('/api', function() use ($router, $db) {
    http_content_type('application/json');

    /* GET for reading all series */
    $router->get('/series', function() use($db) {
        // Retrieve and output information
        $series = get_series($db);
        echo json_encode($series);
    });

    /* GET for reading individual series */
    $router->get('/series/(\d+)', function($id) use($db) {
        // Retrieve and output information
        $serie = get_serieinfo($db, $id);
        echo json_encode($serie);
    });

    /* DELETE individual series */
    $router->delete('/series/(\d+)', function($id) use($db) {
        // delete serie and output feedback
        $feedback = remove_serie($db, $id);
        echo json_encode($feedback);
    });

    /* POST for adding individual series */
    $router->post('/series', function() use($db) {
        // add serie and output feedback
        $feedback = add_serie($db, $_POST);
        echo json_encode($feedback);
    });

    /* PUT for updating individual series */
    $router->put('/series/(\d+)', function($id) use($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        $serie_info = $_PUT + ["serie_id" => $id];
        // update serie and output feedback
        $feedback = update_serie($db, $serie_info);
        echo json_encode($feedback);
    });


});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo "The path you entered was not found. Errorcode: 404";
});

/* Run the router */
$router->run();
