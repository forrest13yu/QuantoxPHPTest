<?php

require_once 'router.php';

router('GET', '/', function() {
    echo '<a href="users">List users</a><br>';
});

router('GET', '/users', function() {
    echo '<a href="users/1000">Show user: 1000</a>';
});

router('GET', '/users/(?<id>\d+)', function($params) {
    echo "You selected User-ID: ";
    var_dump($params);
});

router('POST', '/users', function() {
    header('Content-Type: application/json');
    $json = json_decode(file_get_contents('php://input'), true);
    echo json_encode(['result' => 1]);
});

header("HTTP/1.0 404 Not Found");
echo '404 Not Found';
