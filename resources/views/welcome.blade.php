<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Show your support for TEDxBucharest</title>
        <meta name="description" content="Show your firends you are a tedster by changing your Facebook profile image">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="css/normalize.min.css">
        <link rel="stylesheet" href="css/main.css">
    </head>
    <body>

        <h1>Show your support for TEDxBucharest</h1>

        <img src="{{ route('picture', ['id' => $userId]) }}" alt="" />

        <form action="{{ route('upload') }}" method="post">
            <input type="text" name="description" value="Created with {{ env('CALLBACK') }}" />
            <button type="submit">Set as profile picture</button>
        </form>

        <script src="js/main.js"></script>
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', '{{ env('GOOGLE_ANALYTICS') }}', 'auto');
            ga('send', 'pageview');
        </script>
    </body>
</html>
