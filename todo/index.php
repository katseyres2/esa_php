<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Document</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="./css/style.css">
	<script src="https://kit.fontawesome.com/b92b01fb6c.js" crossorigin="anonymous"></script>
</head>

<?php
// Affiche un formulaire pour ajouter une nouvelle tâche.
// Liste toutes les tâches avec des options pour les marquer comme réalisées/non réalisées, les éditer et les supprimer.
include_once('./functions.php');
include_once('./add.php');
include_once('./delete.php');

$id = $status = $title = $description = null;
$showDeleted = false;

if (isset($_POST['filterShowDeletedTasks'])) {
	setcookie('filterShowDeletedTasks', $_POST['filterShowDeletedTasks']);
	$showDeleted = $_POST['filterShowDeletedTasks'] == '1';
} else if (isset($_COOKIE['filterShowDeletedTasks'])) {
	$showDeleted = $_COOKIE['filterShowDeletedTasks'] == '1';
}

$todos = getTodos();
$displayedTodos = [];
$deletedTodos = [];
$doneTasksCounter = 0;

foreach ($todos as $td) {
	if ($td->isDeleted()) $deletedTodos[] = $td;
	else $displayedTodos[] = $td;

	if ($td->isDone()) $doneTasksCounter++;
}

$activeTasksCounter = count($displayedTodos);
$activeTasks = [];
$doneTasks = [];

foreach ($displayedTodos as $task) {
	if ($task->isDone()) $doneTasks[] = $task;
	else $activeTasks[] = $task;
}

$displayedTodos = array_merge($activeTasks, $doneTasks);

if ($showDeleted) {
	$displayedTodos = array_merge($displayedTodos, $deletedTodos);
}
?>

