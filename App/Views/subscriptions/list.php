<h2>Subscriptions</h2>
<p><a href="/?route=subscriptions.form">Add subscription</a></p>
<?php if (!empty($packages)): ?>
    <table>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Price</th>
            <th>Physical</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($packages as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['id']) ?></td>
                <td><?php echo htmlspecialchars($p['name']) ?></td>
                <td><?php echo htmlspecialchars($p['price']) ?></td>
                <td><?php echo $p['includes_physical_magazine'] ? 'Yes' : 'No' ?></td>
                <td><?php echo htmlspecialchars($p['created_at']) ?></td>
                <td>
                    <form method="post" action="/?route=subscriptions.delete" style="display:inline">
                        <input type="hidden" name="id" value="<?php echo $p['id'] ?>">
                        <button type="submit" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?><p>No packages.</p><?php endif; ?>

