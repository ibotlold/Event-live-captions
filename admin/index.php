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

$action = !empty($_GET['action']) ? trim(htmlspecialchars($_GET['action'])) : NULL;

if ($action == 'addEvent') {
	$eventTitle = !empty($_POST['title']) ? trim(htmlspecialchars($_POST['title'])) : NULL;
	if ($eventTitle) {
		$eventId = substr(hash('sha256',microtime(true).$eventTitle),0,7);
		$authCode = random_int(100000,999999);
		$sql = "INSERT INTO events (id, date, title, status, codeAuth) VALUES ('".$eventId."', '".date('Y-m-d H:i:s')."','".$eventTitle."', 0,'".$authCode."')";
		if (!mysqli_query($conn, $sql)) {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
		
	}
}

$eventId = !empty($_GET['id']) ? trim(htmlspecialchars($_GET['id'])) : NULL;
$pattern = '/(^(\w){7}$)/';
if ($eventId) {
	if (preg_match($pattern, $eventId) != 1) {
		http_response_code(404);
		exit;
	}
	$delete = !empty($_POST['delete']) ? trim(htmlspecialchars($_POST['delete'])) : NULL;
	if (isset($delete)) {
		$sql = 'DELETE FROM events WHERE events.id = "'.$eventId.'"';
		$result = mysqli_query($conn, $sql);
		if (!$result) {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
		$result = NULL;
	} 
	else {
		if ($action != 'newEvent') {
			$codeauth = !empty($_POST['codeauth']) ? trim(htmlspecialchars($_POST['codeauth'])) : NULL;
			if (isset($codeauth)) {
				$title = !empty($_POST['title']) ? trim(htmlspecialchars($_POST['title'])) : NULL;
				$status = !empty($_POST['status']) ? trim(htmlspecialchars($_POST['status'])) : 0;
				$sql = 'UPDATE events SET title = "'.$title.'", status = "'.$status.'", codeAuth = "'.$codeauth.'" WHERE events.id = "'.$eventId.'"';
				$result = mysqli_query($conn, $sql);
				if (!$result) {
					echo "Error: " . $sql . "<br>" . mysqli_error($conn);
				}
				$result = NULL;
				if ($status == 3) {
					$sql = 'DELETE FROM message WHERE message.event = "'.$eventId.'" AND message.text = ""';
					$result = mysqli_query($conn, $sql);
					if (!$result) {
						echo "Error: " . $sql . "<br>" . mysqli_error($conn);
					}
					$sql = 'DELETE FROM message WHERE message.event = "'.$eventId.'" AND message.deleted = 1';
					$result = mysqli_query($conn, $sql);
					if (!$result) {
						echo "Error: " . $sql . "<br>" . mysqli_error($conn);
					}
				}
			}
			$sql = 'SELECT id,DATE_FORMAT(date,\'%d\.%m\.%Y\') date,title,status,codeAuth FROM events WHERE id = "'.$eventId.'"';
			$result = mysqli_query($conn, $sql);

			if ($result) {
				$row = mysqli_fetch_array($result);
				$event = array("id" => $row['id'], "date" => $row['date'], "title" => $row['title'], "status" => $row['status'], "codeAuth" => $row['codeAuth']);
			} else {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			}
			
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php if (!$eventId || isset($delete)) {?>Настройки <?php if ($action == 'newEvent') { echo '/ Создать событие';} } else { echo $event['title']; }?></title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
	<?php 
	if (!$eventId || isset($delete)) {
		if ($action != 'newEvent') {
			include_once('admin.php');
		} else {
			include_once('newEvent.php');
		}
	} else {
		include_once('event.php');
	}
	?>		
</body>
</html>
<?php
mysqli_close($conn);
?>