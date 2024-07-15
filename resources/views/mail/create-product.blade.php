<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <style>
        p {
            font-size: 12px;
        }

        .signature {
            font-style: italic;
        }
    </style>
</head>
<body>
<div>
    <p>Hi Admin,</p>
    <p>Product Info: </p>
    <p>ID : {{$product ? $product->id : ''}}</p>
    <p>Category :{{$product->category ? @collect($product->category->langs)->where('lang_id' , 2)->first()->title : ''}}</p>
    <p>Quantity : {{$product ? $product->quantity : ''}}</p>
    <p>Cost : {{$product ? $product->cost : ''}}</p>

    <p class="signature">Mailtrap</p>
</div>
</body>
</html>