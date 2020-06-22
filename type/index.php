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

$eventId = !empty($_GET['id']) ? trim(htmlspecialchars($_GET['id'])) : NULL;
$event = NULL;
if (isset($eventId)) {
	$sql = 'SELECT id,DATE_FORMAT(date,\'%d\.%m\.%Y\') date,title,status,codeAuth FROM events WHERE id = "'.$eventId.'"';
	$result = mysqli_query($conn, $sql);
	if ($result) {
		$row = mysqli_fetch_array($result);
		$event = array("id" => $row['id'], "date" => $row['date'], "title" => $row['title'], "status" => $row['status'], "codeAuth" => $row['codeAuth']);
	} else {
		echo "Error: " . $sql . "<br>" . mysqli_error($conn);
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php if (isset($eventId)) { echo $event['title']; } else { echo "Ошибка!";} ?></title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
	<div class="wrapper" id="loader"><div class="loader"> </div></div>
	<?php  if ($eventId) {?>
		<div class="header"><h1><?php echo $event['title'].' / Код: '.$event['codeAuth']; ?></h1>
			<div class="status">
				<input type="button" name="0" value="Закрыть доступ" <?php if ($event['status'] == 0) { echo 'disabled'; } ?>>
				<input type="button" name="1" value="Открыть доступ" <?php if ($event['status'] == 1) { echo 'disabled'; } ?>>
				<input type="button" name="2" value="Начать мероприятие" <?php if ($event['status'] == 2) { echo 'disabled'; } ?>>
			</div>
			<a href="/admin/"></a>
		</div>
		<div class="speechRecord"></div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<script src="script.js"></script>
		<?php
		if ($event['status'] < 3) {
			$sql = 'SELECT * FROM message WHERE message.event = "'.$eventId.'" AND message.deleted = 0';
			$result = mysqli_query($conn,$sql);
			if ($result) {
				while ($row = mysqli_fetch_array($result)) {
					$time = date_create_from_format('U.v',$row['timeStamp']/1000);
					date_timezone_set($time, timezone_open('Asia/Yakutsk'))
					?>
					<div class="caption label" id="<?php echo $row['timeStamp']; ?>">
						<div class="timestamp"><?php echo date_format($time,'H:i');;?></div>
						<textarea class="text" name="" placeholder="Новое сообщение..." rows="2"><?php echo $row['text']; ?></textarea>
					</div>
					<?php
				}
			}
		}
		?>
	<?php } ?>
</body>
</html>