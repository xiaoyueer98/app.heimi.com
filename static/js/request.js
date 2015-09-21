function  request(strParame){
    //alert(strParame);
    var args = new Object();
    var query = location.search.substring(1);
    var pairs = query.split("&");
    //alert(query);alert(pairs);
    for(var i = 0;i<pairs.length;i++){
        var pos = pairs[i].indexOf('=');
        if(pos == -1) continue;
        var argname = pairs[i].substring(0,pos);
        var value = pairs[i].substring(pos+1);
        //alert(value);
        value = decodeURIComponent(value);
        args[argname] = value;
    }
    //alert(args[strParame]);
    return args[strParame];
    
}