$.ajax({
    url: "/api/cardinfo/getcardinfoyear",
    data: "ccid=" + ccid,
    type: "GET",
    dataType: "json",
    success: function(re) {
        if (re['code'] == "200") 
        {
            $(".card_num").html(ccid + "*");
            $(".span1").html(re['data'].name);
            $(".hea_bot").html(re['data'].description);
            $.ajax({
                url: "/api/cardinfo/getflowdiffer",
                data: "ccid=" + ccid,
                type: "GET",
                dataType: "json",
                success: function(re) {
                    if(re['code'] == '200') 
                    {
                        var D = re['data'];
                        $("#punlim").attr("style","width:"+D.punlimit+"%");
                        $("#plocal").attr("style","width:"+D.plocal+"%");
                        $("#left1").html(D.unlimit+"MB");
                        $("#left2").html(D.local+"MB");
                    }
                    else if (re['code'] == "500") 
                    {
                        location.href = "/api/cardinfo/netbusy";
                    }
                }
            });
        }
        else if (re['code'] == "500") 
        {
            location.href = "/api/cardinfo/netbusy";
        }
    }
});