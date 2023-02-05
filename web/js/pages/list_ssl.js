// eslint-disable-next-line @typescript-eslint/no-unused-vars
function saveTextToBlob(file, element) {
	const downloadLink = document.createElement('a');
	downloadLink.style.display = 'none';
	downloadLink.textContent = 'Download File';
	downloadLink.download = file;
	downloadLink.href = window.URL.createObjectURL(
		new Blob([document.getElementById(element).value], { type: 'text/plain' })
	);

	const child = document.body.appendChild(downloadLink);
	downloadLink.click();
	document.body.removeChild(child);
}
