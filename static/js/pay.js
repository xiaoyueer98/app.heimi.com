var ccid = request("ccid");
var session_id = request("session_id");
var fid = "";
var start_time = "";
var end_time = "";
$.ajax({
    url: "/device/Getflowlist/index",
    data: "ccid=" + ccid,
    type: "GET",
    dataType: "json",
    success: function (re) {
        var code = re['code'];
        if (code == '200') {
            //alert(re['data'].ccid);
            var data = re['data'];
            for (var i = 0; i < data.length; i++) {
                
                $(".p1").html("<span style='font-weight: normal;font-size:20px; '>￥<b></b>元</span>" + data[i].name);
                $(".p1 span b").html(data[i].app_price.substr(0,data[i].app_price.length-1));
                $(".p2").html(data[i].next_month);
                $(".h_bottom").html(data[i].demo);
                if(ccid.length == 20){
                    ccid = ccid.substr(0,19);
                }
                $(".center .ccid").html(ccid+"*");
                fid = data[i].id;
                start_time = data[i].pay_start_date;
                end_time = data[i].pay_end_date;

            }

        }

    }
})

function chongzhi() {
    $(".footer").html("<a>充值</a>");
    $("#lodd").show();
    $.ajax({
        url: "/flow/buyflow/index",
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
} 
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
