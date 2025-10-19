<!doctype html>
<html lang="">
<head>
    <meta charset="utf-8">
    <title>CREATIM task</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header class="nav">
        <a href="/?route=articles.index">Articles</a> |
        <a href="/?route=subscriptions.index">Subscriptions</a> |
        <a href="/?route=orders.index">Orders</a> |
        <a href="/?route=orders.checkoutForm">Checkout</a>
    </header>

    <div class="content">
        <?php include $viewFile; ?>
    </div>
</body>
</html>
