$.ajax({
    url: "/api/cardinfo/getcardinfo",
    data: "ccid=" + ccid,
    type: "GET",
    dataType: "json",
    success: function (re) {
        var code = re['code'];
        var detail = re['detail'];
        if (code == '200') {
            //alert(re['data'].ccid);
            //var ccid_x = re['data'].ccid.substr("4",8);
            //var ccid_cx = re['data'].ccid.replace(ccid_x,"********");
            //$(".hea_div1_p1").html(ccid_cx + "*");

            $(".hea_div1_p1").html(ccid + "*");
            $(".hea_div1_p2").html(re['data'].name);
            $(".hea_div2").html(re['data'].description);
            var data = re['data'].flow_list;

            for (var i = 0; i < data.length; i++) {
                for (key in data[i]) {
                    if (data[0][key] == -1) {
                        var myDate = new Date();
                        var y = myDate.getFullYear();
                        var m = myDate.getMonth();
                        var m = m+1;
                        $(".hea_div3 ul").append("<li><span style='font-size:16px;'>"+y+"-"+m+"</span><b style='font-size:16px;'>0MB</b></li><li><span style='color:white'>.</span><b style='color:white'>.</b></li><li style=\"border-right:none\"><span style='color:white'>.</span><b style='color:white'>.</b></li>");
                    } else {

                        if ((i+1)%3 == 0) {
                            $(".hea_div3 ul").append("<li style='border-right:none;'><span style='font-size:16px;'>" + key + "</span><b style='color:#333;font-size:16px;'>" + data[i][key] + "MB</b></li>");
                        } else {
                            if (i == 0) {
                                $(".hea_div3 ul").append("<li><span style='font-size:16px;'>" + key + "</span><b id='load' style='font-size:16px;'><img src='/static/images/loading.gif' style='height:15px;'></b></li>");
                                $.ajax({
                                    url: "/flow/getflow/index",
                                    data: "iccid=" + ccid,
                                    type: "GET",
                                    dataType: "json",
                                    success: function (re) {

                                        //alert(re['data'][0].left);
                                        if (re['code'] == "200") {
                                            var left = re['data'][0].left;
                                           
                                            if(left > 1){
                                                left = Math.round(left);
                                            }else if(left >0){
                                                //保留1为小数
                                                var vv = Math.pow(10,1);
                                                left = Math.round(left*vv)/vv;
                                               
                                            }else{
                                                left = 0;
                                            }
                                            $("#load").html(left+"MB");
                                        }else if(re['code'] == "500"){
                                            
                                            //location.href="/api/cardinfo/netbusy";
                                            $(".hea_div3").html("<div style='color:#2882e2;font-size:24px;text-align:center;padding:10px 0;'>网络繁忙，无法获取剩余流量</div>");
                                        }
                                    }
                                })
                            } else {
                                $(".hea_div3 ul").append("<li><span style='font-size:16px;'>" + key + "</span><b style='color:#333;font-size:16px;'>" + data[i][key] + "MB</b></li>");
                            }
                        }

                    }


                }

            }


        } else if (code == '500' && detail == '未查到相关信息，请确认是否为747流量卡!') {
            $("#xq_wrap").hide();
            $("#no_info").hide();
        }

    }
})