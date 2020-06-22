<?php
$action = !empty($_GET['action']) ? trim(htmlspecialchars($_GET['action'])) : NULL;
?>
<div class="header">
	<h1><a href="index.php">Настройки</a> / <a href="index.php?id=<?php echo $eventId; ?>"><?php echo $event['title']; ?></a></h1>
</div>
<div class="wrapper" id="newEvent">
	<div class="block">
		<form method="POST">
			<h1>ID</h1>
			<input type="text" name="editId" value="<?php echo $event['id']; ?>" disabled>
			<h1>Дата создания</h1>
			<input type="text" name="dateCreated" value="<?php echo $event['date']; ?>" disabled>
			<h1>Название события</h1>
			<input type="text" name="title" value="<?php echo $event['title']; ?>" maxlength="255">
			<h1>Статус события</h1>
			<input type="radio" name="status" value="0" <?php if ($event['status'] == 0) { echo 'checked'; } ?>>
			<label for="0">Доступ закрыт</label>
			<input type="radio" name="status" value="1"<?php if ($event['status'] == 1) { echo 'checked'; } ?>>
			<label for="1">Доступ открыт</label>
			<input type="radio" name="status" value="2"<?php if ($event['status'] == 2) { echo 'checked'; } ?>>
			<label for="2">Мероприятие началось</label>
			<input type="radio" name="status" value="3"<?php if ($event['status'] == 3) { echo 'checked'; } ?>>
			<label for="3">Архив</label>
			<h1>Код авторизации</h1>
			<input type="number" name="codeauth" max="999999" min="100000" value="<?php echo $event['codeAuth']; ?>">
			<input type="submit">
			<?php if ($event['status'] < 3) { ?><a href="/type/?id=<?php echo $event['id']; ?>" class="live">Начать трансляцию</a>
		<?php } ?>
		<input type="submit" id="delete" name="delete" value="УДАЛИТЬ">
	</form>
</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="shortcut.js"></script>
<script type="text/javascript">