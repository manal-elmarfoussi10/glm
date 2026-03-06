<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'GLM' }}</title>
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
        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: #334155;
            background-color: #f1f5f9;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0;
            mso-table-rspace: 0;
        }
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 24px 16px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            padding: 28px 32px;
            text-align: center;
        }
        .header img {
            max-height: 40px;
            width: auto;
            display: inline-block;
        }
        .body {
            padding: 32px;
        }
        .footer {
            padding: 24px 32px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }
        .footer a {
            color: #2563EB;
            text-decoration: none;
        }
        @media only screen and (max-width: 620px) {
            .wrapper { padding: 16px 12px !important; }
            .body { padding: 24px 20px !important; }
            .header, .footer { padding-left: 20px !important; padding-right: 20px !important; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <img src="{{ $logoUrl ?? config('app.url') . '/images/light-logo.png' }}" alt="GLM" width="140" />
            </div>
            <div class="body">
                @yield('content')
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} GLM<br>
                <a href="mailto:contact@glm.marfoussiwebart.com">contact@glm.marfoussiwebart.com</a>
            </div>
        </div>
    </div>
</body>
</html>
