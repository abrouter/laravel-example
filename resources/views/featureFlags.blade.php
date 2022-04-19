<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laravel Test page</title>
</head>
<body>

<div>Hello. It's a ABRouter feature flags example. </div>
<br/>
@if ($enabledButtonFeatureFlag)
    <button>This button is showing</button>
@endif
<br/>
<br/>

And the next button is not showing.
<br/>
<br/>


@if ($disabledButtonFeatureFlag)
    <button>This button is not showing</button>
@endif
Let's inspect the code to understand how it works:

<br/>
<br/>
<br/>
Controller:
<br/>

<img src="/imgs/controller.png" width="50%"/>

<br/>
<br/>
<br/>
And the part of template:
<br/>

<img src="/imgs/template.png" width="50%"/>

<br/>
<br/>
<br/>
<div style="width: auto; margin-left:47%;"><a href="/">Back to the main page</a>
</div>
<br/>
<br/>
<br/>



</body>
</html>
