<div>
    <h2>Checkout</h2>
    <?php if (!empty($errors)) { ?>
        <div class="error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php } ?>

    <form method="post" action="/?route=orders.placeOrder">
        <div>
            <label for="phone">Phone (customer)</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($old['phone'] ?? '') ?>">
        </div>

        <div>
            <label for="articles">Articles (Example: 1,2,3)</label>
            <textarea id="articles" name="articles" rows="6" cols="20"><?php echo htmlspecialchars(is_array($old['articles']) ? json_encode($old['articles']) : ($old['articles'] ?? '')) ?></textarea>
        </div>
        <div>
            <label for="subscription_id">
                Subscription package id
            </label>
            <input type="text" id="subscription_id" name="subscription_id" value="<?php echo htmlspecialchars($old['subscription_id'] ?? '') ?>">
        </div>
        <button type="submit">Place Order</button>
    </form>
</div>