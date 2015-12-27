//setInterval("checkInternetConnection()", 5000);

function checkInternetConnection(){
    $.post('curl.php', function(data) {
        if(data == 'true') {
            $(".netConnectionDisplay").css({'background':'#9cef9e'});
            $(".netConnectionDisplay").text("Internet connection is up. Click here to update your offline data.");
        } else {
            $(".netConnectionDisplay").css({'background':'#f0a8a5'});
            $(".netConnectionDisplay").text("Internet connection is down. Check your internet connection settings.");
        }
    });
}
