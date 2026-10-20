<!DOCTYPE html>
<html>

<head>
    <title>Email Template</title>
    <style>
        /* Basic styles are still fine here */
        body {
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
        }

        .container {
            width: 100%;
            max-width: 800px;
            background-color: #ffffff;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .header,
        .footer {
            background-color: #007BFF;
            color: #ffffff;
            text-align: center;
            padding: 15px;
        }

        .header {
            border-radius: 8px 8px 0 0;
        }

        .footer {
            border-radius: 0 0 8px 8px;
            font-size: 14px;
        }

        .content-body {
            padding: 20px;
            color: #333;
        }

        .dynamic-section {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        a {
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
{{-- Set the overall direction on the body tag --}}

<body dir="{{ $direction }}">

    <div class="container" style="direction: {{ $direction }};">
        <div class="header">
            <h2>Welcome to Our Service</h2>
        </div>

        {{-- Apply the dynamic text alignment to the content body --}}
        <div class="content-body" style="text-align: {{ $textAlign }};">
            <p>Hello,</p>
            <p>We are excited to have you with us!</p>

            {{-- The border side is now conditional based on the direction --}}
            <div class="dynamic-section" style="
            border-left: {{ $direction === 'ltr' ? '4px solid #007BFF' : 'none' }};
            border-right: {{ $direction === 'rtl' ? '4px solid #007BFF' : 'none' }};
        ">
                {!! $dynamicContent !!}
            </div>

            <p>Feel free to reach out if you need any assistance.</p>
        </div>

        <div class="footer">
            <p>Thank you for using our service! 🚀</p>
            <p><a href="https://ayhoot.com">Visit our website</a></p>
        </div>
    </div>

</body>

</html>