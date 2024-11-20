<?php include 'header.php'; ?>
<div id="config" 
	data-api-url="<?php 
		$config = include __DIR__ . '/../../config/config.php';
		$baseApiUrl = $config['base_api_url'];
		echo htmlspecialchars($baseApiUrl, ENT_QUOTES, 'UTF-8'); 
	?>" 
	data-user-id="<?php
		$config = include __DIR__ . '/../../config/config.php';
		$userId = $_SESSION['id'];
		echo htmlspecialchars($userId, ENT_QUOTES, 'UTF-8'); 
	?>" 
	class="antialiased bg-gray-50 dark:bg-gray-900">
	<?php include 'views/partials/nav.php'; ?>
	<?php include 'views/partials/aside.php'; ?>
	<main class="p-4 md:ml-64 h-auto pt-20">
		<section class="bg-gray-50 dark:bg-gray-900">
		  <div id="view-container" class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0"></div>
		</section>
	</main>
</div>
<?php include 'views/partials/modals/messages/success_message.php'; ?>
<?php include 'views/partials/modals/messages/error_message.php'; ?>
<?php include 'footer.php'; ?>