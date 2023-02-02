<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title></title>
    <!--[if mso]>
 <noscript>
  <xml>
            <o:OfficeDocumentSettings>
    <o:PixelsPerInch>96</o:PixelsPerInch>
   </o:OfficeDocumentSettings>
  </xml>
 </noscript>
 <![endif]-->
    <style>
        table,
        td,
        div,
        h1,
        p {
            font-family: Arial, sans-serif;
        }

        .footer td {
            background: #3E7BA0 !important;
            color: #ffffff;
        }

        img {
            width: 300px;
        }

        table {
            text-align: center;
            width: 100%;
            border-collapse: collapse;
            border: 0;
            border-spacing: 0;
            background: #ffffff;
        }
    </style>
</head>

<body style="margin:0;padding:0;">
    <table role="presentation">
        <tr class="header">
            <td style="padding:20px;">
                <img alt="Aurora Logo" src="https://aurorasw.com.au/aurora-logo.png" />
            </td>
        </tr>
        @hasSection('content')
            <tr>
                <td style="padding:20px;">
                    @yield('content')
                </td>
            </tr>
        @endif
        <tr class="footer">
            <td style="padding:20px;">
                footer
            </td>
        </tr>
    </table>
</body>

</html>
