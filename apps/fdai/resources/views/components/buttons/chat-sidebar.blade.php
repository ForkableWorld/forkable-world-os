@include('components.buttons.chat-button')
<script>
	drift.on('ready', (api) => {
		api.sidebar.open(); // https://devdocs.drift.com/docs/conversation-sidebar
	})
</script>
