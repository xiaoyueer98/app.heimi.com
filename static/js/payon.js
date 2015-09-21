var ccid = request("ccid");
var session_id = request("session_id");
var fid = "";
var start_time = "";
var end_time = "";
 
function newchongzhi() {
	if($('#addressname').val()!='')
	{
		$(".footer").html("<a>充值中</a>");
		$("#lodd").show();
		$.ajax({
			url: "/flow/buyflow/change",
			data: "session_id=" + session_id + "&fid=" + fid + "&ccid=" + ccid + "&starttime=" + start_time + "&endtime=" + end_time,
			type: "POST",
			dataType: "json",
			success: function (re) {
				$("#lodd").hide();
				var code = re['code'];
				if (code == '200') {
					$(".footer").html("<a>充值</a>");
					$("#lodd").show();
					location.href = re['data'];
					//$("#lodd").hide();
					//alert("hehe");
				}else if(code == '500'){

					alert("参数异常"); 
					$("#lodd").hide();
				}

			}
		})
	}else
	{
		alert('请先添加收货地址');
        $("#lodd").hide();
	}
}
