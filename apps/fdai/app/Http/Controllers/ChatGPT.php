<?php
namespace App\Http\Controllers;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Slim\Middleware\QMAuth;
use App\Storage\DB\Writable;
use Exception;
use Orhanerday\OpenAi\OpenAi;
class ChatGPT extends Controller
{
	const ROLE = "role";
	const CONTENT = "content";
	const USER = "user";
	const SYS = "system";
	const ASSISTANT = "assistant";
	public function get(){
		try {
			$contents = FileHelper::getContents('public/chat/index.php');
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		return response($contents);
	}
	private function table(){
		$db = Writable::db()->table('chat_history');
		return $db;
	}
	public function delete(){
		$db = $this->table();
		$id = QMAuth::id();
		$result = $db->where('user_id', $id)->delete();
		return response()->json(['status' => 'ok', 'result' => $result]);
	}
    public function post(){
	    // Create a new SQLite database connection
	    $db = $this->table();
	    // Get the user ID from the request data
	    $user_id = QMAuth::id();
	    // Prepare and execute a SELECT statement to retrieve the chat history data
	    $stmt = $db->where('user_id', $user_id)
	        ->orderBy('id', 'asc');
	    $chat_history = $stmt->get();
	    // Convert the chat history array to JSON and send it as the HTTP response body
	    return response()->json($chat_history);
    }
	public function sendMessage(){
		$id = QMAuth::id();
		$msg = $_POST['msg'];
		$db = $this->table();
		// Prepare the INSERT statement
		$id = $db->insertGetId(['user_id' => $id, 'human' => $msg]);
		$data = ["id" => $id];
		return json_encode($data);
	}
	/**
	 * @throws Exception
	 */
	public function eventStream(){
		return response()->stream(function (){
			$open_ai_key = getenv('OPENAI_API_KEY');
			$open_ai = new OpenAi($open_ai_key);
			// Open the SQLite database
			$db = $this->table();
			$chat_history_id = $_GET['chat_history_id'];
			$id = $_GET['id'];
			// Retrieve the data in ascending order by the id column
			$results = $db->get();
			$history[] = [
				self::ROLE => self::SYS,
				self::CONTENT => "You are a helpful assistant."
			];
			foreach($results as $row){
				$row = (array) $row;
				$history[] = [
					self::ROLE => self::USER,
					self::CONTENT => $row['human']
				];
				$history[] = [
					self::ROLE => self::ASSISTANT,
					self::CONTENT => $row['ai']
				];
			}
			// Prepare a SELECT statement to retrieve the 'human' field of the row with ID 6
			$stmt = $db->where('id', $chat_history_id)->select('human');
			$msg = $db->pluck('human')->first();
			$history[] = [
				self::ROLE => self::USER,
				self::CONTENT => $msg
			];
			$opts = [
				'model' => 'gpt-3.5-turbo',
				'messages' => $history,
				'temperature' => 1.0,
				'max_tokens' => 100,
				'frequency_penalty' => 0,
				'presence_penalty' => 0,
				'stream' => true
			];
			$txt = "";
			$complete = $open_ai->chat($opts, function($curl_info, $data) use (&$txt){
				if($obj = json_decode($data) and $obj->error->message != ""){
					error_log(json_encode($obj->error->message));
				} else{
					echo $data;
					$clean = str_replace("data: ", "", $data);
					$arr = json_decode($clean, true);
					if($data != "data: [DONE]\n\n" and isset($arr["choices"][0]["delta"]["content"])){
						$txt .= $arr["choices"][0]["delta"]["content"];
					}
				}
				echo PHP_EOL;
				ob_flush();
				flush();
				return strlen($data);
			});
			// Prepare the UPDATE statement
			$stmt = $db->where('id', $chat_history_id)
			           ->update(['ai' => $txt]);
			return $txt;
		}, 200, [
			'Cache-Control' => 'no-cache',
			'Content-Type' => 'text/event-stream',
		]);
	}
}
