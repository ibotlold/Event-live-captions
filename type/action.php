<?php
$servername = 'localhost';
$username = 'writer';
$password = 'taZzDDnANev3o6oT';
$dbname = 'texttranslation';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
	http_response_code(404);
	exit;
}

$action = !empty($_POST['action']) ? trim(htmlspecialchars($_POST['action'])) : NULL;
$status = !empty($_POST['status']) ? trim(htmlspecialchars($_POST['status'])) : NULL;
$data = !empty($_POST['data']) ? $_POST['data'] : NULL;
$eventId = !empty($_POST['eventId']) ? trim(htmlspecialchars($_POST['eventId'])) : NULL;
$result = array('status' => 4, 'action' => 'sendRequest', 'data' => NULL);
if (isset($action)) {
	if ($action == 'changeEventStatus') {
		$data = isset($data) ? $data : 0;
		$sql='UPDATE events SET status = '.$data.' WHERE events.id = "'.$eventId.'"';
		$query = mysqli_query($conn,$sql);
		if (!$query) {
			$result['status'] = 4;
			$result['data'] = 'Event with id '.$eventId.' not found';
		} else {
			$result['status'] = 2;
		}
	}
	if ($action == 'newMessage') {
		$timestamp = date_create();
		$result['status'] = 5;
		if ($data['system'] == 'true') {
			$system = 1;
		} else {
			$system = 0;
		}
		$sql = 'INSERT INTO message (id, event, timeStamp, text, system) VALUES (NULL,"'.$eventId.'", "'.date_format($timestamp, 'Uv').'","","'.$system.'")';
		$query = mysqli_query($conn,$sql);
		if ($query) {
			$result['status'] = 2;
			$result['data'] = date_format($timestamp, 'Uv');
		} else {
			$result['status'] = 4;
		}		
	}
	if ($action == 'editMessage') {
		$result['status'] = 5;
		$sql = 'UPDATE message SET text = "'.$data['text'].'" WHERE message.timeStamp = "'.$data['timeStamp'].'" AND message.event = "'.$eventId.'"';
		$query = mysqli_query($conn,$sql);
		if ($query) {
			$timestamp = date_create();
			$sql = 'UPDATE message SET mTimeStamp = "'.date_format($timestamp, 'Uv').'" WHERE message.timeStamp = "'.$data['timeStamp'].'" AND message.event = "'.$eventId.'"';
			$query = mysqli_query($conn,$sql);
			$result['status'] = 2;
		}
	}
	if ($action == 'deleteMessage') {
		$result['status'] = 5;
		//$sql = 'DELETE FROM message WHERE message.timeStamp = "'.$data['timeStamp'].'" AND message.event ="'.$eventId.'"';
		$sql = 'UPDATE message SET deleted = "1" WHERE message.timeStamp = "'.$data['timeStamp'].'" AND message.event ="'.$eventId.'"';
		$query = mysqli_query($conn,$sql);
		if ($query) {
			$timestamp = date_create();
			$sql = 'UPDATE message SET mTimeStamp = "'.date_format($timestamp, 'Uv').'" WHERE message.timeStamp = "'.$data['timeStamp'].'" AND message.event = "'.$eventId.'"';
			$query = mysqli_query($conn,$sql);
			$result['status'] = 2;
		}
	}
}
echo json_encode($result);
mysqli_close($conn);
?>