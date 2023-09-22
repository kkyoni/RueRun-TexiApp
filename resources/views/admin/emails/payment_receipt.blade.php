<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ruerun - Payment Receipt</title>
</head>
<body style="padding:0px; margin:0px;">
    <div id="invoice-POS" style="box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5); padding:2mm; margin: 0 auto; width: 200mm; background: #FFF;">
        <center id="top" style="min-height: 100px;">
            <div class="logo" style="height: 60px; width: 60px; background: url({{asset(\Settings::get('application_logo'))}}) no-repeat; background-size: 60px 60px;"></div>
        </center>
        <div class="info" style="display: block; margin-left: 0;">
            <h2>Hello {{$userName}},</h2>
            <p>Thanks for join Ruerun for Ride And Complete Trip Payment Receipt.</p>
        </div>
        <div id="bot" style="border-bottom: 1px solid #EEE; min-height: 50px;">
            <div id="table">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr class="tabletitle" style="background: #EEE;">
                        <td class="item" style="width: 24mm;"><h2 style="font-size: .9em;">Name</h2></td>
                        <td class="Hours"><h2 style="font-size: .9em;">Description</h2></td>
                    </tr>
                    <tr class="tabletitle" style="border-bottom: 1px solid #EEE;">
                        <td class="Rate"><p style="font-size: 1em; line-height: 2.2em;">Pick Up Location</p></td>
                        <td class="tableitem"><p class="itemtext" style="font-size: .9em; line-height: 1.2em;">{{$pick_up_location}}</p></td>
                    </tr>
                    <tr class="tabletitle" style="border-bottom: 1px solid #EEE;">
                        <td class="Rate"><p style="font-size: 1em; line-height: 2.2em;">Droup Location</p></td>
                        <td class="tableitem"><p class="itemtext" style="font-size: .9em; line-height: 1.2em;">{{$drop_location}}</p></td>
                    </tr>
                    <tr class="tabletitle" style="border-bottom: 1px solid #EEE;">
                        <td class="Rate"><p style="font-size: 1em; line-height: 2.2em;">Booking Date</p></td>
                        <td class="tableitem"><p class="itemtext" style="font-size: .9em; line-height: 1.2em;">{{$booking_date}}</p></td>
                    </tr>
                    <tr class="tabletitle" style="border-bottom: 1px solid #EEE;">
                        <td class="Rate"><p style="font-size: 1em; line-height: 2.2em;">Ride Type</p></td>
                        <td class="tableitem"><p class="itemtext" style="font-size: .9em; line-height: 1.2em;">{{$ride_name}}</p></td>
                    </tr>
                    <tr class="tabletitle" style="border-bottom: 1px solid #EEE;">
                        <td class="Rate"><p style="font-size: 1em; line-height: 2.2em;">Trip Status</p></td>
                        <td class="tableitem"><p class="itemtext" style="font-size: .9em; line-height: 1.2em;">{{$trip_status}}</p></td>
                    </tr>
                    <tr class="tabletitle">
                        <td class="Rate"><h2 style="font-size: .9em;">Total Amount</h2></td>
                        <td class="payment"><h2 style="font-size: .9em;">{{$base_fare}}</h2></td>
                    </tr>
                </table>
            </div>
        </div>
        <br><br>
        <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #3d4852; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: right;">Thank You,<br> Ruerun Team</p>
    </div>
</body>
</html>