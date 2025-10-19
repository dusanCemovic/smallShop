<h2>Orders</h2>
<?php if (!empty($orders)): ?>
    <table>
        <tr>
            <th>#</th>
            <th>Order No</th>
            <th>Customer phone</th>
            <th>Articles</th>
            <th>Subscription</th>
            <th>Status</th>
            <th>Total</th>
            <th>Created</th>
        </tr>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['id']) ?></td>
                <td><?php echo htmlspecialchars($order['order_number']) ?></td>
                <td><?php echo htmlspecialchars($order['phone']) ?></td>
                <td><?php echo htmlspecialchars(implode(',', json_decode($order['articles']))) ?></td>
                <td><?php echo htmlspecialchars($order['subscription_package_id']) ?></td>
                <td><?php echo htmlspecialchars($order['status']) ?></td>
                <td><?php echo htmlspecialchars($order['total_price']) ?></td>
                <td><?php echo htmlspecialchars($order['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?><p>No orders.</p><?php endif; ?>

