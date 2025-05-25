<?php
if (isset($errors) && is_array($errors) && count($errors) > 0) {
	foreach ($errors as $error) {
		error_log("Error: " . $error, 3, "error.log");
	}
	?>
	<div class="error alert alert-danger alert-dismissible fade show" role="alert">
		<?php foreach ($errors as $error): ?>
			<p><?php echo htmlspecialchars($error); ?></p>
		<?php endforeach; ?>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
<?php } ?>