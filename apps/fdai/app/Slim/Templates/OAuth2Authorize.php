<!DOCTYPE html>
<html>
<head>
	<title>Authorize Request</title>
	<!--<link rel="stylesheet" type="text/css" href="../Templates/css/oauth2authorize.css"/>-->
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link
		href="https://cdn.jsdelivr.net/npm/daisyui@2.6.0/dist/full.css"
		rel="stylesheet"
		type="text/css"
	/>
	<script src="https://cdn.tailwindcss.com"></script>
	<style>
		
		#logo {
			width: 100%;
			border-radius: 50%;
			max-width: 100px;
		}

		.dialog {
			-webkit-border-radius: 3px;
			border-radius: 3px;
			z-index: 1000001;
			position: absolute;
			max-width: 320px;
			top: 108px;
			left: 0;
			right: 0;
			margin-left: auto;
			margin-right: auto;
		}
		
		#client-name {
			font-weight: bold;
		}

		#permissions-list {
			list-style: none;
			padding-left: 0;
		}

		#permissions-list li {
			padding: 5px;
		}

		/* Responsive stuffs */
		@media only screen and (max-width: 768px) {
			.dialog {
				top: 16px;
				max-width: 100%;
				padding-left: 20px;
				padding-right: 20px;
			}
		}
	</style>
	<script src="https://code.jquery.com/jquery-1.11.1.min.js" type="text/javascript"></script>
	<script src="/js/psychedelic-loader.js" defer></script>
</head>
<body class="h-screen bg-gradient-to-b from-gray-900 to-gray-800">
<div class="dialog card w-96 bg-base-100 shadow-xl"
     id="authorize-dialog">

	<div class="dialog-content card-body">
		<div style="text-align: center; padding-bottom: 10px; margin: auto;">
			<img id="logo" src="<?php echo $logoUrl ?>">
		</div>
		<div id="request-heading" class="card-title">
			<span id="client-name"><?php echo $appDisplayName ?></span> would like to:
		</div>
		
		<ul id="permissions-list"
			class="list-disc">
			<?php foreach ($scopeDescriptions as $item): ?>
				<li class="flex items-center">
					<svg class="w-4 h-4 mr-1.5 text-green-500 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
					<?= $item ?>
				</li>
			<?php endforeach; ?>
			<li class="flex items-center">
				<svg class="w-4 h-4 mr-1.5 text-gray-400 flex-shrink-0 text-red-500 " fill="currentColor" viewBox="0 0 
				20 20" 
				     xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
				Take your children in the night
			</li>
		</ul>
		


		<div class="pt-1">
			<form method="post" class="card-actions justify-end">
				<input type="hidden" name="<?php echo $csrf_key; ?>" value="<?php echo $csrf_token; ?>">
				<button class="btn btn-link"
				        id="button-deny"
				        type="submit"
				        name="authorized"
				        value="0">Cancel
				</button>
				<button class="btn btn-primary"
				        id="button-approve"
				        type="submit"
				        name="authorized"
				        value="1">Accept
				</button>
			</form>
		</div>
		
		<div class="pt-6">
			<small>Not <strong><?php echo $loginName ?></strong>?
				<a id="login-link"
				   class="badge badge-primary"
				   href="/auth/login?logout=1&redirectTo=<?php echo urlencode($requestPath) ?>">
					Login</a>
				as another user.
			</small>
		</div>

	</div>

</div>
<script>
	$(function () {
		var submitButton = document.querySelector('#button-approve');
		if (submitButton) {
			submitButton.addEventListener('click', function () {
				showLoader();
				var loginForm = document.querySelector('#authorize-dialog');
				if (loginForm) {
					loginForm.style.display = 'none';
				} else {
					console.debug('#authorize-dialog not found so loader can\'t hide it!');
				}
			});
		}
	});
</script>
</body>
</html>
