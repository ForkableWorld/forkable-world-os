window.mintDataGem = async function() {
	await LitJsSdk.litJsSdkLoadedInALIT()
	qm.auth.setAccessToken('demo')
	var user = await qm.web3.mintDataGem('overall mood');
}
// start().then(function() {
// 	console.log('done')
// })
