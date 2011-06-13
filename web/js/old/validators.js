App.Validate.ipForm = function(values){
    if(values.IP_ADDRESS == '') {
        return alert('Not correct ip');
    }
    
    return true;
}

App.Validate.dnsForm = function(values){
    return true;
}


