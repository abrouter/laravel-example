<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laravel Test page</title>
</head>
<body>

<div>Hello. It's a ABRouter example. Refresh the page to change color button. </div>
<div>Distribution: 33% - red, 33% - green, 33% - blue</div>
<div>We're using uniqid() as the user id, so it's changing time-to-time. In case of using the same id for the same user branch will be memorized.</div>
<br/>




<button style="background: {{$color}}; padding: 10px; border-radius: 3px; margin-left: 20%;">Hello</button>

<div>Additionally, we have sent statistic event: visited_test_page</div>


<br/>

@if ($parallelRunning)
    <h2>Attention</h2>
    <div>
        Parallel running enabled. Please check it in the config/abrouter.php file


        <br/>
        It means that A/B test was completely ran on your server and then the result was sent to ABRouter server in the job.
    </div>
@endif

<br/>
<br/>



<a href="/feature-flags">Check the feature flags <example></example></a>

</body>
</html>
