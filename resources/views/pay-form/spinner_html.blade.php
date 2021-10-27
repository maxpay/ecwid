<!DOCTYPE html>
<html lang="en">
<head>
    <title>Maxpay Payment Form</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="/css/maxpay.css"/>
</head>
<body>
<div id="spinner-container">
    <img src="/img/processing.gif" alt="Processing, please wait" title="Processing, please wait"><br>
    <div id="status">Processing, please wait</div>
</div>

<script>
    let retry = 0;
    let interval = 2000; // 1000 = 1 second
    let orderId = '{{ $orderId }}';
    function doAjax() {
        $.ajax({
            url: '/check-callback',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({orderId: orderId, fallback: retry++ > 30}),
            success: function(data) {
                if (data.redirectUrl) {
                    window.location.replace(data.redirectUrl);
                } else if (data.error) {
                    $('#status').html(data.error);
                } else {
                    setTimeout(doAjax, interval);
                }
            },
            error: function(jqXHR, textStatus) {
                $('#status').html(textStatus);
            },
        });
    }
    setTimeout(doAjax, interval);
</script>
</body>
</html>
