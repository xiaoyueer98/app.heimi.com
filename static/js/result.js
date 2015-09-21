
res = request("res");
if(res == "success"){
    $("#fail").css("display","none");
    $("#succ").css("display","block");
}else{
    $("#succ").css("display","none");
    $("#fail").css("display","block");   
}
