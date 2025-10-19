<div>
    <h2>
        Article - (<?php echo htmlspecialchars($action) ?>)
    </h2>
    <?php if (!empty($errors)) { ?>
        <div class="error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php } ?>
    <form method="post" action="<?php echo '/?route=articles.' . $action ?>">
        <?php if ($action === 'update') { ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($article['id'] ?? $old['id'] ?? '') ?>">
        <?php } ?>
        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name"
                   value="<?php echo htmlspecialchars($article['name'] ?? $old['name'] ?? '') ?>"/>
        </div>
        <div>
            <label for="price" l>Price</label>
            <input type="text" id="price" name="price"
                   value="<?php echo htmlspecialchars($article['price'] ?? $old['price'] ?? '') ?>"/>
        </div>
        <div>
            <label for="supplier_email">Supplier email</label>

            <input type="text" id="supplier_email" name="supplier_email"
                   value="<?php echo htmlspecialchars($article['supplier_email'] ?? $old['supplier_email'] ?? '') ?>">
        </div>
        <div>
            <label for="description">Description</label>
            <textarea id="description"
                      name="description"><?php echo htmlspecialchars($article['description'] ?? $old['description'] ?? '') ?></textarea>
        </div>
        <button type="submit">Save</button>
    </form>
</div>
