<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $userConnections;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        echo "WebSocket server running...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        parse_str($conn->httpRequest->getUri()->getQuery(), $query);
        if(!isset($query['user_id'])){
            $conn->close();
            return;
        }

        $user_id = intval($query['user_id']);
        $conn->user_id = $user_id;

        $this->clients->attach($conn);
        $this->userConnections[$user_id] = $conn;

        echo "User $user_id connected\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg,true);
        if(!$data) return;

        $id_nhan = intval($data['to_id'] ?? 0);
        $noi_dung = trim($data['message'] ?? '');
        $id_gui = $from->user_id;

        if($id_nhan && $noi_dung) {
            $msgData = json_encode(['from_id'=>$id_gui,'to_id'=>$id_nhan,'message'=>$noi_dung]);

            // Gửi tới người nhận nếu đang online
            if(isset($this->userConnections[$id_nhan])){
                $this->userConnections[$id_nhan]->send($msgData);
            }

            // Gửi lại cho người gửi
            $from->send($msgData);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        if(isset($conn->user_id) && isset($this->userConnections[$conn->user_id])){
            unset($this->userConnections[$conn->user_id]);
        }
        echo "User disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: ".$e->getMessage()."\n";
        $conn->close();
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(new WsServer(new ChatServer())),
    8080
);

$server->run();
