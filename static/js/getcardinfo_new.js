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
            $(".card_num").html(ccid+"*");
            $(".span1").html(re['data'].name);
            $(".hea_bot").html(re['data'].description);
            
            $.ajax({
                url: "/api/cardinfo/getflownew",
                data: "ccid=" + ccid,
                type: "GET",
                dataType: "json",
                success: function (re) {
                    var code = re['code'];
                    if (code == '200') {
                        if(re['data'].left < 1){
                            var left = re['data'].left;
                        }else{
                            var left = Math.round(re['data'].left);
                        }
                        if(re['data'].percent == 1){
                            drawingSphere(left,0.99 ,1);
                        }else{
                            drawingSphere(left,re['data'].percent ,1);
                        }
                      
                    }else if(re['code'] == "500"){
                        var percentText = document.getElementById('percentText');
                        drawingSphere(left, re['data'].percent,2);
                        //percentText.innerHTML = "<span style='font-size:2.5rem'>获取失败</span>";
                        
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