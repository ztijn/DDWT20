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

/* Run the router */
$router->run();
