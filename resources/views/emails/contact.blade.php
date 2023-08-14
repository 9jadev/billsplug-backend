<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <ul>
        <li>Name: {{ $data["name"] }}</li>
        <li>Email: {{ $data["email"] }}</li>
        <li>Phone: {{ $data["phone"] }}</li>
    </ul>
    <div>
        {{ $data["message"] }}
    </div>
</body>
</html>
