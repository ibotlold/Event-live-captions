<div class="header">
	<a href="index.php"><h1>Настройки</h1></a>
</div>
<div class="wrapper">
	<div class="block">
		<h1>Активные события</h1>
		<?php
		$sql = 'SELECT id,DATE_FORMAT(date,\'%d\.%m\.%Y\') date,title,status FROM events WHERE status != 3 ORDER BY events.date  ASC';
		$result = mysqli_query($conn, $sql);

		if ($result) {
			while ($row = mysqli_fetch_array($result)) {
				echo "<a href=\"index.php?id=".$row['id']."\" class=\"event\" title=\"Создано ".$row['date']."\">".$row['title']."</a>\n";
			}
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn); 
		}
		?>
		<a href="index.php?action=newEvent" class="button"><div class="icon" id="add"></div><div class="text">Добавить новое событие</div></a>
	</div>
	<div class="block">
		<h1>Архив</h1>
		<?php
		$sql = 'SELECT id,DATE_FORMAT(date,\'%d\.%m\.%Y\') date,title,status FROM events WHERE status = 3 ORDER BY events.date  ASC';
		$result = mysqli_query($conn, $sql);

		if ($result) {
			while ($row = mysqli_fetch_array($result)) {
				echo "<a href=\"index.php?id=".$row['id']."\" class=\"event\" title=\"Создано ".$row['date']."\">".$row['title']."</a>\n";
			}
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn); 
		}
		?>
	</div>
</div>