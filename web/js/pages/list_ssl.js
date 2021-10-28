function saveTextToBlob ( file, element ){
    text = document.getElementById(element).value;
    console.log(text, file);
    var textFileAsBlob = new Blob([text], {type:'text/plain'}); 
    var downloadLink = document.createElement("a");
    downloadLink.download = file;
    downloadLink.innerHTML = "Download File";
    if (window.webkitURL != null)
    {
        // Chrome allows the link to be clicked
        // without actually adding it to the DOM.
        downloadLink.href = window.webkitURL.createObjectURL(textFileAsBlob);
    }
    else
    {
        // Firefox requires the link to be added to the DOM
        // before it can be clicked.
        downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
        downloadLink.onclick = destroyClickedElement;
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
    }

    downloadLink.click();
    return false;
}

function destroyClickedElement(event)
{
    document.body.removeChild(event.target);
}