<body>
	<div class="container text-left">
		<div class="container text-center mb-5 mt-1 border-bottom border-secondary">
			<h1>My Tasks</h1>
		</div>
		<div class="container text-left">
			<div class="row">
				<div class="col container">
					<div class="row">
						<div class="col">
							Active tasks<br>
							Deleted tasks<br>
							Checked tasks<br>
						</div>
						<div class="col">
							<?=$activeTasksCounter?><br>
							<?=count($deletedTodos)?><br>
							<?=$doneTasksCounter?><br>
						</div>
					</div>
					<div class="row pt-3">
						<div class="col-md-auto">
							<form action="./index.php" method="POST">
								<input type="hidden" name="filterShowDeletedTasks" value="<?= $showDeleted ? '0' : '1' ?>">
								<input style="width: 10em;" type="submit" value="<?= $showDeleted ? 'Hide' : 'Show'?> deleted tasks" class="btn btn-secondary" <?=count($deletedTodos) == 0 ? 'disabled' : ''?>>
							</form>
						</div>
						<div class="col-md-auto">
							<form action="./clear.php" method="GET">
							<input style="width: 10em;" type="submit" value="Clear deleted tasks" class="btn btn-secondary">
							</form>
						</div>
					</div>
					<div class="row pt-3">
						<div class="col">
							<a style="width: 10em;" href="./todos.csv" class="btn btn-secondary">Download</a>
						</div>
					</div>
					<div class="row pt-3 visually-hidden">
						<div class="col">
							<form action="./purge.php" method="GET">
								<input type="submit" value="Purge all tasks" class="btn btn-primary">
							</form>
						</div>
					</div>
				</div>
				<div class="col container text-left">
					<form action="./add.php" method="POST">
						<div class="row">
							<div class="col">
								<div class="mb-3">
									<label for="inputTitle" class="form-label">Title</label>
									<input type="text" class="form-control" id="inputTitle" name="inputTitle">
								</div>
								<div class="mb-3">
									<label for="inputDescription" class="form-label">Description</label>
									<input type="text" class="form-control" id="inputDescription" name="inputDescription">
								</div>
								<button type="submit" class="btn btn-primary">Create task</button>							
							</div>
							<div class="col-3">
								<div class="mb-3">
									<label for="inputPriority" class="form-label">Priority</label>
									<select class="form-select" aria-label="Default select example" name="inputPriority">
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
									</select>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="container text-center pt-5 pb-5">
			<div class="row gy-4">
				<?php if (count($displayedTodos) == 0): ?>
					<h6 class="fst-italic">There is no visible task...</h5>
				<?php endif ?>

				<?php foreach ($displayedTodos as $todo): ?>
					<?php
						$imgStatus = $todo->isDone() ? 'done' : 'todo';
						$createdAtDate = $todo->getCreatedAt()->format('d/m/Y');
						$createdAtTime = $todo->getCreatedAt()->format('H:i:s');
						$buttonCheck = $todo->isDone() ? 'btn btn-danger' : 'btn btn-success';
						$buttonValue = $todo->isDone() ? '58' : 'f00c';
						$priorityColor = null;
						$cardBorder = null;
						
						switch ($todo->getPriority()) {
							case Priority::Medium:
								$priorityColor = 'orange';
								$cardBorder = "warning";
								break;
							case Priority::High:
								$priorityColor = 'red';
								$cardBorder = "danger";
								break;
							case Priority::Low:
							default:
								$priorityColor = 'blue';
								$cardBorder = "";
								break;
						}

						$cardBackground = '';

						if ($todo->isDone()) {
							$cardBackground = 'text-bg-success';
							$cardBorder = '';
						} else if ($todo->isDeleted()) {
							$cardBackground = 'text-bg-secondary';
							$cardBorder = '';
						}

						$modificationDisabled = $todo->isDeleted() ? 'visually-hidden' : '';
						$taskDoneTextDecoration = $todo->isDone() ? 'text-decoration-line-through' : '';
					?> 
					<div class="col" id="card-<?=$todo->getId()?>">
						<div class="card shadow w-200 h-100 <?=$cardBackground?> border-<?=$cardBorder?>">
							<div class="card-body">
								<h5>
									<label class="card-title mb-0 pb-0 text-nowrap <?=$taskDoneTextDecoration?>" for="<?= $todo->getTitle(); ?>">
										#<?=$todo->getId()?> <?= $todo->getTitle(); ?>
									</label>
								</h5>

								<h6 class="text-nowrap card-subtitle mt-0 pt-0 mb-2"><?=$createdAtDate?> - <?=$createdAtTime?></h6>
								
								<div class="card-text mb-3">
									<?= $todo->getDescription(); ?>
								</div>
							</div>
							<div class="card-footer" style="min-width: 15em;">
								<div class="container m-0 p-0">
									<div class="row justify-content-between">
										<div class="col-md-auto">
											<form action="./toggle.php" method="post" class="m-0 p-0 <?=$modificationDisabled?>" style="display:inline;width: 50px;">
												<input type="hidden" name="id" value="<?=$todo->getId()?>">
												<input type="hidden" name="check" value="<?=!$todo->isDone()?>">
												<input class="<?=$buttonCheck?>" style="font-family: FontAwesome" value="&#x<?=$buttonValue?>;" type="submit">
											</form>
											<form action="./edit-form.php" method="post" style="display:inline;width: 50px;" class="<?=$modificationDisabled?>">
												<input type="hidden" name="id" value="<?=$todo->getId()?>">
												<input class="btn btn-secondary" style="font-family: FontAwesome" value="&#xf303;" type="submit">
											</form>
											<form action="./delete.php" method="post" style="display:inline;width: 50px;" class="<?=$modificationDisabled?>">
												<input type="hidden" name="id" value="<?=$todo->getId()?>">
												<input class="btn btn-secondary" style="font-family: FontAwesome" value="&#xf1f8;" type="submit">
											</form>
											<form action="./restore.php" method="post" class="<?php if (strlen($modificationDisabled) == 0) echo 'visually-hidden'?>">
												<input type="hidden" name="id" value="<?=$todo->getId()?>">
												<input class="btn btn-primary" style="font-family: FontAwesome" value="&#xf0e2;" type="submit">
											</form>
										</div>
										<div class="col-md-auto">
											<form action="./priority.php" method="post" style="display:inline;width: 50px;" class="<?=$modificationDisabled?>">
												<input type="hidden" name="id" value="<?=$todo->getId()?>">
												<input type="hidden" name="currentPriority" value="<?=$todo->getPriority()->value?>">
												<input class="btn btn-secondary" style="font-family: FontAwesome; color: <?=$priorityColor?>; background-color: white" value="&#xf024;" type="submit">
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
