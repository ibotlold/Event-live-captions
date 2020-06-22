<?php
include_once('db.php');
# Status check:
# 1 - informational
# 2 - succesful
# 3 - redirect
# 4 - client error
# 5 - server error
$eventId = !empty($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
$pattern = '/(^(\w){7}$)/';
$status = 4;
/*
$message = 'Wrong event id';
$event = NULL;
if (preg_match($pattern, $eventId) == 1) {
	$status = 5;
	$message = 'Database connection error';
	$sql = 'SELECT title,status FROM events WHERE events.id = "'.$eventId.'"';
	$result	= mysqli_query($conn,$sql);
	if ($result) {
		$row = mysqli_fetch_array($result);
		$status = 5;
		$message = 'Event with id doesnt exist';
		if ($row) {
			if ($row['status'] > 0) {
				$status = 2;
				$event = array("id" => $eventId, "title" => $row['title'], "status" => $row['status']);
				$message = '';
			} else {
				$status = 4;
				$message = 'Event didnt started';
			}
		}
	}
}

$result = array("status" => $status, "message" => $message, "event" => $event);
echo json_encode($result);
mysqli_close($conn);*/

$action = !empty($_POST['action']) ? trim(htmlspecialchars($_POST['action'])) : NULL;
$status = !empty($_POST['status']) ? trim(htmlspecialchars($_POST['status'])) : NULL;
$data = !empty($_POST['data']) ? $_POST['data'] : NULL;
$eventId = !empty($_POST['eventId']) ? trim(htmlspecialchars($_POST['eventId'])) : NULL;
$result = array('status' => 4, 'action' => 'sendRequest', 'data' => NULL);
if (isset($action)) {
	if ($action == 'getCurrentState') {
		$sql = 'SELECT * FROM message WHERE message.event = "'.$eventId.'" AND message.deleted = 0';
		$query = mysqli_query($conn,$sql);
		$messages = NULL;
		if ($query) {
			$messages = array();
			while ($row = mysqli_fetch_array($query)) {
				array_push($messages, array('timeStamp' => $row['timeStamp'], 'text' => $row['text'], 'system' => $row['system']));
				//$messages[$row['timeStamp']] = array('text' => $row['text'], 'system' => $row['system']);
			}
		}
		$result['status'] = 2;
		$sql = 'SELECT title FROM events WHERE id="'.$eventId.'"';
		$query = mysqli_query($conn,$sql);
		$row = mysqli_fetch_array($query);
		$result['data'] = array('event' => array('title' => $row['title'],'id' => $eventId, 'mTimeStamp' => date_format(date_create(),'Uv')), 'messages' => $messages );
	}
	if ($action == 'listenUpdates') {
		$result['status'] = 5;
		$sql = 'SELECT * FROM message WHERE message.event = "'.$eventId.'" AND message.mTimeStamp > "'.$data['mTimeStamp'].'"';
		/*while ($query = mysqli_query($conn, $sql)) {
			usleep(40000);
		}
		if ($query) {
			$messages = array();
			while ($row = mysqli_fetch_array($query)) {
				$text = $row['text'];
				if ($row['deleted'] == 1) {
					$text = null;
				}
				array_push($messages, array('timeStamp' => $row['timeStamp'], 'text' => $text, 'system' => $row['system']));
				//$messages[$row['timeStamp']] = array('text' => $row['text'], 'system' => $row['system']);
			}
		}*/
		$k = 1;
		for ($i=0; $i <	37; $i++) {
			if (connection_aborted()) {
				exit();
			}
			$query = mysqli_query($conn, $sql);
			if ($query) {
				if (mysqli_num_rows($query)) {
					break;
				} else {
					$query = null;
				}
			}
			switch ($i) {
				case 5:
					$k = 2;
					break;
				case 8:
					$k = 3;
					break;
				case 10:
					$k = 4;
					break;
				case 12:
					$k = 5;
					break;
			}
			usleep($k*200000);
		}
		if ($query) {
			$result['status'] = 2;
			$messages = array();
			while ($row = mysqli_fetch_array($query)) {
				array_push($messages, array('timeStamp' => $row['timeStamp'], 'text' => $row['text'], 'system' => $row['system'], 'mTimeStamp' => $row['mTimeStamp'], 'deleted' => $row['deleted']));
				//$messages[$row['timeStamp']] = array('text' => $row['text'], 'system' => $row['system']);
			}
			$result['data'] = array('messages' => $messages );
		} else {
			$result['status'] = 1;
		}
	}
}
echo json_encode($result);
mysqli_close($conn);
?>