<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KituRare Collections | Cart Count</title>
    <script src="https://cdn.tailwindcss.com"></script>


</head>
<div class="relative inline-block">
    <span class="text-gray-700">🛒 Cart</span>
    @if($count > 0)
        <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
            {{ $count }}
        </span>
    @endif
</div>

</html>
