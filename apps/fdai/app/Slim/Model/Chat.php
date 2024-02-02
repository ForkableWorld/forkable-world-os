<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
use App\Logging\QMLog;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;
class Chat implements MessageComponentInterface {
	protected $clients;
	public function __construct(){
		$this->clients = new SplObjectStorage;
	}
	/**
	 * @param ConnectionInterface $conn
	 */
	public function onOpen(ConnectionInterface $conn){
		// Store the new connection to send messages to later
		$this->clients->attach($conn);
		QMLog::info("New connection! ({$conn->resourceId})\n");
	}
	/**
	 * @param ConnectionInterface $from
	 * @param string $msg
	 */
	public function onMessage(ConnectionInterface $from, $msg){
		$numRecv = count($this->clients) - 1;
		QMLog::info(sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n", $from->resourceId,
			$msg, $numRecv, $numRecv == 1 ? '' : 's'));
		foreach($this->clients as $client){
			if($from !== $client){
				// The sender is not the receiver, send to each client connected
				$client->send($msg);
			}
		}
	}
	/**
	 * @param ConnectionInterface $conn
	 */
	public function onClose(ConnectionInterface $conn){
		// The connection is closed, remove it, as we can no longer send it messages
		$this->clients->detach($conn);
		QMLog::info("Connection {$conn->resourceId} has disconnected\n");
	}
	/**
	 * @param ConnectionInterface $conn
	 * @param Exception $e
	 */
	public function onError(ConnectionInterface $conn, Exception $e){
		QMLog::info("An error has occurred: {$e->getMessage()}\n");
		$conn->close();
	}
}
