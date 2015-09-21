$.ajax({
    url: "/api/cardinfo/getcardinfoyear",
    data: "ccid=" + ccid,
    type: "GET",
    dataType: "json",
    success: function (re) {
        var code = re['code'];
        if (code == '200') {
//            alert(re['data'].ccid);
//            var ccid_x = re['data'].ccid.substr("4",8);
//            var ccid_cx = re['data'].ccid.replace(ccid_x,"********");
//            $(".hea_div1_p1").html(ccid_cx + "*");

//            $(".hea_div1_p1").html(ccid + "*");
//            //alert(ccid);
//            $(".hea_div1_p2").html(re['data'].name);
//            $(".hea_div2").html(re['data'].description);
            $("#content").html("<div class=\"hea_div1\"><p class=\"hea_div1_p1\">" + ccid + "*</p><p class=\"hea_div1_p2\">" + re['data'].name + "</p></div><div  class=\"hea_div2\">" + re['data'].description + "</div>");
            $.ajax({
                url: "/api/cardinfo/getflowyear",
                data: "ccid=" + ccid,
                type: "GET",
                dataType: "json",
                success: function (re) {
                    var code = re['code'];
                    if (code == '200') {
                        $(".hea_div3").html("<section class=\"container\"><p>套餐余量<span id=\"left\">"+re['data'].left+"</span>   套餐总量<span id=\"total\">"+re['data'].total+"</span></p><div class=\"progress\"><span class=\"red\" style=\"width:0%;\"><span></span></span></div></section>");
                        loading(re['data'].percent);
                    }else if(re['code'] == "500"){
                        //location.href="/api/cardinfo/netbusy";
                        $(".hea_div3").html("<div style='color:#2882e2;font-size:24px;text-align:center;padding:10px 0;'>网络繁忙，无法获取剩余流量</div>");
                    }
                }
            })
//            var data = re['data'].flow_list;
//            $("#left").html(data.left);
//            $("#total").html(data.total);
//            loading(data.percent);
        }else if(re['code'] == "500"){
              location.href="/api/cardinfo/netbusy";
        }

    }
})