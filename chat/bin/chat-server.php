<?php
    use Ratchet\Server\IoServer;
    use Ratchet\Http\HttpServer;
    use Ratchet\WebSocket\WsServer;
    use MyApp\Chat;
    
    require dirname(__DIR__) . "/vendor/autoload.php";
    
    // Désactiver la détection automatique du PORT par ReactPHP
    putenv('PORT=9090');
    $_ENV['PORT'] = '9090';
    
    $server = IoServer::factory(
        new HttpServer (
            new WsServer (
                new Chat()
            )
        ),
        9090
    );
    
    $server->run();
?>
