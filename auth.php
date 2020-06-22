<?php
include_once('db.php');
# Status check:
# 1 - informational
# 2 - succesful
# 3 - redirect
# 4 - client error
# 5 - server error
$char0 = !empty($_POST['char0']) ? htmlspecialchars($_POST['char0']) : '';
$char1 = !empty($_POST['char1']) ? htmlspecialchars($_POST['char1']) : '0';
$char2 = !empty($_POST['char2']) ? htmlspecialchars($_POST['char2']) : '0';
$char3 = !empty($_POST['char3']) ? htmlspecialchars($_POST['char3']) : '0';
$char4 = !empty($_POST['char4']) ? htmlspecialchars($_POST['char4']) : '0';
$char5 = !empty($_POST['char5']) ? htmlspecialchars($_POST['char5']) : '0';
$code = $char0.$char1.$char2.$char3.$char4.$char5;
$status = 4;
$data = 'Wrong auth code';
$pattern = '/^(\d){6}$/';
if (preg_match($pattern, $code)) {
	$status = 4;
	$data = 'Wrong Code';
	$sql = 'SELECT id FROM events WHERE events.codeAuth = "'.$code.'" AND events.status BETWEEN 1 AND 2';
	$result	= mysqli_query($conn,$sql);
	if ($result) {
		$row = mysqli_fetch_array($result);
		if ($row) {
			$status = 2;
			$data = $row['id'];
		}
	}
}
$result = array("status" => $status, "data" => $data);
echo json_encode($result);
mysqli_close($conn);
?>