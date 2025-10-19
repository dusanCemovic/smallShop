<h2>Articles</h2>
<p><a href="/?route=articles.form">Add article</a></p>
<?php if (!empty($articles)): ?>
    <table>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Price</th>
            <th>Supplier</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($articles as $a): ?>
            <tr>
                <td><?php echo htmlspecialchars($a['id']) ?></td>
                <td><?php echo htmlspecialchars($a['name']) ?></td>
                <td><?php echo htmlspecialchars($a['price']) ?></td>
                <td><?php echo htmlspecialchars($a['supplier_email']) ?></td>
                <td><?php echo htmlspecialchars($a['created_at']) ?></td>
                <td>
                    <a href="/?route=articles.editForm&id=<?php echo $a['id'] ?>">Edit</a>
                    <form method="post" action="/?route=articles.delete" style="display:inline">
                        <input type="hidden" name="id" value="<?php echo $a['id'] ?>">
                        <button type="submit" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No articles found.</p>
<?php endif; ?>

